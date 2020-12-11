<?php 
define('FPDF_FONTPATH','./fonts/');
include ('includes/fpdf.php');
class Suscripciones extends FPDF {
	var $Recorset;
	var $db;
	function setRecordset($rs) {
		$this->recorset = $rs;
	}

	function setdb($db) {
		$this->db = $db;
	}
	function Header(){
		$this->SetFont('helvetica','B',12);
		$this->SetFillColor(255, 255, 255);
		$this->SetX(80);
		$this->cell(80,  4, _('REPORTE DE SUSCRIPCIONES CANCELADAS').'', 0, 0, 'L', true);
		$this->Ln();
		$this->cell(20,  4, _('De').':', 0, 0, 'L', true);
		$this->cell(20,  4, $_GET['FromFechaLog'], 0, 0, 'L', true);
		$this->cell(20,  4, _('Hasta').':', 0, 0, 'L', true);
		$this->cell(20,  4, $_GET['ToFechaLog'], 0, 0, 'L', true);
		$this->Ln();
		$this->cell(30,  4, _('Ruta').':', 0, 0, 'L', true);//
		if($_GET['Ruta'] == 0){
			$this->cell(50,  4, _('Todas las Rutas'), 0, 0, 'L', true);
		}else{
			$SQL = "SELECT StockCategoriesRutaEntrega.IdRutaEntrega,
						StockCategoriesRutaEntrega.Ruta,
						StockCategoriesRutaEntrega.Responsable,
					FROM StockCategoriesRutaEntrega
					WHERE StockCategoriesRutaEntrega.Activo = 1
					AND StockCategoriesRutaEntrega.IdRutaEntrega = '".$_GET['Ruta']."'";
			$Result = DB_query($SQL, $this->db);
			$myrow = DB_fetch_array($Result);
			$this->cell(50,  4, $myrow['IdRutaEntrega'].'-'.$myrow['Ruta'],0, 0, 'L', true);
			$this->cell(13,  4, _('Responsable').':', 0, 0, 'L', true);
			$this->cell(50,  4, $myrow['Responsable'],0, 0, 'L', true);
		}
		$this->Ln();
		$this->cell(30,  4, _('Categoria').':', 0, 0, 'L', true);
		if($_GET['categorylog'] == '*'){
			$this->cell(50,  4, _('Todas las categorias'), 0, 0, 'L', true);
		}else{
			$SQL = "Select stockcategory.categoryid,
						stockcategory.categorydescription
				 from stockcategory
				 where stockcategory.categoryid = '".$_GET['categorylog']."'";
			$Result = DB_query($SQL, $this->db);
			$myrow = DB_fetch_array($Result);
			$this->cell(70,  4, $myrow['categoryid'].'-'.$myrow['categorydescription'],0, 0, 'L', true);
		}
		$this->Ln(6);
		$this->cell(250,1,"","B",0,"",true);
		$this->Ln(6);
	}
	function exportPDF(){
		$this->fpdf('L','mm','Letter');

		$this->AddPage();
		$this->SetFillColor(255,255,255);
		$this->SetFont('helvetica', '', 6);
		$IdRutaEntrega = "";//
		$CategoriaId = "";
		$flag = 1;
		$Ruta = 0;
		$SumaRuta = 0;
		$SumaDomicilio = 0;
		$TotalSumRuta = 0;
		$TotalSumDom = 0;
		$flagPrimero = 0;
		while($myrow = DB_fetch_array($this->recorset)) {
			if($IdRutaEntrega <> $myrow['IdRutaEntrega']){
				if($flag <> 1){
					$this->AddPage();
					
				}
				$flag = 0;
				$this->cell(20,  4, _('Ruta'), 0, 0, 'L', true);
				$this->cell(40,  4, $myrow['ruta'], 0, 0, 'L', true);
				$this->cell(20,  4, _('Repartidor'), 0, 0, 'L', true);
				$this->cell(20,  4, $myrow['responsable'], 0, 0, 'L', true);
				$this->Ln();
				$IdRutaEntrega = $myrow['IdRutaEntrega'];
				$this->cell(250,1,"","B",0,"",true);
				$this->Ln();
				$this->SetFont('helvetica','B',6);
				$this->SetFillColor(255, 255, 255);
				$this->cell(12,  4, _('Susp'), 0, 0, 'L', true);
				$this->cell(12,  4, _('O.T'), 0, 0, 'L', true);
				$this->cell(12,  4, _('P.V'), 0, 0, 'L', true);//
				$this->cell(12,  4, _('F. Inicial'), 0, 0, 'L', true);
				$this->cell(12,  4, _('F. Can.'), 0, 0, 'L', true);
				$this->cell(40,  4, _('Cliente'), 0, 0, 'L', true);
				$this->cell(7,  4, _('Ej'), 0, 0, 'L', true);
				$this->cell(20,  4, _('Cod. Ant.'), 0, 0, 'L', true);
				$this->cell(30,  4, _('Direccion'), 0, 0, 'L', true);
				$this->cell(50,  4, _('Motivo'), 0, 0, 'L', true);
				$this->cell(50,  4, _('Comentario'), 0, 0, 'L', true);
				$this->Ln();
				$this->SetFont('helvetica','',6);
				$this->SetFillColor(255, 255, 255);
			}
			
			$this->cell(12,  4, $myrow['stockid'], 0, 0, 'L', true);
			$this->cell(12,  4, $myrow['wo'], 0, 0, 'L', true);
			$this->cell(12,  4, $myrow['orderno'], 0, 0, 'L', true);
			$this->cell(12,  4, $myrow['dia'].'/'.$myrow['mes'].'/'.$myrow['anio'], 0, 0, 'L', true);
			$this->cell(12,  4, $myrow['diacan'].'/'.$myrow['mescan'].'/'.$myrow['aniocan'], 0, 0, 'L', true);
			$posantYcliente = $this->GetY();
			$posanXcliente = $this->GetX();
			$this->MultiCell(40,4,$myrow['name'],0,'L');
			$MultiClienteY = $this->GetY();
			$this->SetXY($posanXcliente+40 , $posantYcliente);
			$this->cell(7,  4, $myrow['qtyreqd'], 0, 0, 'L', true);
			$this->cell(20,  4, $myrow['nopedido'], 0, 0, 'L', true);
			$domicilio = $myrow['domicilio'].' '.$myrow['numeroext'];
			if($myrow['numeroint'] <> ""){
				$domicilio = $domicilio.' '.$myrow['numeroint'];
			}
			$domicilio = $domicilio.' col. '.$myrow['colonia'];
			if($myrow['codpos'] <> ""){
				$domicilio = $domicilio . ' cp. '.$myrow['codpos'];
			}
			$posantYdireccion = $this->GetY();
			$posanXdireccion = $this->GetX();
			$this->MultiCell(30,4,$domicilio,0,'L');
			$MultidireccionY = $this->GetY();
			
			$this->SetXY($posanXdireccion+30 , $posantYdireccion);
			$posantYcance = $this->GetY();
			$posanXcance = $this->GetX();
			$this->MultiCell(50,4,$myrow['comcancelaciondes'],0,'L');
			$MulticanceY = $this->GetY();
			
			$this->SetXY($posanXcance+50 , $posantYcance);
			$posantYcomentario = $this->GetY();
			$posanXcomentario = $this->GetX();
			$this->MultiCell(60,4,$myrow['comentario'],0,'L');
			$MulticomentariosY = $this->GetY();
			$this->Ln();
			
			$PosArray = array($MultiClienteY, $MulticanceY, $MulticomentariosY,$MultidireccionY);
			$PosMaxY = max($PosArray);
			if($this->GetY() < $PosMaxY){
				$Diferencia = $PosMaxY - $this->GetY();
			}//
			$this->Ln($Diferencia);
				
			if ($this->GetY() > 180){
			
				$this->AddPage();
			}
			
		}
		$this->OutPut('','I');
		die();
	}
}
?>