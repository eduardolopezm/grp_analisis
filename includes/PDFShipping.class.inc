<?php

define('FPDF_FONTPATH','./fonts/');
include ('includes/fpdf.php');

class PDFShipping extends FPDF {
	
	var $db;
	var $shippingLogId;
	var $type, $typeno;
	var $hidePrice;
	
	function setDb(&$db) {
		$this->db = $db;
	}
	
	function hidePrice($hide) {
		$this->hidePrice = $hide;
	}
	
	function setShippingLogId($shippingLogId) {
		$this->shippingLogId = $shippingLogId;
	}
	function setFirmaEmbarque($FirmEmbarque) {
		$this->FirmaEmbarque = $FirmEmbarque;
	}
	
	function Header(){
		
		$SQL = "
			SELECT
			shippinglog.type,
			shippinglog.typeno,
			shippinglog.name,
			shippinglog.trackingnumber,
			shippinglog.userid,
			shippinglog.shippingno,
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
			legalbusinessunit.logo,
			legalbusinessunit.logoAlterno,
			shippinglog.camion,
			shippinglog.chofer,
			shippinglog.folio_envio,
			www_users.realname,
			custbranch.brpostaddr1 as domicilio,
			custbranch.brpostaddr3 as municipio,
			custbranch.brpostaddr5 as estado,
			custbranch.brpostaddr4 as cp,
			custbranch.brpostaddr2 as colonia
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
			LEFT JOIN www_users ON www_users.userid = shippinglog.chofer
			WHERE shippinglogid = '{$this->shippingLogId}'
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
		$shippingno     = "";
		$folio_envio 	= "";
		
		if($row = DB_fetch_array($rs)) {
			$name 			= $row['name'];
			$trackingnumber = $row['trackingnumber'];
			$userid 		= $row['userid'];
			$deliverydate 	= $row['deliverydate'];
			$shippingdate 	= $row['deliverydate'];
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
			$camion			= $row['camion'];
			$chofer			= $row['chofer'];
			$this->type		= $row['type'];
			$this->typeno	= $row['typeno'];
			$logo = $row['logo'];
			$logoAlterno = $row['logoAlterno'];
			$domicilio = $row['domicilio'];
			$colonia = $row['colonia'];
			$municipio = $row['municipio'];
			$estado = $row['estado'];
			$cp = $row['cp']; 
			$shippingno = $row['shippingno'];
			$folio_envio = $row['folio_envio']; 
			
		}
		
		$this->Image('.' . $rootpath . '/' . $logo, 10, 5, 40, 20);
		$this->SetFont('helvetica', 'B', 12);
		if(empty($logoAlterno) == false)
		{
		$this->Image('.' . $rootpath . '/' . $logoAlterno,165, 5, 40, 20); 
		$this->SetFont('helvetica', 'B', 12);
		} 
		$folio_envio_tmp = "";
		if (empty($folio_envio) == false) {
			$folio_envio_tmp = " FOLIO: " . $folio_envio; 
		}
		$this->cell('', 4, "DETALLE DE ENVIO ORDEN NO " . $this->shippingLogId . $folio_envio_tmp, 0, 1, 'C');
		$this->SetY(30);
		$this->SetFont('helvetica','B',9);
		$this->SetFillColor(224, 224, 224);//
		
		$this->SetFont('helvetica','B',9);
		$this->SetFillColor(224, 224, 224);
		$this->cell(85, 4, "SUCURSAL DEL CLIENTE", 1, 1, 'L', true);
		$this->SetFont('helvetica','',6);
		$this->cell(15, 4, "Sucursal:", 1, 0, 'L', true);
		$this->MultiCell(70, 4, $branchname, 1, 'L');
		//$this->cell(70, 4, $branchname, 1, 1, 'L');
		$this->cell(15, 4, "Estado:" , 1, 0, 'L', true);
		$this->cell(70, 4, $branchstate, 1, 1, 'L');
		$this->cell(15, 4, "Calle:", 1, 0, 'L', true);
		$this->cell(70, 4, $branchstreet, 1, 1, 'L');
		$this->cell(15, 4, "No:", 1, 0, 'L', true);
		$this->cell(70, 4, $branchno, 1, 1, 'L');
		$this->cell(15, 4, "RFC:", 1, 0, 'L', true);
		$this->cell(70, 4, $branchrfc, 1, 1, 'L');
		
		$this->Ln(4);
		
		$this->SetFont('helvetica','B',9);
		$this->cell(175, 4, "DATOS DE ENVIO", 1, 1, 'L', true);
		$this->SetFont('helvetica','',9);
		$this->cell(30, 4, "Nombre del Envio:", 1, 0, 'L', true);
		$this->cell(145, 4, $name, 1, 1, 'L');
		$this->cell(30, 4, "Número de Guia:" , 1, 0, 'L', true);
		$this->cell(145, 4, $trackingnumber, 1, 1, 'L');
		$this->cell(30, 4, "Usuario de Registro:", 1, 0, 'L', true);
		$this->cell(145, 4, $userid, 1, 1, 'L');
		if($this->FirmaEmbarque <> 1){
			$this->cell(30, 4, "Fecha de Entrega:", 1, 0, 'L', true);
			$this->cell(145, 4, $deliverydate, 1, 1, 'L');
		}else{
			$this->cell(30, 4, "Embarque:", 1, 0, 'L', true);
			$this->cell(145, 4, $shippingno, 1, 1, 'L');
		}
		$this->cell(30, 4, "Fecha de Envio:", 1, 0, 'L', true);
		$this->cell(145, 4, $shippingdate, 1, 1, 'L');
		$this->cell(30, 10, "Comentarios:", 1, 0, 'L', true);
		$this->MultiCell(145, 4, $comments, 1, 'L');
		$this->cell(30, 4, "Chofer:", 1, 0, 'L', true);
		$this->MultiCell(145, 4, $chofer, 1, 'L',true);
		$this->cell(30, 4, "Camion:", 1, 0, 'L', true);
		$this->MultiCell(145, 4, $camion, 1, 'L',true);
		
		$this->Ln(4);
		
		$this->SetFont('helvetica','B',9);
		$this->cell(175, 4, "DATOS DE ENTREGA", 1, 1, 'L', true);
		$this->SetFont('helvetica','',9);
		$this->cell(30, 4, "Domicilio:", 1, 0, 'L', true);
		$this->cell(145, 4, $domicilio, 1, 1, 'L');
		$this->cell(30, 4, "Colonia:" , 1, 0, 'L', true);
		$this->cell(145, 4, $colonia, 1, 1, 'L');
		$this->cell(30, 4, "Municipio y estado", 1, 0, 'L', true);
		$this->cell(145, 4, $municipio.', '.$estado, 1, 1, 'L');//
		$this->cell(30, 4, "CP", 1, 0, 'L', true);
		$this->cell(145, 4, $cp, 1, 1, 'L');
		
		$this->SetY(30);
		$this->SetX(100);
		$this->SetFont('helvetica','B',9);
		$this->SetFillColor(224, 224, 224);
		$this->cell(105, 4, "INFORMACION RAZON SOCIAL", 1, 1, 'L', true);
		$this->SetFont('helvetica','',7);
		$this->SetX(100);
		$this->cell(25, 4, "Razon Social:", 1, 0, 'L', true);
		$this->MultiCell(80, 4, $legalname, 1, 'L');
		//$this->cell(80, 4, $legalname, 1, 1, 'L');
		$this->SetX(100);
		$this->cell(25, 4, "Estado:" , 1, 0, 'L', true);
		$this->cell(80, 4, $legalstate, 1, 1, 'L');
		$this->SetX(100);
		$this->cell(25, 4, "Calle:", 1, 0, 'L', true);
		$this->cell(80, 4, $legalstreet, 1, 1, 'L');
		$this->SetX(100);
		$this->cell(25, 4, "No:", 1, 0, 'L', true);
		$this->cell(80, 4, $legalno, 1, 1, 'L');
		$this->SetX(100);
		$this->cell(25, 4, "RFC:", 1, 0, 'L', true);
		$this->cell(80, 4, $legalrfc, 1, 1, 'L');
		
		$this->SetY(130);
		$this->SetFont('helvetica', 'B', 10);
		$this->cell('', 4, 'DETALLE DE PRODUCTOS', '0', 1, 'C');
		$this->Ln();
		$this->SetFillColor(220, 220, 220);
		$this->SetFont('helvetica', 'B', 8);
		
		if($this->FirmaEmbarque == 1){
			$centerX = 10;
		}else{
			$centerX = $this->w/2 - 150/2;
		}
		
		
		$this->setX($centerX);
		if($this->FirmaEmbarque == 1){
			$this->cell(20, 8, 'Cod. Prod', 1, 0, 'C', true);
			$this->cell(130, 8, 'Descripcion', 1, 0, 'C', true);
			$this->cell(20, 8, 'Cantidad', 1, 0, 'C', true);
			if (!$this->hidePrice) {
				$this->cell(12, 8, 'Precio', 1, 0, 'C', true);
			}
			$this->cell(20, 8, 'Fecha', 1, 0, 'C', true);
		}else{
			$this->cell(50, 8, 'Codigo Producto', 1, 0, 'C', true);
			$this->cell(20, 8, 'Cantidad', 1, 0, 'C', true);
			if (!$this->hidePrice) {
				$this->cell(30, 8, 'Precio', 1, 0, 'C', true);
			}
			$this->cell(50, 8, 'Fecha', 1, 0, 'C', true);
		}
		$this->Ln();

		
	}
	
