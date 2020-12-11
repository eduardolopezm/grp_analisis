<?php 
define('FPDF_FONTPATH','./fonts/');
include ('includes/fpdf.php');
class Suscripciones extends FPDF {
	var $Recorset;
	var $db;
	function setRecordset($rs) {
		$this->recorset = $rs;
	}

	function setdb($db) {
		$this->db = $db;
	}
	function Header(){
		$this->SetFont('helvetica','B',12);
		$this->SetFillColor(255, 255, 255);
		$this->cell(70,  4, _('Periodicos por entregar al dia').':', 0, 0, 'L', true);
		$this->cell(20,  4, $_GET['FromFechaLog'], 0, 0, 'L', true);
		$this->Ln(6);
		$this->cell(250,1,"","B",0,"",true);
		$this->Ln(6);
	}
	function exportPDF(){
		$this->fpdf('L','mm','Letter');
		$this->AddPage();
		$this->SetFillColor(255,255,255);
		$this->SetFont('helvetica', '', 6);
		$IdRutaEntrega = "";//
		$CategoriaId = "";
		$flag = 1;
		$Ruta = 0;
		$SumaRuta = 0;
		$SumaDomicilio = 0;
		$TotalSumRuta = 0;
		$TotalSumDom = 0;
		$flagPrimero = 0;
		while($myrow = DB_fetch_array($this->recorset)) {
			$DiaXEntrega = 1;
			$DiaSemana = $myrow['fechasemana'];
			$LunesArray = Array(1 => "Domingo",
					2 => "Lunes",
					3 => "Martes",
					4 => "Miercoles",
					5 => "Jueves",
					6 => "Viernes",
					7 => "Sabado");
			$DiaSemanaLetra = $LunesArray[$DiaSemana];
			
			$sql = "SELECT salesstockproperties.valor
						FROM salesstockproperties
						INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
						WHERE salesstockproperties.orderno = '".$myrow['orderno']."'
						AND salesstockproperties.orderlineno = '".$myrow['orderlineno']."'
						AND stockcatproperties.controltype = 2
						AND stockcatproperties.label like '%".$DiaSemanaLetra."%'";
			
			$result = DB_query($sql, $this->db);
			
			$Colonia = "";
			$Domicilio = "";
			$flagSiNo = 0;
			if(DB_num_rows($result) > 0){
				while($myrow2 = DB_fetch_array($result)){
					if($myrow2['valor'] == 'SI'){
						$flagSiNo = 1;
					}elseif($myrow2['valor'] == 'NO' and $flagSiNo == 0){
							
						$DiaXEntrega = $DiaXEntrega + 1;
					}
					
				}
					
					
				$sql = "SELECT salesstockproperties.valor
							FROM salesstockproperties
							INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
							WHERE salesstockproperties.orderno ='".$myrow['orderno']."'
							AND salesstockproperties.orderlineno ='".$myrow['orderlineno']."'
							AND stockcatproperties.controltype = 1
							AND stockcatproperties.label like '%Dom%'";
				$result = DB_query($sql, $this->db);
				$Contador = 1;
				while($myrow2 = DB_fetch_array($result)){
					if($Contador == $DiaXEntrega){
						$Domicilio = $myrow2['valor'];
						$Contador = $Contador + 1;
					}else{
						$Contador = $Contador + 1;
					}
				}
				$sql = " SELECT salesstockproperties.valor
							FROM salesstockproperties
							INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
							WHERE salesstockproperties.orderno ='".$myrow['orderno']."'
							AND salesstockproperties.orderlineno ='".$myrow['orderlineno']."'
							AND stockcatproperties.controltype = 1
							AND stockcatproperties.label like '%col%'";
				$result = DB_query($sql, $this->db);
				$Contador = 1;
				while($myrow2 = DB_fetch_array($result)){
					if($Contador == $DiaXEntrega){
						$Colonia = $myrow2['valor'];
						$Contador = $Contador + 1;
					}else{
						$Contador = $Contador + 1;
					}
				}
				$sql = " SELECT salesstockproperties.valor
										FROM salesstockproperties
										INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
										WHERE salesstockproperties.orderno ='".$myrow['orderno']."'
										AND salesstockproperties.orderlineno ='".$myrow['orderlineno']."'
										AND stockcatproperties.controltype = 1
										AND stockcatproperties.label like '%Numero Ext%'";
				$result = DB_query($sql, $this->db);
				$Contador = 1;
				while($myrow2 = DB_fetch_array($result)){
					if($Contador == $DiaXEntrega){
						$NumExt = $myrow2['valor'];
						$Contador = $Contador + 1;
					}else{
						$Contador = $Contador + 1;
					}
				}
				$sql = " SELECT salesstockproperties.valor
										FROM salesstockproperties
										INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
										WHERE salesstockproperties.orderno ='".$myrow['orderno']."'
										AND salesstockproperties.orderlineno ='".$myrow['orderlineno']."'
										AND stockcatproperties.controltype = 1
										AND stockcatproperties.label like '%Numero Int%'";
				$result = DB_query($sql, $this->db);
				$Contador = 1;
				while($myrow2 = DB_fetch_array($result)){
					if($Contador == $DiaXEntrega){
						$NumInt = $myrow2['valor'];
						$Contador = $Contador + 1;
					}else{
						$Contador = $Contador + 1;
					}
				}
				$sql = " SELECT salesstockproperties.valor
										FROM salesstockproperties
										INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
										WHERE salesstockproperties.orderno ='".$myrow['orderno']."'
										AND salesstockproperties.orderlineno ='".$myrow['orderlineno']."'
										AND stockcatproperties.controltype = 1
										AND stockcatproperties.label like '%CP%'";
				$result = DB_query($sql, $this->db);
				$Contador = 1;
				while($myrow2 = DB_fetch_array($result)){
					if($Contador == $DiaXEntrega){
						$CP = $myrow2['valor'];
						$Contador = $Contador + 1;
					}else{
						$Contador = $Contador + 1;
					}
				}
				$sql = " SELECT salesstockproperties.valor
										FROM salesstockproperties
										INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
										WHERE salesstockproperties.orderno ='".$myrow['orderno']."'
										AND salesstockproperties.orderlineno ='".$myrow['orderlineno']."'
										AND stockcatproperties.controltype = 1
										AND stockcatproperties.label like '%Entre%'";
				$result = DB_query($sql, $this->db);
				$Contador = 1;
				while($myrow2 = DB_fetch_array($result)){
					if($Contador == $DiaXEntrega){
						$EntreCalle = $myrow2['valor'];
						$Contador = $Contador + 1;
					}else{
						$Contador = $Contador + 1;
					}
				}
				if($NumExt <> ""){
					$Domicilio = $Domicilio.' NumExt. '.$NumExt;
				}
				if($NumInt <> ""){
					$Domicilio = $Domicilio.' NumInt. '.$NumInt;
				}
				$sql = "SELECT
										salesstockproperties.valor
								FROM workorders
								INNER JOIN woitems ON woitems.wo = workorders.wo
								INNER JOIN stockmaster ON woitems.stockid = stockmaster.stockid
								INNER JOIN salesorders ON workorders.orderno = salesorders.orderno
								INNER JOIN salesorderdetails ON salesorderdetails.woline = workorders.wo
								INNER JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
								INNER JOIN stockcatproperties ON stockcatproperties.categoryid = stockmaster.categoryid
								INNER JOIN salesstockproperties ON salesorderdetails.orderno = salesstockproperties.orderno and salesorderdetails.orderlineno = salesstockproperties.orderlineno and stockcatproperties.stkcatpropid = salesstockproperties.stkcatpropid
								INNER JOIN StockCategoriesRutaEntrega ON salesstockproperties.valor = StockCategoriesRutaEntrega.IdRutaEntrega
								WHERE workorders.wo = '".$myrow['wo']."'
								AND stockcatproperties.controltype = 7
							";
				$Contador = 1;
				$result = DB_query($sql, $this->db);
				while($myrow2 = DB_fetch_array($result)){
					if($Contador == $DiaXEntrega){
						$Ruta = $myrow2['valor'];
						$Contador = $Contador + 1;
					}else{
						$Contador = $Contador + 1;
					}
				}
				
				if($Ruta == $myrow['IdRutaEntrega']){
					if($IdRutaEntrega <> $myrow['IdRutaEntrega']){
						if($flag <> 1){
							$this->AddPage();
						}
						if($flagPrimero == 1){
							$this->cell(250,1,"","B",0,"",true);
							$this->Ln();
													$this->cell(20,  4, _('Ejemplares'), 0, 0, 'L', true);
								$this->SetX(120);
								$this->cell(40,  4, $SumaRuta, 0, 0, 'L', true);
								$this->cell(20,  4, _('Visitas'), 0, 0, 'L', true);
								$this->cell(20,  4, $SumaDomicilio, 0, 0, 'L', true);
								$this->Ln();
							$SumaDomicilio = 0;
							$SumaRuta = 0;
						}
						$flagPrimero =1;
						$flag = 0;
						$this->cell(20,  4, _('Ruta'), 0, 0, 'L', true);
						$this->cell(40,  4, $myrow['Ruta'], 0, 0, 'L', true);
						$this->cell(20,  4, _('Repartidor'), 0, 0, 'L', true);
						$this->cell(20,  4, $myrow['Responsable'], 0, 0, 'L', true);
						$this->Ln();
						$IdRutaEntrega = $myrow['IdRutaEntrega'];
						$this->cell(250,1,"","B",0,"",true);
						$this->Ln();
						$this->SetFont('helvetica','B',6);
						$this->SetFillColor(255, 255, 255);
						$this->cell(12,  4, _('Suscripcion'), 0, 0, 'L', true);
						$this->cell(12,  4, _('O.T'), 0, 0, 'L', true);
						$this->cell(12,  4, _('P.V'), 0, 0, 'L', true);//
						$this->cell(12,  4, _('F. Inicial'), 0, 0, 'L', true);
						$this->cell(30,  4, _('Cliente'), 0, 0, 'L', true);
						$this->cell(12,  4, _('F. Final'), 0, 0, 'L', true);
						$this->cell(20,  4, _('Estatus'), 0, 0, 'L', true);
						$this->cell(7,  4, _('Ej'), 0, 0, 'L', true);
						$this->cell(40,  4, _('Direccion'), 0, 0, 'L', true);
						$this->cell(30,  4, _('Colonia'), 0, 0, 'L', true);
						$this->cell(12,  4, _('CP'), 0, 0, 'L', true);
						$this->cell(40,  4, _('Entre calles'), 0, 0, 'L', true);
						$this->cell(12,  4, _('Cod. Ant.'), 0, 0, 'L', true);
						$this->Ln();
						$this->SetFont('helvetica','',6);
						$this->SetFillColor(255, 255, 255);
					}
					if($CategoriaId <> $myrow['categoryid']){
						$this->cell(250,1,"","B",0,"",true);
						$this->Ln();
						$this->cell(20,  4, $myrow['categoryid'], 0, 0, 'L', true);
						$this->cell(50,  4, $myrow['categorydescription'], 0, 0, 'L', true);
						$CategoriaId = $myrow['categoryid'];
					}
					$this->Ln();
					$this->cell(12,  4, $myrow['stockid'], 0, 0, 'L', true);
					$this->cell(12,  4, $myrow['wo'], 0, 0, 'L', true);
					$this->cell(12,  4, $myrow['orderno'], 0, 0, 'L', true);
					$this->cell(12,  4, $myrow['dia'].'/'.$myrow['mes'].'/'.$myrow['anio'], 0, 0, 'L', true);
					$MultiDescripcionY = $this->GetY();
					$MultiDescripcionX = $this->GetX();
					//$this->MultiCell(30, 4, $myrow['name'],0,'L');
					
					//$this->MultiCell(40, 4, $Domicilio,0,'L');
					$actX = $this->GetX();
					$actY = $this->GetY();
					//
					//$myrow['name']='';
					$this->MultiCell(30,4,$myrow['name'],0,'L');
					$posYMultiCell = $this->GetY();
					if ($actY < $posYMultiCell){
					$y = $actY+3;
					while ($y < $posYMultiCell){
					$this->SetY($y);
					$this->cell('',3,'','',1,'L');
					$y+=3;
					}
					$this->cell('',3,'','',1,'L');
					$actY = $this->GetY();
					}
					$this->SetXY($actX+30,$actY);// para regresar a la posicion sigte//
					
					
					
					$MultiDescripcionYDespues = $this->GetY();
					$this->SetXY($MultiDescripcionX+30, $MultiDescripcionY);
					$this->cell(12,  4, $myrow['diavencimiento'].'/'.$myrow['mesvencimiento'].'/'.$myrow['yearvencimiento'], 0, 0, 'L', true);
					$this->cell(20,  4, $myrow['nombre'], 0, 0, 'L', true);
					$this->cell(7,  4, $myrow['qtyreqd'], 0, 0, 'L', true);
					$MultiDireccionY = $this->GetY();
					$MultiDireccionX = $this->GetX();
					
					
					//$this->MultiCell(40, 4, $Domicilio,0,'L');
					$actX = $this->GetX();
					$actY = $this->GetY();
					//
					
					$this->MultiCell(40,4,$Domicilio,0,'L');
					$posYMultiCell = $this->GetY();
					if ($actY < $posYMultiCell){
						$y = $actY+3;
						while ($y < $posYMultiCell){
							$this->SetY($y);
							$this->cell('',3,'','',1,'L');
							$y+=3;
						}
					}
					$this->SetXY($actX+40,$actY);// para regresar a la posicion sigte//
					
					
					//$MultiDireccionYDespues = $this->GetY();
					//$this->SetXY($MultiDireccionX+40, $MultiDireccionY);
					$MultiColoniaY = $this->GetY();
					$MultiColoniaX = $this->GetX();
					//$this->MultiCell(30, 4, $Colonia,0,'L');
					
					$actX = $this->GetX();
					$actY = $this->GetY();
					//
						
					$this->MultiCell(30,4,$Colonia,0,'L');
					$posYMultiCell = $this->GetY();
					if ($actY < $posYMultiCell){
					$y = $actY+3;
					while ($y < $posYMultiCell){
					$this->SetY($y);
					$this->cell('',3,'','',1,'L');
					$y+=3;
					}
					}
					$this->SetXY($actX+30,$actY);// para regresar a la posicion sigte//
					
					
					
					
					
					$MultiColoniaYDespues = $this->GetY();
					//$this->SetXY($MultiColoniaX+30, $MultiColoniaY);
					$this->cell(12,  4, $CP, 0, 0, 'L', true);
					
					$MultiEntreY = $this->GetY();
					$MultiEntreX = $this->GetX();
					
					//$this->MultiCell(30, 4, $EntreCalle,0,'L');
					$actX = $this->GetX();
					$actY = $this->GetY();
					//
					
					$this->MultiCell(30,4,$EntreCalle,0,'L');
					$posYMultiCell = $this->GetY();
					if ($actY < $posYMultiCell){
					$y = $actY+3;
					while ($y < $posYMultiCell){
					$this->SetY($y);
					$this->cell('',3,'','',1,'L');
					$y+=3;
					}
					}
					$this->SetXY($actX+30,$actY);// para regresar a la posicion sigte//
					
					
					$MultiEntreYDespues = $this->GetY();
					$this->SetXY($MultiEntreX+30, $MultiEntreY);
					$this->cell(12,  4, $myrow['nopedido'], 0, 0, 'L', true);
					$this->Ln();
					//$PosArray = array($MultiDescripcionYDespues, $MultiDireccionYDespues, $MultiColoniaYDespues, $MultiEntreYDespues);
					$PosMaxY = max($PosArray);
					$SumaRuta = $SumaRuta + $myrow['qtyreqd'];
					$TotalSumRuta = $TotalSumRuta + $myrow['qtyreqd'];
					$SumaDomicilio = $SumaDomicilio +  1;
					$TotalSumDom = $TotalSumDom + 1;
					//if($this->GetY() < $PosMaxY){
						//$Diferencia = $PosMaxY - $this->GetY();
						
					//}//
					$this->SetY($posYMultiCell+3);
					if ($this->GetY() > 150){
						$this->AddPage();
					}//
					
				}
				
			}
		}
		$this->cell(250,1,"","B",0,"",true);
		$this->Ln();
		$this->cell(20,  4, _('Ejemplares'), 0, 0, 'L', true);
		$this->SetX(120);
		$this->cell(40,  4, $SumaRuta, 0, 0, 'L', true);
		$this->cell(20,  4, _('Visitas'), 0, 0, 'L', true);
		$this->cell(20,  4, $SumaDomicilio, 0, 0, 'L', true);
		$this->Ln();
		$this->cell(250,1,"","B",0,"",true);
		$this->Ln();
		$this->cell(20,  4, _('Ejemplares'), 0, 0, 'L', true);
		$this->SetX(120);
		$this->cell(40,  4, $TotalSumRuta, 0, 0, 'L', true);
		$this->cell(20,  4, _('Visitas'), 0, 0, 'L', true);
		$this->cell(20,  4, $SumaDomicilio, 0, 0, 'L', true);
		$this->Ln();
		$this->OutPut('','I');
		die();
	}
}
?>