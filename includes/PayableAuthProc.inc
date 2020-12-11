<?php
	define('FPDF_FONTPATH','./fonts/');
	include ('includes/fpdf.php');
	 
	class PayableAuthProcPDF extends FPDF {
			
		function Header(){

			$this->Image('.'.$rootpath . '/' . $_SESSION['LogoFile'],10,5,10,15);
			$this->SetFont('helvetica','B',9);
			$this->SetY(10);
			$this->cell('',4,"Autorizacion y Programacion de Cuentas por Pagar",0,1,'C');
			$this->Ln();
			
			//filtros
			$this->SetFont('helvetica','B',8);
			$arrFiltros = $_SESSION['rptPayableAuthProc']['filtros'];
			foreach($arrFiltros as $titulo=>$texto){
				$this->cell('',4,$titulo.': '.$texto,0,1,'C');
			}
			$this->Ln();
		
			$this->SetFillColor(220,220,220);
			$this->SetFont('helvetica','B',6);
			$arrTitulos = $_SESSION['rptPayableAuthProc']['header'];
			for($i=0;$i<count($arrTitulos);$i++){
				$ancho = 20;
				if ($i==0)
					$ancho=40;
				if ($i==1)
					$ancho=17;
				if ($i==2)
					$ancho=10;
				if ($i==3)
					$ancho=75;
					
				$this->cell($ancho,4,$arrTitulos[$i],1,0,'C',1);
			}
			
		
			$this->Ln();	

		}
		
		function exportPDF(){
						
			$this->fpdf('L','mm','Letter');
			
			$this->AliasNbPages();
			$this->SetAutoPageBreak(true,10);
			$this->AddPage();
			
			$this->SetFillColor(255,255,255);
			$this->SetFont('helvetica','',6);

			$total = count($_SESSION['rptPayableAuthProc']['details']);
			//echo $total;
			//exit;
			for($i=0;$i<$total;$i++){
				$arrvalores = $_SESSION['rptPayableAuthProc']['details'][$i];
				if ($i==($total)){
					$this->SetFont('helvetica','B',6);
					$this->cell(222,4,'TOTAL GENERAL',1,0,'L',1);
					$this->cell(20,4,$arrvalores[0],1,0,'R',1);
					$this->SetFont('helvetica','',6);

				}
				else
					for($j=0;$j<count($arrvalores)-1;$j++){
						$this->SetFillColor(255,255,255);
						$v = $arrvalores[$j];
					
						$ancho = 20;
						if ($j==0)
							$ancho=40;

						if ($j==1){
							$ancho=17;
						}
						if ($j==2)
							$ancho=10;

						if ($j==3)
							$ancho=75;

						
						if ($j==(count($arrvalores)-1))
							$this->cell($ancho,4,$v,1,0,'R',1);
						else	
							$this->cell($ancho,4,$v,1,0,'L',1);
					}
					$totalt=$totalt+$arrvalores[9];
					
				$this->Ln();	

			
			}
			//$arrvalores = $_SESSION['rptPayableAuthProc']['details'][$total-1];
			$this->SetFont('helvetica','B',6);
			$this->cell(222,4,'TOTAL GENERAL',1,0,'L',1);
			$this->cell(20,4,'$ '.number_format($totalt,2),1,0,'R',1);
			
			if ($_SESSION['rptPayableAuthProc']['honorarios'] > 0){
				$this->Ln();
				$this->cell(222,4,'TOTAL HONORARIOS',1,0,'L',1);
				$this->cell(20,4,'$ '.number_format($_SESSION['rptPayableAuthProc']['honorarios'],2),1,0,'R',1);			
			}
			
			$this->SetFont('helvetica','',6);
			$this->Ln();
			$this->Ln();
			$this->Ln();
			$ind=0;
			$posY = $this->GetY();
			foreach($_SESSION['rptPayableAuthProc']['footer'] as $title=>$arrfooter){
				$this->SetFillColor(220,220,220);
				$this->SetFont('helvetica','B',7);
				$this->SetXY($ind+50,$posY);
				$this->cell(60,4,$title,0,1,'C',1);
				for($i=0;$i<count($arrfooter);$i++){
					$arrvalues = $arrfooter[$i];
					foreach($arrvalues as $col1=>$col2){
						$this->SetX($ind+50);
						$this->cell(40,4,$col1,0,0,'L');
						$this->cell(20,4,$col2,0,1,'R');
					}
				}
				
				
				$ind+=100;
			}
			
			$this->OutPut('','I');
				
		}
		
		function Footer(){
			global $db;
			
			$qry = "Select * FROM www_users WHERE userid = '".$_SESSION['UserID']."'";
			$rs = DB_query($qry,$db);
			$row = DB_fetch_array($rs);
			$usuario = $row['realname'];
			
			$this->SetFont('helvetica','B',8);
			$this->SetY(-10);
			$this->cell('60',5,utf8_decode('Fecha de elaboración: ').date("d/m/Y H:i"),0,0,'C');
			$this->cell('100',5,utf8_decode('Elaboró: '.$usuario),0,0,'C');
			$this->cell('',5,utf8_decode('Página ').$this->PageNo().'/{nb}',0,0,'R');
		}
	
	}



?>