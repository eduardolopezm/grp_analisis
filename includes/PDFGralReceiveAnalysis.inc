<?php
	define('FPDF_FONTPATH','./fonts/');
	include ('includes/fpdf.php');
	 
	class GralReceiveAnalysisPDF extends FPDF {
		
		
		function Header(){
			if(strlen($_GET['razon'])==0){
				$razon='TODAS LAS RAZONES SOCIALES';
			}else{
			
				$razon=$_GET['razon'];
			}

			$this->Image('.'.$rootpath . '/' . $_SESSION['LogoFile'],10,5,20,10);
			$this->SetFont('helvetica','B',10);
			$this->SetY(15);
			$this->cell('',5,"REPORTE GENERAL DE RECEPCIONES",0,1,'C');
			
			$this->cell('',5,'RAZON SOCIAL: '.$razon,0,1,'C');
				
			$this->cell('',5,"DESDE EL ".$_GET['desde']." HASTA EL ".substr($_GET['hasta'],0,10),0,1,'C');
			$this->Ln();
			

			$arrTitulos = $_SESSION['rptGeneralReceiveAnalysis']['header'];
			for($i=0;$i<count($arrTitulos);$i++){
				
				if ($i==1)
					$this->cell(95,5,$arrTitulos[$i],1,0,'C');
				else
					if ($i==0)	
						$this->cell(25,5,$arrTitulos[$i],1,0,'C');
					else 
						$this->cell(20,5,$arrTitulos[$i],1,0,'C');
			}
			
			$this->Ln();	

		}
		
		function exportPDF(){
						
			$this->fpdf('L','mm','Letter');
			
			$this->AliasNbPages();
			$this->SetAutoPageBreak(true,10);
			$this->AddPage();
			
			$this->SetFillColor(255,255,255);
			$this->SetFont('helvetica','',7);

			$arrItems = $_SESSION['rptGeneralReceiveAnalysis']['details'];
			//echo "<pre>";
			//print_r($arrItems);
			for($i=0;$i<count($arrItems)-1;$i++){
				$items = $arrItems[$i];
				$this->SetFillColor(255,255,255);
				if (count($items)==1){
					$this->SetFont('helvetica','B',7);
					$this->cell(240,4,$items[0],1,0,'L',1);
					$this->SetFont('helvetica','',7);
				}
				else{
					if ($items[1]==""){
						$this->SetFillColor(235,235,235);	
						for($j=0;$j<count($items);$j++){
							if ($j==0)
								$this->cell(120,4,$items[$j],1,0,'R',1);
							else	
								if ($j > 1)
									$this->cell(20,4,$items[$j],1,0,'R',1);
						}	
					}
					else
						for($j=0;$j<count($items);$j++){
							if ($j==1)
								$this->cell(95,4,$items[$j],1,0,'L',1);
							else	
								if ($j==0)
									$this->cell(25,4,$items[$j],1,0,'R',1);
								else
									$this->cell(20,4,$items[$j],1,0,'R',1);
						}	
				}
				$this->Ln();
				
			}	
			
			//ultima fila son totales generales
			$items = $arrItems[count($arrItems)-1];
			//$this->SetFillColor(255,50,0);	
			$this->SetFont('helvetica','B',7);
			for($j=0;$j<count($items);$j++){
				if ($j==0)
					$this->cell(120,4,'TOTAL GENERAL',1,0,'R',1);
				else	
					if ($j > 1)
						$this->cell(20,4,$items[$j],1,0,'R',1);
			}	
			
			$this->OutPut('','I');
			
		}
		
		function Footer(){
			$this->SetFont('helvetica','B',7);
			$this->SetY(-10);
			$this->cell('',5,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
		}
	
	}



?>