	function exportPDF(){
					
		//$this->fpdf('L','mm','Letter');
		$this->fpdf('P','mm','Letter');
		
		$this->AliasNbPages();
		$this->SetAutoPageBreak(true,10);
		$this->AddPage();
		
		$this->SetFillColor(255,255,255);
		$this->SetFont('helvetica', '', 6);//
		
		$SQL = "SELECT *, 
					DATE_FORMAT(trandate, '%d-%m-%Y') AS trandatemod, 
					stkmoveno,
					stockmaster.description 
				FROM stockmoves 
				INNER JOIN stockmaster on stockmaster.stockid = stockmoves.stockid
				WHERE type = '{$this->type}' 
				AND transno = '{$this->typeno}'";
		$rs = DB_query($SQL, $this->db);
		
		if($this->FirmaEmbarque == 1){
			$centerX = 10;
		}else{
			$centerX = $this->w/2 - 150/2;
		}
		//
		while($row = DB_fetch_array($rs)) {
			$this->setX($centerX);
			
			if($this->FirmaEmbarque == 1){
				$this->cell(20, 4, $row['stockid'], 1, 0, 'L');
				$this->cell(130, 4, $row['description'], 1, 0, 'L');
				$this->cell(20, 4, $row['qty']*-1, 1, 0, 'L');
				if (!$this->hidePrice) {
					$this->cell(12, 4, $row['price'], 1, 0, 'L');
				}
				$this->cell(20, 4, $row['trandatemod'], 1, 0, 'L');
			}else{
				$this->cell(50, 4, $row['stockid'], 1, 0, 'L');
				$this->cell(20, 4, $row['qty']*-1, 1, 0, 'L');
				if (!$this->hidePrice) {
					$this->cell(30, 4, $row['price'], 1, 0, 'L');
				}
				$this->cell(50, 4, $row['trandatemod'], 1, 0, 'L');
			}//
			
			$SQLSer = "Select shippingserialitems.serialno,
							  shippingserialitems.cantidad
						from shippingserialitems
						where shippingserialitems.shippinglogid = '".$this->shippingLogId."'
						AND shippingserialitems.stockid = '".$row['stockid']."'";
			$rsser = DB_query($SQLSer, $this->db);
			$flag = 1;
			while($myrowser = DB_fetch_array($rsser)){
				if ($flag == 1){
					$this->Ln();
					$flag = 0;
					$this->setX($centerX);
					$this->SetFillColor(220, 220, 220);
					$this->SetFont('helvetica', 'B', 6);
					$this->cell(20, 8, 'Serie', 1, 0, 'C', true);
					$this->cell(20, 8, 'Cantidad', 1, 0, 'C', true);
					$this->Ln();
				}
				$this->setX($centerX);
				$this->SetFillColor(255,255,255);
				$this->SetFont('helvetica', '', 6);
				$this->cell(20, 4, $myrowser['serialno'], 1, 0, 'L');
				$this->cell(20, 4, $myrowser['cantidad'], 1, 0, 'L');
				$this->Ln();
				
				if($this->GetY() >= 200){
					$this->AddPage();
				}
			}
			$this->Ln();
		}
		
		$this->OutPut('','I');
		die();	
	}
	
	function Footer(){
		$this->SetFont('helvetica','B',12);//
		
		if($this->FirmaEmbarque == 1){
			$this->SetX(40);
			$this->SetY(-30);
			$this->cell(80,1,"","B",0,"",true);
			$this->SetX(110);
			$this->cell(40,1,"","B",0,"",true);
			$this->SetX(160);
			$this->cell(40,1,"","B",0,"",true);
			$this->Ln(4);
			
			$this->SetX(20);
			
			$this->cell(40, 8, 'Firma Cliente', 0, 0, 'L', true);
			$this->SetX(120);
			
			$this->SetFont('helvetica','B',7);//
			
			$this->cell(40, 8, 'Firma Calidad', 0, 0, 'L', true);
			$this->SetX(170);
			$this->cell(40, 8, 'Firma Operador', 0, 0, 'L', true);
		}
		
		$this->SetY(-10);
		$this->cell('',5,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
	}
}
?>