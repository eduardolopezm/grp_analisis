<?php

define('FPDF_FONTPATH','./fonts/');
include ('includes/fpdf.php');


class PDFControlObjetivos extends FPDF {
	//global $db;
	
	
	var $dataArray;
	
	
	
	function Header(){
		
		
		$this->Image('.'.$rootpath . '/' . $_SESSION['LogoFile'],5,5,35,25);
		$this->SetFont('helvetica','B',10);
		$this->cell('',5,"HIDALGO, ".date("d")." DE ".glsnombremeslargo(date("m"))." DEL ".date("Y"),'0',1,'R');
		$this->SetXY(50,15);
		$this->ln();

		$this->SetY(30);
		$this->cell('',5,"REPORTE DE OBJETIVOS",'0',1,'C');
	}
	//
	function setDataArray($dataArray) {
		$this->dataArray = $dataArray;
	}
	
	
	function exportPDF(){
		global $db;
					
		$this->fpdf('P','mm','Letter');
		
		$this->AliasNbPages();
		$this->SetAutoPageBreak(true, 10);
		$this->AddPage();
		
		$this->SetFillColor(255, 255, 255);
		$this->SetFont('helvetica', '', 6);
		
		$width = 200;
		$centerX = $this->w/2 - $width/2;
		$this->setX($centerX);
		
		$this->SetFont('helvetica','',6);
		$this->Ln();
		$yTemp2 = 0;
		$xcolor=0;
		$totalgeneraltareas=0;
		$totalgeneralterm=0;
		
		foreach ($this->dataArray as $name => $rows) 
		{
			//$this->cell($width, 8, $name, 1, 0, 'L', true);
			$this->SetFont('helvetica','B',11);
			
			$this->SetFillColor(192, 192,192);
			$this->SetTextColor(255, 255, 255);
			
			//$this->cell(30,4,'Objetivo: ',0,0,'',1);
			$this->SetX($this->GetX());
			$this->cell(200,4,$name,0,0,'',1);
			
			$this->Ln();
			$this->SetFont('helvetica','B',9);
			//$this->cell(20,4,'Fecha',0,0,'C');
			$this->SetFillColor(5, 102, 147);
			$this->SetTextColor(255, 255, 255);
			if($xcolor==0){
				$this->cell(10, 8, 'Id', 0, 0, 'C',1);
				$this->cell(20, 8, 'Fecha Alta', 0, 0, 'C',1);
				$this->cell(30, 8, 'Fecha Vencimiento', 0, 0, 'C',1);
				$this->cell(73, 8, 'Nombre Objetivo', 0, 0, 'C',1);
				$this->cell(20, 8, 'Estatus', 0, 0, 'C',1);
				$this->cell(17, 8, '# Tareas', 0, 0, 'C',1);
				$this->cell(20, 8, 'Terminadas', 0, 0, 'C',1);
				$this->cell(10, 8, '%', 0, 1, 'C',1);
				$this->SetFont('helvetica','',8);
			}
			$this->SetFillColor(255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$xcolor=$xcolor+1;
			$totalterminadas=0;
			$totaltareas=0;
			
			foreach ($rows as $row) {
				$this->cell(10, 8, $row['id'], 0, 0, 'C');
				$this->cell(20, 8, $row['fechaAlta'], 0, 0, 'C');
				$this->cell(30, 8, $row['fechaVencimiento'], 0, 0, 'C');
				$actY = $this->GetY();
				$actX=$this->GetX();
				$this->MultiCell(73,6, $row['nombre'],0,'L');
				// se agrega comentarios
				$comentarioobj='';
				$usuario='';
				$fechaaltacomentarioobj='';
				$SQLCom = "SELECT *
					   	   FROM prdobjetivo_comentarios
					  			INNER JOIN www_users on prdobjetivo_comentarios.userid = www_users.userid
					   	   WHERE Idobjetivo ='".$row['id']."'
					   	   ORDER BY Idobjetivo DESC ";
				$resultCom = DB_query($SQLCom, $db);
				//echo '<pre><br>'.$SQLCom;
				while($myrowcom = DB_fetch_array($resultCom)){
					$fechaaltacomentarioobj = $myrowcom['fechaaltacomentarioobj'];
					$usuario = $myrowcom['realname'];
					$comentarioobj = $myrowcom['comentarioobj'];
				}
				if($fechaaltacomentarioobj!=''){
					$posYMultiCell = $this->GetY();
					$this->SetX($actX);
					$this->SetXY($actX,$posYMultiCell);// para regresar a la posicion sigte
					$this->SetFont('helvetica','',7);
					$this->SetTextColor(0, 0, 255);
					$this->MultiCell(73,4,$fechaaltacomentarioobj.' ('.$usuario.') '.$comentarioobj,0,'L');
					$this->SetFont('helvetica','',8);
					$this->SetTextColor(0, 0, 0);
				}
				$posYMultiCell = $this->GetY();
				$maxposy = $posYMultiCell;
				$this->SetXY($actX+73,$actY);// para regresar a la posicion sigte
				
				
				$this->cell(20, 8,$row['status'], 0, 0, 'C');
				$this->cell(17, 8, $row['nTareas'], 0, 0, 'C');
				$this->cell(20, 8, $row['tareasTerminadas'], 0, 0, 'C');
				//$this->cell(30, 8, $row['horasEstimadas'], 1, 0, 'C', true);
				//$this->cell(30, 8, $row['horasRealizadas'], 1, 0, 'C', true);
				$this->cell(10, 8, number_format($row['porcentaje'], 0), 0, 1, 'C');
				$this->SetY($maxposy);
				$this->cell(200,3," ",'T',1,'L');
				$totaltareas=$totaltareas+ $row['nTareas'];
				$totalterminadas=$totalterminadas+$row['tareasTerminadas'];
				$totalgeneraltareas+= $row['nTareas'];
				$totalgeneralterm+= $row['tareasTerminadas'];
				
				if ($this->GetY()>=250){
					$this->AddPage();
					$this->Ln();
					$this->SetFillColor(5, 102, 147);
					$this->SetTextColor(255, 255, 255);
					$this->SetFont('helvetica','B',9);
					//$this->cell(20,4,'Fecha',0,0,'C');
					$this->cell(10, 8, 'Id', 0, 0, 'C',1);
					$this->cell(20, 8, 'Fecha Alta', 0, 0, 'C',1);
					$this->cell(30, 8, 'Fecha Vencimiento', 0, 0, 'C',1);
					$this->cell(73, 8, 'Nombre Objetivo', 0, 0, 'C',1);
					$this->cell(20, 8, 'Estatus', 0, 0, 'C',1);
					$this->cell(17, 8, '# Tareas', 0, 0, 'C',1);
					$this->cell(20, 8, 'Terminadas', 0, 0, 'C',1);
					$this->cell(10, 8, '%', 0, 1, 'C',1);
					$this->SetFont('helvetica','',8);
					$this->SetFillColor(255, 255, 255);
					$this->SetTextColor(0, 0, 0);
				}
				
			}
			
			$this->SetFont('helvetica','B',11);
			$this->cell(150,7,'Total: ',0,0,'R');
			$this->cell(22, 7, $totaltareas, 0, 0, 'C');
			$this->SetX($this->GetX()+3);
			$this->cell(11, 7, $totalterminadas, 0, 0, 'C');
			$this->cell(15, 7, number_format(($totalterminadas/$totaltareas)*100, 0)."%", 0, 0, 'R');
			$this->Ln();
		}
		
		$this->Ln();
		$this->SetFont('helvetica','B',15);
		$this->cell(152,8,'Total General: ',0,0,'R');
		$this->cell(20, 8, $totalgeneraltareas, 0, 0, 'C');
		$this->SetX($this->GetX()+2);
		$this->cell(14, 8, $totalgeneralterm, 0, 0, 'C');
		$this->cell(15, 8, number_format(($totalgeneralterm/$totalgeneraltareas)*100, 0)."%", 0, 0, 'R');
		
		$this->OutPut('','I');
		die();	
	}
	
	function Footer(){
		$this->SetFont('helvetica', 'B', 12);
		$this->SetY(-10);
		$this->cell('',5,'Pagina '.$this->PageNo().'/{nb}', 0, 0, 'C');
	}
}
?>