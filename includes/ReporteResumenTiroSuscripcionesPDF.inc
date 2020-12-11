<?php 
define('FPDF_FONTPATH','./fonts/');
include ('includes/fpdf.php');//
class ResumenSuscripciones extends FPDF {
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
		$this->Ln();
		$this->cell(30,  4, _('Ruta').':', 0, 0, 'L', true);
		if($_GET['Ruta'] == 0){
			$this->cell(50,  4, _('Todas las Rutas'), 0, 0, 'L', true);
		}else{
			$SQL = "SELECT StockCategoriesRutaEntrega.IdRutaEntrega,
						StockCategoriesRutaEntrega.Ruta,
						StockCategoriesRutaEntrega.Responsable,
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
		
		
		

		$this->Ln(6);
		$this->cell(260,1,"","B",0,"",true);
		$this->Ln(6);
	}
	function exportPDF(){
		$this->fpdf('P','mm','Letter');

		$this->AddPage();
		$this->SetFillColor(255,255,255);
		$this->SetFont('helvetica', '', 6);
		
		$this->SetFillColor(255,255,255);
		$this->SetFont('helvetica', '', 6);
			
		$this->cell(50,  4, _('Ruta'), 0, 0, 'L', true);
		$this->cell(50,  4, _('Responsable.T'), 0, 0, 'L', true);
		$this->cell(12,  4, _('Visitas'), 0, 0, 'L', true);
		$this->cell(12,  4, _('Ejemplares'), 0, 0, 'L', true);
		$this->Ln();
		
		while($myrow = DB_fetch_array($this->recorset)) {
			
			$this->cell(50,  4, $myrow['Ruta'],0, 0, 'L', true);
			$this->cell(50,  4, $myrow['Responsable'],0, 0, 'L', true);
			$this->cell(12,  4, $myrow['visita'],0, 0, 'L', true);
			$this->cell(12,  4, $myrow['Ejemplares'],0, 0, 'L', true);
			
			$TotalVisitas = $TotalVisitas + $myrow['visita'];
			$TotalEjemplares = $TotalEjemplares + $myrow['Ejemplares'];
			$this->Ln();
		}
		$this->Ln(4);
		$this->SetX(110);
		$this->cell(20,1,"","B",0,"",true);
		$this->Ln(4);
		$this->SetX(90);
		$this->cell(20,  4, _('Totales'), 0, 0, 'L', true);
		$this->cell(12,  4, $TotalVisitas,0, 0, 'L', true);
		$this->cell(12,  4, $TotalEjemplares,0, 0, 'L', true);
		
		$this->OutPut('','I');
		die();
	}
}
?>