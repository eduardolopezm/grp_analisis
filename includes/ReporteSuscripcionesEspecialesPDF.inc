<?php 
define('FPDF_FONTPATH','./fonts/');
include ('includes/fpdf.php');
class SuscripcionesEspeciales extends FPDF {
	var $Recorset;
	var $db;
	function setRecordset($rs) {
		$this->recorset = $rs;
	}

	function setdb($db) {
		$this->db = $db;
	}
	function Header(){
		$this->SetFont('helvetica','B',10);
		$this->SetFillColor(255, 255, 255);
		$this->SetX(80);
		$this->cell(80,  4, _('REPORTE DE SUSCRIPCIONES ESPECIALES').'', 0, 0, 'L', true);
		$this->Ln();
		$this->cell(20,  4, _('De').':', 0, 0, 'L', true);
		$this->cell(20,  4, $_GET['FromFechaLog'], 0, 0, 'L', true);
		$this->cell(20,  4, _('Hasta').':', 0, 0, 'L', true);
		$this->cell(20,  4, $_GET['ToFechaLog'], 0, 0, 'L', true);
		$this->Ln();
		$this->cell(30,  4, _('Ruta').':', 0, 0, 'L', true);//
		if($_GET['Ruta'] == 0){
			$this->cell(50,  4, _('Todas las Rutas'), 0, 0, 'L', true);
		}else{
			$SQL = "SELECT StockCategoriesRutaEntrega.IdRutaEntrega,
						StockCategoriesRutaEntrega.Ruta,
						StockCategoriesRutaEntrega.Responsable
					FROM StockCategoriesRutaEntrega
					WHERE StockCategoriesRutaEntrega.Activo = 1
					AND StockCategoriesRutaEntrega.IdRutaEntrega = '".$_GET['Ruta']."'";
			$Result = DB_query($SQL, $this->db);
			$myrow = DB_fetch_array($Result);
			$this->cell(50,  4, $myrow['IdRutaEntrega'].'-'.$myrow['Ruta'],0, 0, 'L', true);
			$this->cell(13,  4, _('Responsable').':', 0, 0, 'L', true);
			$this->cell(50,  4, $myrow['Responsable'],0, 0, 'L', true);
		}
		$this->Ln();
		$this->cell(30,  4, _('Categoria').':', 0, 0, 'L', true);
		if($_GET['categorylog'] == '*'){
			$this->cell(50,  4, _('Todas las categorias'), 0, 0, 'L', true);
		}else{
			$SQL = "Select stockcategory.categoryid,
						stockcategory.categorydescription
				 from stockcategory
				 where stockcategory.categoryid = '".$_GET['categorylog']."'";
			$Result = DB_query($SQL, $this->db);
			$myrow = DB_fetch_array($Result);
			$this->cell(70,  4, $myrow['categoryid'].'-'.$myrow['categorydescription'],0, 0, 'L', true);
		}
		
		$this->Ln();
		$this->cell(10,  4, _('Sus'), 0, 0, 'L', true);
		$this->cell(12,  4, _('O.T'), 0, 0, 'L', true);
		$this->cell(12,  4, _('P.V'), 0, 0, 'L', true);
		$this->cell(12,  4, _('F. Ini'), 0, 0, 'L', true);
		$this->cell(40,  4, _('Cliente'), 0, 0, 'L', true);
		$this->cell(12,  4, _('F. Final'), 0, 0, 'L', true);
		$this->cell(8,  4, _('Ej'), 0, 0, 'L', true);
		$this->cell(30,  4, _('Direccion'), 0, 0, 'L', true);
		$this->cell(30,  4, _('Colonia'), 0, 0, 'L', true);
		$this->cell(8,  4, _('Cp'), 0, 0, 'L', true);
		$this->cell(30,  4, _('Entre calles'), 0, 0, 'L', true);
		$this->cell(20,  4, _('Cod. Ant'), 0, 0, 'L', true);
		$this->cell(30,  4, _('Dias Entrega'), 0, 0, 'L', true);
		$this->Ln(4);
		
		$this->SetFont('helvetica','B',6);
		$this->SetFillColor(255, 255, 255);
		$this->SetX(233);
		$this->cell(5,  2, _('Do'), 0, 0, 'L', true);
		$this->cell(5,  2, _('Lu'), 0, 0, 'L', true);
		$this->cell(5,  2, _('Ma'), 0, 0, 'L', true);
		$this->cell(5,  2, _('Mi'), 0, 0, 'L', true);
		$this->cell(5,  2, _('Ju'), 0, 0, 'L', true);
		$this->cell(5,  2, _('Vi'), 0, 0, 'L', true);
		$this->cell(5,  2, _('Sa'), 0, 0, 'L', true);
		

		$this->Ln(6);
		$this->cell(260,1,"","B",0,"",true);
		$this->Ln(6);
	}
	function exportPDF(){
		$this->fpdf('L','mm','Letter');

		$this->AddPage();
		$this->SetFillColor(255,255,255);
		$this->SetFont('helvetica', '', 6);
		
		$Ruta = 0;
		$CategoriaId = 0;
		$index=0;
		while($myrow = DB_fetch_array($this->recorset)) {
			$this->SetFillColor(255,255,255);
			$this->SetFont('helvetica', '', 6);
			if($Ruta <> $myrow['idRutaEntrega']){
				$this->cell(12,  4, _('Ruta'), 0, 0, 'L', true);
				$this->cell(50,  4, $myrow['ruta'],0, 0, 'L', true);
				$this->cell(12,  4, _('Repartidor'), 0, 0, 'L', true);
				$this->cell(50,  4, $myrow['Responsable'],0, 0, 'L', true);
				$Ruta = $myrow['idRutaEntrega'];
				$this->Ln();
			}
			if($CategoriaId <> $myrow['categoryid']){
				$this->cell(50,  4, $myrow['categoryid'],0, 0, 'L', true);
				$this->cell(50,  4, $myrow['categorydescription'],0, 0, 'L', true);
				$CategoriaId = $myrow['categoryid'];
				$this->Ln();
			}
			
			$this->cell(10,  4, $myrow['stockid'],0, 0, 'L', true);
			$this->cell(12,  4, $myrow['wo'],0, 0, 'L', true);
			$this->cell(12,  4, $myrow['orderno'],0, 0, 'L', true);
			$this->cell(12,  4, $myrow['dia'].'/'.$myrow['mes'].'/'.$myrow['anio'],0, 0, 'L', true);
			
			$AYcliente = $this->GetY();
			$AXcliente = $this->GetX();
			$this->MultiCell(40,4,$myrow['name'],0,'L');
			$DClienteY = $this->GetY();
			$this->SetXY($AXcliente+40 , $AYcliente);
			
			$this->cell(12,  4, $myrow['diavencimiento'].'/'.$myrow['mesvencimiento'].'/'.$myrow['yearvencimiento'],0, 0, 'L', true);
			$this->cell(8,  4, $myrow['qtyreqd'],0, 0, 'L', true);
			
			$AYdomicilio = $this->GetY();
			$AXdomicilio = $this->GetX();
			$Domicilio = $myrow['domicilio'].' '.$myrow['numeroext'].' '.$myrow['numeroint'];
			$this->MultiCell(30,4,$Domicilio,0,'L');
			$DdomicilioY = $this->GetY();
			$this->SetXY($AXdomicilio+30 , $AYdomicilio);
			
			$AYcolonia = $this->GetY();
			$AXcolonia = $this->GetX();
			$this->MultiCell(30,4,$myrow['colonia'],0,'L');
			$DcoloniaY = $this->GetY();
			$this->SetXY($AXcolonia+30 , $AYcolonia);
			
			$this->cell(8,  4, $myrow['codpos'],0, 0, 'L', true);
			
			$AYentre = $this->GetY();
			$AXentre = $this->GetX();
			$this->MultiCell(30,4,$myrow['entrecalles'],0,'L');
			$DentreY = $this->GetY();
			$this->SetXY($AXentre+30 , $AYentre);
			
			
			$this->cell(20,  4, $myrow['nopedido'],0, 0, 'L', true);

			// Consulto los dias que se entrega en esta direccion
			for($x=1;$x<=7;$x++){
				$sql="SELECT stockcatproperties.label,salesstockproperties.orderno,
													salesstockproperties.orderlineno,salesstockproperties.typedocument,
													stockcatproperties.dayweek,stockcatproperties.addressref
				FROM salesstockproperties
					INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
					INNER JOIN stockcategory on stockcategory.categoryid=stockcatproperties.categoryid
				WHERE stockcatproperties.controltype = 2 and stockcategory.CodigoPanelControl=911 and valor='SI'
					AND stockcatproperties.dayweek =".$x."
					AND salesstockproperties.orderno=".$myrow['orderno']."
					AND salesstockproperties.orderlineno=".$myrow['orderlineno']."
					AND stockcatproperties.addressref=".$myrow['addressref'];
				$SearchResult = DB_query($sql,$this->db);
				if (DB_num_rows($SearchResult)==0 ){
					$this->SetFont('helvetica', 'B', 9);
					$this->cell(5,  2, 'N', 0, 0, 'L', true);
				}else{
					$this->SetFont('helvetica', 'B', 9);
					$this->cell(5,  2, 'S', 0, 0, 'L', true);
				}
			}
			$PosArray = array($DClienteY, $DcoloniaY, $DdomicilioY, $DentreY);
			$PosMaxY = max($PosArray);
			if($this->GetY() < $PosMaxY){
				$Diferencia = $PosMaxY - $this->GetY();
			}//
			$this->Ln($Diferencia);
				
			if ($this->GetY() > 180){
			
				$this->AddPage();
			}
		}
		$this->OutPut('','I');
		die();
	}
}
?>