<?php 
 define('FPDF_FONTPATH','./fonts/');
 include ('includes/fpdf.php');

class pdfwoissue extends FPDF {
	function Header(){
		global $db;
		
		
		$this->Image('.'.$rootpath . '/' . $_SESSION['LogoFile'],10,5);
		$this->SetFont('helvetica','B',9);
		$this->SetY(15);
		$this->cell('',4,"LISTADO DE MATERIALES EMITIDOS",0,1,'C');
		$this->Ln();

		$qry = "Select distinct stockmoves.loccode,
						stockmoves.reference as wo,
						locations.locationname,
						DATE_FORMAT(stockmoves.trandate,'%d/%m/%Y') as fecha
				FROM stockmoves
				INNER JOIN locations
					ON stockmoves.loccode = locations.loccode
				
				WHERE type = ".$_GET['type']."
				and transno = ".$_GET['transno'];
		
		$rs = DB_query($qry,$db);
		$rows = DB_fetch_array($rs);
		
		$this->SetFont('helvetica','',8);
		$this->cell('',4,"ORDEN DE TRABAJO: ".$rows['wo'],'0',1,'L');
		$this->cell('',4,"ALMACEN: ".$rows['loccode'].'  '.$rows['locationname'],'0',1,'L');
		$this->cell('',4,"FECHA: ".$rows['fecha'],'0',1,'L');
		$this->Ln();
			
			
		$this->Ln();
		$this->SetFont('helvetica','B',7);
		$this->SetFillColor(220,220,220);
		//$this->SetFont('helvetica','B',6);
		$this->cell(40,4,'Clave',1,0,'C');
		$this->cell(130,4,'Descripcion',1,0,'C');
		$this->cell(20,4,'Cantidad',1,0,'C');					
			
		$this->Ln();
	
	}
	
	function exportPDF(){
		global $db;
		
		$this->fpdf('P','mm','Letter');
			
		$this->AliasNbPages();
		$this->SetAutoPageBreak(true,10);
		$this->AddPage();
			
		$this->SetFillColor(255,255,255);
		$this->SetFont('helvetica','',7);
	
		$qry = "Select  stockmoves.stockid,
						stockmaster.description,
						stockmoves.qty
				FROM stockmoves
				LEFT JOIN stockmaster
					ON stockmoves.stockid = stockmaster.stockid
		
				WHERE type = ".$_GET['type']."
				and transno = ".$_GET['transno'];
		
		$rs = DB_query($qry,$db);
		while ($rows = DB_fetch_array($rs)){
			$this->cell(40,4,$rows['stockid'],1,0,'C');
			$this->cell(130,4,$rows['description'],1,0,'L');
			$this->cell(20,4,number_format(abs($rows['qty']),4),1,0,'R');
				
			$this->Ln();
		}
		$this->Ln(15);
		$this->cell(60,4,'Recibido por','T',1,'L');
	
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