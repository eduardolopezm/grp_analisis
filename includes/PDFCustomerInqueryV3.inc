<?php
	define('FPDF_FONTPATH','./fonts/');
	include ('includes/fpdf.php');
	 
	class PDFCustomerInquery extends FPDF {
			
		function Header(){
			global $db,$saldoI;					
			
			
			$this->Image('.'.$rootpath . '/' . $_SESSION['LogoFile'],10,5,10,15);
			$this->SetFont('helvetica','B',10);
			$this->SetY(10);
			$this->SetFont('helvetica','B',8);
			$this->cell('',4,"ESTADO DE CUENTA DE CLIENTES",0,1,'C');
			$this->cell('',4,$_GET['cliente'],0,1,'C');
			$this->cell('',4,"DESDE EL ".$_GET['fi']." HASTA ".substr($_GET['ff'],0,10),0,1,'C');
			
			$arrclte = explode("-",$_GET['cliente']);
			$qry = "Select sum((ovamount+ovgst-alloc)/rate) as si
					FROM debtortrans
					where origtrandate < '".$_GET['fi']."'
					and debtorno='".$arrclte[0]."'";
			$rs = DB_query($qry,$db);
			$myrow = DB_fetch_array($rs);
			$saldoI = $myrow['si'];
			
			$this->Ln(10);
			$this->SetFont('helvetica','B',7);
			$this->cell('',5,'Saldo Incial al '.$_GET['fi'].' '.number_format($saldoI,2),0,1,'L');
			$this->Ln();
			
			$this->SetFont('helvetica','B',7);			
			$this->cell(10,4,'#',1,0,'C');
			//$this->cell(35,4,'Unid. Neg.',1,0,'C');
			$this->cell(55,4,'Folios',1,0,'C');
			$this->cell(70,4,'Concepto',1,0,'C');
			$this->cell(20,4,'Cargo',1,0,'C');
			$this->cell(20,4,'Iva',1,0,'C');
			$this->cell(20,4,'Total',1,0,'C');
			$this->cell(20,4,'Aabono',1,0,'C');
			$this->cell(20,4,'Saldo',1,0,'C');
			$this->cell(10,4,'TC',1,0,'C');
			$this->cell(15,4,'Fech.Venc',1,0,'C');
			$this->Ln(5);
			
		}
		
		function exportPDF($fullpath=""){
			global $db,$saldoI;
						
			$this->fpdf('L','mm','Letter');
			
			$this->AliasNbPages();
			//$this->SetAutoPageBreak(true,10);
			$this->AddPage();
			
			$this->SetFillColor(235,235,235);
			$this->SetFont('helvetica','',7);
			

			/*echo "<pre>";
			print_r($_SESSION['rptEdoCtaCliente']);
			die();
			*/
			function comparator ($a, $b) {
				return strcmp($a['un'],$b['un']);
			}
			
			foreach($_SESSION['rptEdoCtaCliente'] as $seccion=>$datos){
				$this->SetFont('helvetica','B',7);
				$this->cell('',4,$seccion,0,1,'L',1);
				$this->SetFont('helvetica','',7);
				//$datos = asort($datos);
				usort($datos, "comparator");
				$unant = "";
				foreach($datos as $key=>$arrdata){
					if (is_numeric($key)){
						if($unant <> $arrdata['un']){
							$this->Ln(2);
							$this->SetFont('helvetica','B',7);
							$this->cell(100,4,$arrdata['un'],0,1,'L',0);
							$this->Ln(4);
							$unant = $arrdata['un'];
							$this->SetFont('helvetica','',7);
						}
						$this->cell(10,4,$arrdata['#'],0,0,'C');
						//$this->cell(35,4,$arrdata['un'],0,0,'L');
						$this->cell(55,4,$arrdata['folio'],0,0,'L');
						
						$txt = $arrdata['concepto'];
						if ($arrdata['leyenda'])
							$txt.="\n".$arrdata['leyenda'];
						
						$actY = $this->GetY();
						$this->MultiCell(70,4,$txt,0,'L');
						$newY = $this->GetY();
						$this->SetXY($this->GetX()+135,$actY);
						
						$this->cell(20,4,$arrdata['cargo'],0,0,'R');
						$this->cell(20,4,$arrdata['iva'],0,0,'R');
						$this->cell(20,4,$arrdata['total'],0,0,'R');
						$this->cell(20,4,$arrdata['abono'],0,0,'R');
						$this->cell(20,4,$arrdata['saldo'],0,0,'R');
						$this->cell(10,4,$arrdata['tc'],0,0,'R');
						$this->cell(15,4,$arrdata['fechav'],0,0,'L');
						if($this->GetY() > 200){
							$this->AddPage();
						}
					}else{
						$this->SetFont('helvetica','B',7);
						$txt="SALDO TOTAL EN PESOS ";
						if ($key=="total")
							$txt = "TOTAL ";
						else
							$saldo = $datos[$key];	
							
						$this->cell(235,4,$txt.number_format($datos[$key],2),0,0,'R');
						if($this->GetY() > 200){
							$this->AddPage();
						}
					}
					$this->Ln();
					//
					/*if ($newY > $this->GetY())
						$this->SetY($newY);*/
					if($this->GetY() < $newY){
						$Diferencia = $newY - $this->GetY();
					}//
					$this->Ln($Diferencia);
					
					if($this->GetY() > 180){
						$this->AddPage();
					}
					
				}	
				
			}		
			$this->Ln(5);
			$this->cell(235,4,'SALDO TOTAL AL '.substr($_GET['ff'],0,10).'  '.number_format($saldo+$saldoI,2),0,0,'R');
			
			
			if (trim($fullpath)!=""){
				$this->Output($fullpath, 'F');//F
				
			}
			else{
				$this->OutPut('','I');
				die();
			}
			//$this->OutPut('','I');
			
		}
		
		function Footer(){			
			$this->SetFont('helvetica','B',8);
			$this->SetY(-10);
			$this->cell('60',5,utf8_decode('Fecha de elaboración: ').date("d/m/Y H:i"),0,0,'C');
			$this->cell('',5,utf8_decode('Página ').$this->PageNo().'/{nb}',0,0,'R');
		}
	
	}



?>