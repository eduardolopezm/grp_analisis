<?php

define('FPDF_FONTPATH','./fonts/');
include ('fpdf.php');

class PDFGeneralCompras extends FPDF {
	
	var $dataArray;
	
	function Header(){
		
		$razon='TODAS LAS RAZONES SOCIALES';
		$fechaini= $this->dataArray["complementos"][0]["fechaini"];
		$fechafin= $this->dataArray["complementos"][0]["fechafin"];
		
		$this->Image('.'.$rootpath . '/' . $_SESSION['LogoFile'],10,5,20,20);
		$this->SetFont('helvetica','B',10);
		$this->SetY(15);
		$this->cell('',5,"REPORTE GENERAL DE COMPRAS",0,1,'C');
			
		$this->cell('',5,'RAZON SOCIAL: '.$razon,0,1,'C');
		
		$this->cell('',5,"DESDE EL ".$fechaini." HASTA EL ".$fechafin,0,1,'C');
		$this->Ln();
			
		$this->SetX(5);
		$arrTitulos = $this->dataArray["encabezado"];
		for($i=0;$i<count($arrTitulos);$i++)
		{
			$ancho= strlen(trim($arrTitulos[$i]));
			$act= $this->GetX();
			//$this->cell($ancho+12, 5, $arrTitulos[$i], 1, 0, 'L');
			$this->MultiCell($ancho, 5, $arrTitulos[$i], 1, "L");
		}
			
		$this->Ln();
	}
	
	function setDatosMatriz($dataArray){
		$this->dataArray= $dataArray;
	}
	
	function exportPDF(){
		$this->fpdf('L','mm','Letter');
			
		$this->AliasNbPages();
		$this->SetAutoPageBreak(true,10);
		$this->AddPage();
			
		$this->SetFillColor(255,255,255);
		$this->SetFont('helvetica','',7);

		$this->OutPut('','I');
	}
	
	function Footer(){
		$this->SetFont('helvetica','B',7);
		$this->SetY(-10);
		$this->cell('',5,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
	}
}
