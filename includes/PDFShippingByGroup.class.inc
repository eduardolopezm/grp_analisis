<?php

define('FPDF_FONTPATH','./fonts/');
include ('includes/fpdf.php');
 
class PDFShipping extends FPDF {
	
	var $db;
	var $shippingLogId;
	var $type, $typeno;
	
	function setDb(&$db) {
		$this->db = $db;
	}
	
	function setShippingLogId($shippinglogid) {
		$this->shippinglogid = $shippinglogid;
	}
	
	function Header(){
		
		$SQL = "
			SELECT
			shippinglog.type,
			shippinglog.typeno,
			shippinglog.name,
			shippinglog.trackingnumber,
			shippinglog.userid,
			DATE_FORMAT(shippinglog.deliverydate, '%d-%m-%Y') AS deliverydate,
			DATE_FORMAT(shippinglog.shippingdate, '%d-%m-%Y') AS shippingdate,
			shippinglog.comments,
			custbranch.brname AS branchname,
			custbranch.taxid AS branchrfc,
			custbranch.braddress4 AS branchstate,
			custbranch.braddress5 AS branchno,
			custbranch.braddress1 AS branchstreet,
			legalbusinessunit.legalname AS legalname,
			legalbusinessunit.taxid AS legalrfc,
			legalbusinessunit.address4 AS legalstate,
			legalbusinessunit.address5 AS legalno,
			legalbusinessunit.address1 AS legalstreet,
			replace(shippinglog.folio,'|','') as foliofac
			FROM shippinglog
			LEFT JOIN debtortrans
			ON debtortrans.id = shippinglog.debtortransid
			LEFT JOIN tags
			ON tags.tagref = shippinglog.tagref
			LEFT JOIN legalbusinessunit
			ON legalbusinessunit.legalid = tags.legalid
			LEFT JOIN custbranch
			ON custbranch.debtorno = debtortrans.debtorno
			AND custbranch.branchcode = debtortrans.branchcode
			WHERE shippingparent = '{$this->shippinglogid}'
		";
		$rs = DB_query($SQL, $this->db);
		
		$name 			= "";
		$trackingnumber = "";
		$userid 		= "";
		$deliverydate 	= "";
		$shippingdate 	= "";
		$comments		= "";
		$branchname		= "";
		$branchrfc		= "";
		$branchstate	= "";
		$branchno		= "";
		$branchstreet	= "";
		$legalname		= "";
		$legalrfc		= "";
		$legalstate		= "";
		$legalno		= "";
		$legalstreet	= "";
		
		if($row = DB_fetch_array($rs)) {
			$name 			= $row['name'];
			$trackingnumber = $row['trackingnumber'];
			$userid 		= $row['userid'];
			$deliverydate 	= $row['deliverydate'];
			$shippingdate 	= $row['shippingdate'];
			$comments 		= $row['comments'];
			$branchname		= $row['branchname'];
			$branchrfc		= $row['branchrfc'];
			$branchstate	= $row['branchstate'];
			$branchno		= $row['branchno'];
			$branchstreet	= $row['branchstreet'];
			$legalname		= $row['legalname'];
			$legalrfc		= $row['legalrfc'];
			$legalstate		= $row['legalstate'];
			$legalno		= $row['legalno'];
			$legalstreet	= $row['legalstreet'];
			$this->type		= $row['type'];
			$this->typeno	= $row['typeno'];
		}
		
		$this->Image('.' . $rootpath . '/' . $_SESSION['LogoFile'], 10, 5, 25, 20);
		$this->SetFont('helvetica', 'B', 12);
		$this->cell('', 4, "DETALLE DE ENVIO EMBARQUE NO " . $this->shippinglogid, 0, 1, 'C');
		
		$this->SetY(30);
		$this->SetFont('helvetica','B',9);
		$this->SetFillColor(224, 224, 224);
		
		$this->SetFont('helvetica','B',9);
		$this->SetFillColor(224, 224, 224);
		$this->cell(25, 4, "Razon social", 1, 0, 'L', true);
		$this->cell(60, 4, $legalname, 1, 1, 'L');
		$this->SetFont('helvetica','',9);
		$this->cell(25, 4, "Estado:" , 1, 0, 'L', true);
		$this->cell(60, 4, $legalstate, 1, 1, 'L');
		$this->SetFont('helvetica','',9);
		$this->cell(25, 4, "Calle:", 1, 0, 'L', true);
		$this->cell(60, 4, $legalstreet, 1, 1, 'L');
		$this->cell(25, 4, "No:", 1, 0, 'L', true);
		$this->cell(60, 4, $legalno, 1, 1, 'L');
		$this->cell(25, 4, "RFC:", 1, 0, 'L', true);
		$this->cell(60, 4, $legalrfc, 1, 1, 'L');
		
		$this->Ln(4);
		
		$this->SetFont('helvetica','B',9);
		$this->cell(190, 4, "DATOS DE ENVIO", 1, 1, 'L', true);
		$this->SetFont('helvetica','',9);
		$this->cell(45, 4, "Responsable del Envio:", 1, 0, 'L', true);
		$this->cell(145, 4, $name, 1, 1, 'L');
		$this->cell(45, 4, "Número de Guia:" , 1, 0, 'L', true);
		$this->cell(145, 4, $trackingnumber, 1, 1, 'L');
		$this->cell(45, 4, "Usuario de Registro:", 1, 0, 'L', true);
		$this->cell(145, 4, $userid, 1, 1, 'L');
		//$this->cell(45, 4, "Fecha de Entrega:", 1, 0, 'L', true);
		//$this->cell(145, 4, $deliverydate, 1, 1, 'L');
		$this->cell(45, 4, "Fecha de Envio:", 1, 0, 'L', true);
		$this->cell(145, 4, $shippingdate, 1, 1, 'L');
		$this->cell(45, 10, "Comentarios:", 1, 0, 'L', true);
		$this->MultiCell(145, 10, $comments, 1, 'L');
	
		$this->SetY(100);
		$this->SetFont('helvetica', 'B', 10);
		$this->cell('', 4, 'DETALLE DE PRODUCTOS', '0', 1, 'C');
		$this->Ln();
		$this->SetFillColor(220, 220, 220);
		$this->SetFont('helvetica', 'B', 8);
		
		$centerX = $this->w/2 - 150/2;
		$this->setX($centerX);
		$this->cell(20, 8, _('No. Factura'), 1, 0, 'C', true);
		$this->cell(20, 8, _('Cantidad'), 1, 0, 'C', true);
		$this->cell(50, 8, _('Codigo Producto'), 1, 0, 'C', true);
		
		$this->cell(30, 8, _('Precio'), 1, 0, 'C', true);
		$this->cell(50, 8, _('Fecha'), 1, 0, 'C', true);
		
		$this->Ln();	
	}
	
