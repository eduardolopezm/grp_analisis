<?php 

define('FPDF_FONTPATH','./fonts/');
include ('includes/fpdf.php');
class Publicidad extends FPDF {
	var $Recorset;
	var $db;
	function setRecordset($rs) {
		$this->recorset = $rs;
	}
	
	function setdb($db) {
		$this->db = $db;
	}
	function Header(){//
		$this->SetFont('helvetica','B',12);
		$this->SetFillColor(255, 255, 255);
		$this->SetX(100);
		$this->cell(40,  4, _('REPORTE DE ROLES'), 0, 0, 'L', true);
		$this->Ln(10);
		$this->cell(55,  4, _('FECHA DE PUBLICACION').':', 0, 0, 'L', true);
		$this->cell(20,  4, $_GET['fecha'], 0, 0, 'L', true);
		$x = $this->GetX();
		$this->SetX($x+20);
		$this->cell(55,  4, _('FECHA DE IMPRESION').':', 0, 0, 'L', true);
		$this->cell(20,  4, date("d/m/Y"), 0, 0, 'L', true);
		$this->Ln(6);
		
		$this->cell(260,1,"","B",0,"",true);
		$this->Ln(6);
	}
	function exportPDF(){
		$category = "";
		$bandera = 1;
		$flag = 1;
			$this->fpdf('L','mm','Letter');

		$this->AddPage();
		
		$this->SetFillColor(255,255,255);
		$this->SetFont('helvetica', '', 6);//
		
		while($InvAnalysis = DB_fetch_array($this->recorset)) {
			$posActuakYsector = 0;
			$posActuakYcliente = 0;
			$posActuakYnarrative= 0;
			$posActuakYusuario = 0;
			if($posActuakYsector <> 0 or $posActuakYcliente <> 0 or $posActuakYnarrative <> 0 or $posActuakYusuario <> 0){
				$maximo = array($posActuakYsector,$posActuakYcliente,$posActuakYnarrative,$posActuakYusuario);
				$posmax = max($maximo);
				$this->SetY($posmax);
				$posActuakYsector = 0;
				$posActuakYcliente = 0;
				$posActuakYnarrative= 0; 
				$posActuakYusuario = 0;
			}
			if($category <> $InvAnalysis['categoryid']){
			//	if($flag <> 1 and $this->GetY() > 200){
				//	$this->AddPage();
				//}
				if($flag <> 1){
					$this->Ln();
					$this->cell(260,1,"","B",0,"",true);
					$this->Ln();
					$this->SetX(210);
					//$this->cell(10,  4, _('TOTAL'), 0, 0, 'L', true);
					$this->cell(40,  4, $categoriadesc, 0, 0, 'L', true);
					//$this->cell(12,  4, '$'.number_format($PrecioSeccion,2), 0, 0, 'L', true);
					$this->Ln();
				}
				if ($this->GetY() > 180){
				
					$this->AddPage();
				}
				$flag = 2;
				$this->Ln(6);
				$category = $InvAnalysis['categoryid'];
				$this->cell(15,  4, _('AM'), 0, 0, 'L', true);
				$x = $this->GetX();
				$this->SetX($x+40);
				$this->cell(20,  4, _('AM QUERETARO'), 0, 0, 'L', true);
				$x = $this->GetX();
				$this->SetX($x+40);
				$this->cell(20,  4, $InvAnalysis['categorydescription'], 0, 0, 'L', true);
				$categoriadesc = $InvAnalysis['categorydescription'];
				$this->Ln();
				$this->cell(260,1,"","B",0,"",true);
				$this->Ln();
				$this->cell(12,  4, _('O.T'), 0, 0, 'L', true);
				$this->cell(35,  4, _('GIRO'), 0, 0, 'L', true);
				$this->cell(50,  4, _('CLIENTE'), 0, 0, 'L', true);
				$this->cell(50,  4, _('OBSERVACIONES'), 0, 0, 'L', true);
				$this->cell(54,  4, _('PRODUCTO'), 0, 0, 'L', true);
				/*$this->cell(7,  4, _('M. A'), 0, 0, 'L', true);
				$this->cell(7,  4, _('M. B'), 0, 0, 'L', true);
				$this->cell(9,  4, _('MOD2'), 0, 0, 'L', true);
				$this->cell(11,  4, _('CM2'), 0, 0, 'L', true);
				$this->cell(9,  4, _('COLOR'), 0, 0, 'L', true);//
				*/
				$this->cell(40,  4, _('AGENTE'), 0, 0, 'L', true);
				$this->cell(22,  4, _('Pedido de Venta'), 0, 0, 'L', true);
				//$this->cell(12,  4, _('PRECIO'), 0, 0, 'L', true);
				$this->Ln(6);
				$PrecioSeccion = 0;

			}
			$this->cell(12,  4, $InvAnalysis['wo'], 0, 0, 'L', true);
			$posantYsector = $this->GetY();
			$posX = $this->GetX();
			if($InvAnalysis['SectComClNom'] == ""){
				$this->MultiCell(35, 4, 'Sin Giro',0,"L",0);
			}else{
				$this->MultiCell(35, 4, $InvAnalysis['SectComClNom'],0,"L",0);
			}
			$MultiSectorYDespues = $this->GetY();
			$Precio  = ($InvAnalysis['cantidad']*$InvAnalysis['precio'])-$InvAnalysis['totaldesc'];
			$PrecioSeccion = $PrecioSeccion + $Precio;
			$PrecioGeneral = $PrecioGeneral + $Precio;
			
			$posActuakYsector = $this->GetY();
			$this->SetXY($posX+35 , $posantYsector);
			$Cliente = $InvAnalysis['debtorno'].' - '.$InvAnalysis['name'];
			
			$posantYcliente = $this->GetY();
			$posX = $this->GetX();
			$this->MultiCell(50, 4, trim($Cliente),0,"L",0);
			$MultiClienteYDespues = $this->GetY();
			$posActuakYcliente = $this->GetY();
			$this->SetXY($posX+50 , $posantYcliente);
			
			$posantYnarrative = $this->GetY();
			$posX = $this->GetX();
			$this->MultiCell(50, 4,$InvAnalysis['narrative'],0,"L",0);
			$MultiNarrativeYDespues = $this->GetY();
			$posActuakYnarrative = $this->GetY();
			$this->SetXY($posX+50 , $posantYnarrative);
			$this->cell(52,  4, $InvAnalysis['description'], 0, 0, 'L', true);
			/*
			$this->cell(7,  4, number_format($InvAnalysis['height'],0), 0, 0, 'L', true);
			$this->cell(7,  4, number_format($InvAnalysis['width'],0), 0, 0, 'L', true);
			$this->cell(9,  4, number_format($InvAnalysis['mult'],0), 0, 0, 'L', true);
			$cm = $InvAnalysis['mulpag'] / $InvAnalysis['mult'];
			$this->cell(11,  4, number_format($cm,2), 0, 0, 'L', true);
			$this->cell(9,  4, $InvAnalysis['pagecolor'], 0, 0, 'L', true);
			*/
			$usuario = $InvAnalysis['salesmanname'];
			$posantYusuario = $this->GetY();
			$posX = $this->GetX();
			$this->MultiCell(50, 4, $usuario,0,"L",0);
			$MultiUsuarioYDespues = $this->GetY();
			$posActuakYusuario = $this->GetY();
			$this->SetXY($posX+50 , $posantYusuario);
			
		
			$this->cell(12,  4, $InvAnalysis['orderno'], 0, 0, 'L', true);
			//$this->cell(12,  4, '$'.number_format($Precio,2), 0, 0, 'L', true);
			
			$this->Ln();
			
			//$this->Ln(6);  
			
			
			$PosArray = array($MultiClienteYDespues, $MultiNarrativeYDespues, $MultiSectorYDespues, $MultiUsuarioYDespues);
			$PosMaxY = max($PosArray);
			if($this->GetY() < $PosMaxY){
				$Diferencia = $PosMaxY - $this->GetY();
			}//
			$this->Ln($Diferencia);
			
			if ($this->GetY() > 180){
				
				$this->AddPage();
			}
		}

		$this->Ln();
		$this->cell(260,1,"","B",0,"",true);
		$this->Ln();
		$this->SetX(210);
		//$this->cell(10,  4, _('TOTAL'), 0, 0, 'L', true);
		//$this->cell(40,  4, $categoriadesc, 0, 0, 'L', true);
		//$this->cell(12,  4, '$'.number_format($PrecioSeccion,2), 0, 0, 'L', true);
		$this->Ln();
		$this->Ln();
		$this->cell(260,1,"","B",0,"",true);
		$this->Ln();
		$this->SetX(240);
		//$this->cell(20,  4, _('TOTAL GENERAL'), 0, 0, 'L', true);
		//$this->cell(12,  4, '$'.number_format($PrecioGeneral,2), 0, 0, 'L', true);
		$this->Ln();
		$this->OutPut('','I');
		die();
	}//fin export
	
}// fin clase
?>