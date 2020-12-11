<?php
	define('FPDF_FONTPATH','./fonts/');
	include ('includes/fpdf.php');
	 
	class priceListPDF extends FPDF {
		
		
		function Header(){

			$this->Image('.'.$rootpath . '/' . $_SESSION['LogoFile'],10,5,40,10);
			$this->SetFont('helvetica','B',9);
			$this->SetY(15);
			$this->cell('',4,"LISTADO DE PRECIOS ACTUALIZADOS",0,1,'C');
			$this->Ln();
			$posIniX = 20;
			$this->Ln();
			$this->SetX($posIniX);
			$this->SetFont('helvetica','B',7);
			$this->cell(40,4,'PRODUCTO',1,0,'C');
			$this->cell(30,4,'PRECIO ANTERIOR',1,0,'C');
			$this->cell(30,4,'PRECIO NUEVO',1,0,'C');
			$this->cell(30,4,'DIFERENCIA',1,0,'C');
			$this->cell(30,4,'% INCREMENTO',1,0,'C');
			
			
			$this->Ln();	

		}
		
		function exportPDF(){
						
			$this->fpdf('P','mm','Letter');
			
			$this->AliasNbPages();
			$this->SetAutoPageBreak(true,10);
			$this->AddPage();
			
			$this->SetFillColor(255,255,255);
			$this->SetFont('helvetica','',6);
			$posIniX = 20;
			foreach ($_SESSION['rptChangePrices'] as $arrdatos){
				$this->SetX($posIniX);				
				$this->cell(40,4,$arrdatos['producto'],1,0,'L');
				$this->cell(30,4,number_format($arrdatos['oldprice'],2),1,0,'R');
				$this->cell(30,4,number_format($arrdatos['newprice'],2),1,0,'R');
				$this->cell(30,4,number_format($arrdatos['newprice']-$arrdatos['oldprice'],2),1,0,'R');
				$this->cell(30,4,number_format((($arrdatos['newprice']-$arrdatos['oldprice'])/($arrdatos['oldprice']))*100,2)."%",1,0,'R');
				$this->Ln();				
			}
				
			$this->OutPut('','I');
			
			
			die();	
		}
		
		function Footer(){
			$this->SetFont('helvetica','B',7);
			$this->SetY(-10);
			$this->cell('',5,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
		}
	
	}



?>