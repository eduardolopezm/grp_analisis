<?php 
define('FPDF_FONTPATH','./fonts/');
include ('includes/fpdf.php');
class Contrarecibo extends FPDF {
	var $Recorset;
	var $db;
	function setRecordset($rs) {
		$this->recorset = $rs;
	}

	function setdb($db) {
		$this->db = $db;
	}

	function exportPDF(){
		$myrow = DB_fetch_array($this->recorset);
		$myrowx = DB_fetch_array($this->recorset);
		$this->fpdf('P','mm','Letter');
		
		$this->AddPage();
		$logo = $myrow['logo'];
			
		$this->Image( $logo ,15,5,25,15);
		$this->SetFont('helvetica','B',10);
		$this->SetFillColor(255, 255, 255);
		$this->SetXY(90,$this->GetY()-2);
		$this->cell(80,  4, $myrow['legalname'], 0, 0, 'L', true);
		//
		$this->Ln();
		$this->SetX(90);
		//$this->cell(80,  4, $myrow['tagname'], 0, 0, 'L', true);
 
		$this->Ln(10);
		$this->SetX(140);
		$this->cell(20,  4, _('Fecha Impresion:').'   '.date('d/m/Y'), 0, 0, 'L', true);
		$this->SetX(40);
		$this->cell(20,  4, _('CONTRA RECIBO').'   '.$myrow['id'], 0, 0, 'L', true);
		$this->Ln(); 
		$this->SetX(140);
		$this->cell(20,  4, _('Fecha Emision:  ').'   '.$myrow['fechafactura'], 0, 0, 'L', true);
		$this->Ln(); 
		
	
		
		

		$this->Ln(10);
		$this->SetFont('helvetica','B',8);
		
		$this->SetFillColor(255, 255, 255);
		$this->cell(40,  4, _('RECIBIMOS DE').':', 0, 0, 'L', true); 
		$this->SetFillColor(255,255,255);
		$this->SetFont('helvetica', '', 8);
		$this->SetX(40); 
		$this->cell(100,  4, $myrow['suppname'], 0, 0, 'L', true);
		$this->Ln(8);
		
		//$this->Rect(10, 52, 204, 5, 'F'); 
		
		$this->SetX(60);
		$this->cell(200,  4, _('LA SIGUIENTE DOCUMENTACION PARA SU REVISION').':', 0, 0, 'L', true);
		$this->Ln(8);
		$this->SetFillColor(215, 215, 215);
		$this->cell(100,  5, _('Descripcion').':', 0, 0, 'L', true);
		$this->cell(40,  5, _('Folio').':', 0, 0, 'L', true);
		$this->cell(40,  5, _('Fecha').':', 0, 0, 'L', true);
		$this->cell(14,  5, _('Importe').':', 0, 0, 'L', true);
		$this->SetFillColor(255, 255, 255);
		$this->Ln(6);//
     if(isset($myrow['comments']) and $myrow['comments']!=null  and $myrow['comments']!="") 
     	$descripcion=$myrow['comments'];   
     else 
     	$descripcion="Factura";
     	
		$this->cell(100,  4,$descripcion, 0, 0, 'L', true);
		$this->cell(40,  4, $myrow['foliofactura'], 0, 0, 'L', true);
		$this->cell(40,  4, $myrow['trandate'], 0, 0, 'L', true);
		$this->cell(40,  4, $myrow['total'], 0, 0, 'L', true);
		    
		
		$this->Line(10,67,204,67);  
		$this->Line(10,62,204,62);  
		
		$this->Line(10,62,10,67);  
		$this->Line(204,62,204,67);
		
		$this->Ln();
		$this->Ln(); 
	  
		$this->Line(10,105,50,105);
		$this->SetX(170);
		$this->cell(20,  4, _('SUMA').':', 0, 0, 'L', true);
		$this->cell(20,  4, $myrow['total'], 0, 0, 'L', true); 
		
		$this->Ln(20);
		$this->cell(40,  4, _('FIRMA O SELLO DE RECIBO').':', 0, 0, 'L', true);
		
		$this->Ln(16); 
		$this->cell(100,  4, $myrow['realname'], 0, 0, 'L', true);
		$this->Ln();
		 
		// Mostrar la ordenes de compra relacionadas
		$sql = "SELECT GROUP_CONCAT(orderno) AS purchorders
			FROM supptrans INNER JOIN supptransdetails
			ON supptrans.id = supptransdetails.supptransid
			WHERE supptrans.transno = '{$myrow['transno']}'";
		
		$rs = DB_query($sql, $this->db);
		if ($row2 = DB_fetch_array($rs)) {
			if($row2['purchorders'] <> 0){
				$this->cell(88,  4, _('LAS ORDENES DE COMPRA INCLUIDAS SON LAS SIGUIENTES:'), 0, 0, 'L', true);
				$this->cell(20,  4, $row2['purchorders'], 0, 0, 'L', true);
			}
		}
		
		$this->OutPut('','I');
		die();
	}
	
}
?>