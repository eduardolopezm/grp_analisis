<?php
	
	include('PDFPurchOrderNew.inc');
	
	function nombremeslargo($idmes){
		$nombremeslargo = "";
		switch ($idmes) {
			case 1:
				$nombremeslargo = "Enero";
				break;
			case 2:
				$nombremeslargo = "Febrero";
				break;
			case 3:
				$nombremeslargo = "Marzo";
				break;
			case 4:
				$nombremeslargo = "Abril";
				break;
			case 5:
				$nombremeslargo = "Mayo";
				break;
			case 6:
				$nombremeslargo = "Junio";
				break;
			case 7:
				$nombremeslargo = "Julio";
				break;
			case 8:
				$nombremeslargo = "Agosto";
				break;
			case 9:
				$nombremeslargo = "Septiembre";
				break;
			case 10:
				$nombremeslargo = "Octubre";
				break;
			case 11:
				$nombremeslargo = "Noviembre";
				break;
			case 12:
				$nombremeslargo = "Diciembre";
				break;
	
		}
		return $nombremeslargo;
	
	}
	
	$_GET['tipodocto']=25;
	$_GET['OrderNo'] = $OrderNoCompra;
	$pdf = new pdfpurchorder();
	$pdf->printPDF($direcciondos);
	
	
 
?>