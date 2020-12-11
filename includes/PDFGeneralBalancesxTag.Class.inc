<?php

define('FPDF_FONTPATH','./fonts/');
include ('fpdf.php');

class PDFGeneralBalancesTag extends FPDF {

	var $dataArray;
	
	function __construct(){
		$this->dataArray= array();
	}

	function Header(){
		global $razonsocial, $legalname, $fechainicial, $fechafinal;
		
		$this->Image('.'.$rootpath . '/' . $_SESSION['LogoFile'],10,5,20,20);
		$this->SetFont('helvetica','B',10);
		$this->SetY(15);
		$this->cell('',5,"REPORTE DE SALDOS INGRESOS Y EGRESOS",0,1,'C');
			
		$this->cell('',5,'RAZON SOCIAL: '.$legalname,0,1,'C');

		$this->cell('',5,"DESDE EL ".$fechainicial." HASTA EL ".$fechafinal,0,1,'C');
		$this->Ln();
		
		$this->SetY($this->GetY()+2);
		$this->SetFont('helvetica','B',8);
		$this->SetFillColor(204,204,229);
		$this->Cell(25,6, " Razon Social", 0, 0, "L",1);
		$this->Cell(35,6, "Region", 0, 0, "C",1);
		$this->Cell(50,6, "Unidad Negocio", 0, 0, "C",1);
		$this->Cell(30,6, "Monto Ingreso", 0, 0, "R",1);
		$this->Cell(30,6, "Monto Saldo CxC", 0, 0, "R",1);
		$this->Cell(30,6, "Monto Compras", 0, 0, "R",1);
		$this->Cell(30,6, "Monto Egresos", 0, 0, "R",1);
		$this->Cell(30,6, "Monto Saldo", 0, 0, "R",1);
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
		$resume= 0;
		$region="";
		
		foreach ($this->dataArray as $Record)
		{
			$saldotagname  = trim($Record['tagref']);
			$saldovalida=abs($Record['saldoingreso'])+abs($Record['saldocompras'])+abs($Record['saldoegreso'])+abs($Record['saldoCxC']);
			
			if(empty($saldotagname) == false && $saldovalida!=0) 
			{
				if($region != $Record["name"]){
					if ($resume == 1){
						//$this->SetX($this->GetX()+90);
						$this->Cell(260, 0, '', 'T');
						$this->Ln();
						$this->SetFillColor(230,230,230);
						$this->SetFont('helvetica','B',7);
						$this->Cell(110,5, "TOTAL ".rtrim($region).":", 0, 0, "R",1);  // region
						$this->Cell(30,5, "$ ".number_format($subtotalingreso,2), 0, 0, "R",1);  // monto de ingresos
						$this->Cell(30,5, "$ ".number_format($subtotalCxC,2), 0, 0, "R",1);  // monto de CxC
						$this->Cell(30,5, "$ ".number_format($subtotalcompras,2), 0, 0, "R",1);  // monto de compras
						$this->Cell(30,5, "$ ".number_format($subtotalegreso,2), 0, 0, "R",1);  // monto de egresos
						$this->Cell(30,5, "$ ".number_format($subtotalsaldo,2), 0, 0, "R",1);  // saldo
						$this->SetFont('helvetica','',7);
						$subtotalingreso= 0;
						$subtotalCxC= 0;
						$subtotalcompras= 0;
						$subtotalegreso= 0;
						$subtotalsaldo= 0;
						$this->Ln();
					}
				}
				
				$saldo= $Record["saldoingreso"]-$Record["saldoegreso"];
				$col= $this->GetX();
				$row= $this->GetY();
				$this->MultiCell(38,5, $Record["legalname"], 0, "L");  // razon social
				$this->SetXY($col+38, $row);
				$this->Cell(30,5, $Record["name"], 0, 0, "L");  // region
				$col= $this->GetX();
				$this->MultiCell(48,5, $Record["tagname"], 0, "L");  // unidad de negocio
				$this->SetXY($col+48, $row);
				$this->Cell(24,5, "$ ".number_format($Record["saldoingreso"],2), 0, 0, "R");  // monto de ingresos
				$this->Cell(30,5, "$ ".number_format($Record["saldoCxC"],2), 0, 0, "R");  // saldo de CxC
				$this->Cell(30,5, "$ ".number_format($Record["saldocompras"],2), 0, 0, "R");  // monto de compras
				$this->Cell(30,5, "$ ".number_format($Record["saldoegreso"],2), 0, 0, "R");  // monto de egresos
				$this->Cell(30,5, "$ ".number_format($saldo,2), 0, 0, "R");  // saldo
				
				if(strlen(trim($Record["tagname"]))>30 or strlen(trim($Record["legalname"]))>20){
					$this->SetY($row+5);
				}
					
				$subtotalingreso+= $Record["saldoingreso"];
				$subtotalCxC+= $Record["saldoCxC"];
				$subtotalcompras+= $Record["saldocompras"];
				$subtotalegreso+= $Record["saldoegreso"];
				$subtotalsaldo+= $Record["saldoingreso"]-$Record["saldoegreso"];
				
				$totalingreso+= $Record["saldoingreso"];
				$totalCxC+= $Record["saldoCxC"];
				$totalcompras+= $Record["saldocompras"];
				$totalegreso+= $Record["saldoegreso"];
				$totalsaldo+= $Record["saldoingreso"]-$Record["saldoegreso"];
				$region= $Record["name"];
				$resume=1;
				
				if($this->GetY() >= 200){
					$this->AddPage();
				}
				$this->Ln();
			} 
		}  // FIN DEL CICLO
		
		$this->Cell(260, 0, '', 'T');
		$this->Ln();
		$this->SetFont('helvetica','B',7);
		$this->SetFillColor(230,230,230);
		$this->Cell(110,5, "TOTAL ".$Record["name"].":", 0, 0, "R",1);  // region
		$this->Cell(30,5, "$ ".number_format($subtotalingreso,2), 0, 0, "R",1);  // monto de ingresos
		$this->Cell(30,5, "$ ".number_format($subtotalCxC,2), 0, 0, "R",1);  // monto de CxC
		$this->Cell(30,5, "$ ".number_format($subtotalcompras,2), 0, 0, "R",1);  // monto de compras
		$this->Cell(30,5, "$ ".number_format($subtotalegreso,2), 0, 0, "R",1);  // monto de egresos
		$this->Cell(30,5, "$ ".number_format($subtotalsaldo,2), 0, 0, "R",1);  // saldo
		$this->Ln();
		$this->Cell(260, 0, '', 'T');
		$this->Ln();
		$this->SetFillColor(224,248,224);
		$this->Cell(110,5, "TOTAL GENERAL:", 0, 0, "R",1);  // region
		$this->Cell(30,5, "$ ".number_format($totalingreso,2), 0, 0, "R",1);  // monto de ingresos
		$this->Cell(30,5, "$ ".number_format($totalCxC,2), 0, 0, "R",1);  // monto de CxC
		$this->Cell(30,5, "$ ".number_format($totalcompras,2), 0, 0, "R",1);  // monto de compras
		$this->Cell(30,5, "$ ".number_format($totalegreso,2), 0, 0, "R",1);  // monto de egresos
		$this->Cell(30,5, "$ ".number_format($totalsaldo,2), 0, 0, "R",1);  // saldo
			
		$this->OutPut('','I');
	}

	function Footer(){
		$this->SetFont('helvetica','B',7);
		$this->SetY(-10);
		$this->cell('',5,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
	}
}