	function exportPDF(){
					
		//$this->fpdf('L','mm','Letter');
		$this->fpdf('P','mm','Letter');
		
		$this->AliasNbPages();
		$this->SetAutoPageBreak(true,10);
		$this->AddPage();
		
		$this->SetFillColor(255,255,255);
		$this->SetFont('helvetica', '', 8);
		
		
		
		$SQL = "SELECT *, DATE_FORMAT(stockmoves.trandate, '%d-%m-%Y') AS trandatemod ,
		replace(shippinglog.folio,'|','') as foliofac
		FROM stockmoves inner join shippinglog on shippinglog.type=stockmoves.type
		and shippinglog.typeno=stockmoves.transno
		WHERE shippingparent = '{$this->shippinglogid}'";
		
		//WHERE stockmoves.type = '{$this->type}' AND stockmoves.transno = '{$this->typeno}'";
		$rs = DB_query($SQL, $this->db);
		
		$centerX = $this->w/2 - 150/2;
		
		while($row = DB_fetch_array($rs)) {
			$this->setX($centerX);
			$this->cell(20, 4, $row['foliofac'], 1, 0, 'C');
			$this->cell(20, 4, $row['qty'], 1, 0, 'R');
			$this->cell(50, 4, $row['stockid'], 1, 0, 'L');
			
			$this->cell(30, 4, $row['price'], 1, 0, 'R');
			$this->cell(50, 4, $row['trandatemod'], 1, 0, 'C');
			$cantidad=$cantidad+$row['qty'];
			$this->Ln();
		}
		
		$this->setX($centerX);
		//$this->cell(20, 4, '', 1, 0, 'C');
		$this->SetFont('helvetica','B',9);
		$this->cell(20, 4, _('Total'), 1, 0, 'R');
		$this->SetFont('helvetica','',9);
		$this->cell(20, 4, $cantidad, 1, 0, 'R');
		$this->cell(130, 4, '', 1, 0, 'R');
		//$this->cell(50, 4, '', 1, 0, 'C');
		$this->Ln();
			
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