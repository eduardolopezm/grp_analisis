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
		$this->SetX(100);
		$this->cell(30,  4, _('Periodo del').':', 0, 0, 'L', true);
		$this->cell(20,  4, $_GET['FromFechaLog'], 0, 0, 'L', true);
		$this->cell(10,  4, _(' Al').':', 0, 0, 'L', true);
		$this->cell(20,  4, $_GET['ToFechaLog'], 0, 0, 'L', true);
		$this->Ln();
		$sql = "SELECT stockcategory.categoryid,
										stockcategory.categorydescription
								FROM stockcategory
								WHERE stockcategory.categoryid ='".$_GET['categorylog']."'";
		$result = DB_query($sql, $this->db);
		$myrrow = DB_fetch_array($result);
		if($_GET['categorylog'] <> "*"){
			$this->cell(20,  4, _('Seccion').':', 0, 0, 'L', true);
			$this->cell(20,  4, $myrrow['categoryid'], 0, 0, 'L', true);
			$this->cell(20,  4, $myrrow['categorydescription'], 0, 0, 'L', true);
			$this->Ln();
		}
		$this->Ln(2);
		$this->cell(15,  4, _('O.T.'), 0, 0, 'L', true);
		$this->cell(15,  4, _('P.V.'), 0, 0, 'L', true);
		$this->cell(15,  4, _('Ant Cod.'), 0, 0, 'L', true);
		$this->cell(15,  4, _('Cod.'), 0, 0, 'L', true);
		$this->cell(35,  4, _('Descripcion'), 0, 0, 'L', true);
		$this->cell(40,  4, _('Razon Social'), 0, 0, 'L', true);
		$this->cell(45,  4, _('Direccion'), 0, 0, 'L', true);
		$this->cell(15,  4, _('Inicio'), 0, 0, 'L', true);
		$this->cell(15,  4, _('Vence'), 0, 0, 'L', true);
		$this->cell(20,  4, _('Telefono'), 0, 0, 'L', true);
		$this->cell(10,  4, _('Can.'), 0, 0, 'L', true);
		$this->cell(30,  4, _('Comentarios'), 0, 0, 'L', true);
		$this->Ln(6);
		$this->cell(260,1,"","B",0,"",true);
		$this->Ln(6);
	}
	function exportPDF(){
		$this->fpdf('L','mm','Letter');
		$this->AddPage();
		$this->SetFillColor(255,255,255);
		$this->SetFont('helvetica', '', 6);
		$friendlymes = array(1=>"Ene",2=>"Feb",3=>"Mar",4=>"Abr",5=>"May",6=>"Jun",7=>"Jul",8=>"Ago",9=>"Sep",10=>"Oct",11=>"Nov",12=>"Dic");
		$catego = "";
		while($myrow = DB_fetch_array($this->recorset)) {
			$DiaXEntrega = 1;
			$DiaSemana = $myrow['fechasemana'];
			$LunesArray = Array(1 => "Domingo",
					2 => "Lunes",
					3 => "Martes",
					4 => "Miercoles",
					5 => "Jueves",
					6 => "Viernes",
					7 => "Sabado");
			$DiaSemanaLetra = $LunesArray[$DiaSemana];
			
			$sql = "SELECT salesstockproperties.valor
						FROM salesstockproperties
							INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
						WHERE salesstockproperties.orderno = '".$myrow['orderno']."'
							AND salesstockproperties.orderlineno = '".$myrow['orderlineno']."'
							AND stockcatproperties.controltype = 2
							AND stockcatproperties.label like '%".$DiaSemanaLetra."%'";
			
			$result = DB_query($sql, $this->db);
			
			$Colonia = "";
			$Domicilio = "";
			$flagSiNo = 0;
			while($myrow2 = DB_fetch_array($result)){
				if($myrow2['valor'] == 'SI'){
					$flagSiNo = 1;
				}elseif($myrow2['valor'] == 'NO' and $flagSiNo == 0){
					$DiaXEntrega = $DiaXEntrega + 1;
				}
			}
			
			
			$sql = "SELECT salesstockproperties.valor
						FROM salesstockproperties
							INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
						WHERE salesstockproperties.orderno ='".$myrow['orderno']."'
							AND salesstockproperties.orderlineno ='".$myrow['orderlineno']."'
							AND stockcatproperties.controltype = 1
							AND stockcatproperties.label like '%Dom%'";
			$result = DB_query($sql,  $this->db);
			$Contador = 1;
			while($myrow2 = DB_fetch_array($result)){
				if($Contador == $DiaXEntrega){
					$Domicilio = $myrow2['valor'];
					$Contador = $Contador + 1;
				}else{
					$Contador = $Contador + 1;
				}
			}
			$sql = "SELECT salesstockproperties.valor
						FROM salesstockproperties
							INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
						WHERE salesstockproperties.orderno ='".$myrow['orderno']."'
							AND salesstockproperties.orderlineno ='".$myrow['orderlineno']."'
							AND stockcatproperties.controltype = 1
							AND stockcatproperties.label like '%col%'";
			$result = DB_query($sql,  $this->db);
			$Contador = 1;
			while($myrow2 = DB_fetch_array($result)){
				if($Contador == $DiaXEntrega){
					$Colonia = $myrow2['valor'];
					$Contador = $Contador + 1;
				}else{
					$Contador = $Contador + 1;
				}
			}
			$sql = "SELECT salesstockproperties.valor
						FROM salesstockproperties
							INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
						WHERE salesstockproperties.orderno ='".$myrow['orderno']."'
							AND salesstockproperties.orderlineno ='".$myrow['orderlineno']."'
							AND stockcatproperties.controltype = 1
							AND stockcatproperties.label like '%Numero Ext%'";
			$result = DB_query($sql,  $this->db);
			$Contador = 1;
			while($myrow2 = DB_fetch_array($result)){
				if($Contador == $DiaXEntrega){
					$NumExt = $myrow2['valor'];
					$Contador = $Contador + 1;
				}else{
					$Contador = $Contador + 1;
				}
			}
			$sql = "SELECT salesstockproperties.valor
						FROM salesstockproperties
							INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
						WHERE salesstockproperties.orderno ='".$myrow['orderno']."'
							AND salesstockproperties.orderlineno ='".$myrow['orderlineno']."'
							AND stockcatproperties.controltype = 1
							AND stockcatproperties.label like '%Numero Int%'";
			$result = DB_query($sql,  $this->db);
			$Contador = 1;
			while($myrow2 = DB_fetch_array($result)){
				if($Contador == $DiaXEntrega){
					$NumInt = $myrow2['valor'];
					$Contador = $Contador + 1;
				}else{
					$Contador = $Contador + 1;
				}
			}
			$sql = " SELECT salesstockproperties.valor
													FROM salesstockproperties
													INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
													WHERE salesstockproperties.orderno ='".$myrow['orderno']."'
													AND salesstockproperties.orderlineno ='".$myrow['orderlineno']."'
													AND stockcatproperties.controltype = 1
													AND stockcatproperties.label like '%CP%'";
			$result = DB_query($sql,  $this->db);
			$Contador = 1;
			while($myrow2 = DB_fetch_array($result)){
				if($Contador == $DiaXEntrega){
					$CP = $myrow2['valor'];
					$Contador = $Contador + 1;
				}else{
					$Contador = $Contador + 1;
				}
			}
			$sql = " SELECT salesstockproperties.valor
													FROM salesstockproperties
													INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
													WHERE salesstockproperties.orderno ='".$myrow['orderno']."'
													AND salesstockproperties.orderlineno ='".$myrow['orderlineno']."'
													AND stockcatproperties.controltype = 1
													AND stockcatproperties.label like '%Entre%'";
			$result = DB_query($sql,  $this->db);
			$Contador = 1;
			while($myrow2 = DB_fetch_array($result)){
				if($Contador == $DiaXEntrega){
					$EntreCalle = $myrow2['valor'];
					$Contador = $Contador + 1;
				}else{
					$Contador = $Contador + 1;
				}
			}
			$sql = " SELECT salesstockproperties.valor
													FROM salesstockproperties
													INNER JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
													WHERE salesstockproperties.orderno ='".$myrow['orderno']."'
													AND salesstockproperties.orderlineno ='".$myrow['orderlineno']."'
													AND stockcatproperties.controltype = 1
													AND stockcatproperties.label like '%estado%'";
			$result = DB_query($sql,  $this->db);
			$Contador = 1;
			while($myrow2 = DB_fetch_array($result)){
				if($Contador == $DiaXEntrega){
					$Estado = $myrow2['valor'];
					$Contador = $Contador + 1;
				}else{
					$Contador = $Contador + 1;
				}
			}
			if($Domicilio <> ""){
				$Direccion = $Domicilio;
				if($NumExt <> ""){
					$Direccion = $Direccion.' Num. Ext. '.$NumExt;
				}
				if($NumInt <> ""){
					$Direccion = $Direccion.' Num. Int. '.$NumInt;
				}
				if($NumExt <> ""){
					$Direccion = $Direccion.' Num. Int. '.$NumInt;
				}
				if($Colonia <> ""){
					$Direccion = $Direccion.' Col. '.$Colonia;
				}
				if($CP <> ""){
					$Direccion = $Direccion.' CP. '.$CP;
				}
				if($Estado <> ""){
					$Direccion = $Direccion.' Mun y Edo '.$Estado;
				}
				if($EntreCalle <> ""){
					$Direccion = $Direccion.' Entre Calles '.$EntreCalle;
				}
			}
			if($catego <> $myrow['categoryid']){
				$this->SetFillColor(255,255,255);
				$this->SetFont('helvetica', 'B', 10);
				$this->Ln();
				$this->SetX(100);
				$this->cell(20,  4, _('Seccion'), 0, 0, 'L', true);
				$this->cell(20,  4, $myrow['categoryid']." - ".$myrow['categorydescription'], 0, 0, 'L', true);
				$catego = $myrow['categoryid'];
				$this->Ln();
			}
			$this->SetFillColor(255,255,255);
			$this->SetFont('helvetica', '', 6);
			$this->Ln();
			$this->cell(15,  4, $myrow['wo'], 0, 0, 'L', true);
			$this->cell(15,  4, $myrow['orderno'], 0, 0, 'L', true);
			$this->cell(15,  4, $myrow['nopedido'], 0, 0, 'L', true);
			$this->cell(15,  4, $myrow['stockid'], 0, 0, 'L', true);
			
			$MultiDescripcionY = $this->GetY();
			$MultiDescripcionX = $this->GetX();
			$this->MultiCell(35, 4, $myrow['description'],0,'L');
			$MultiDescripcionYDespues = $this->GetY();
			$this->SetXY($MultiDescripcionX+35, $MultiDescripcionY);
			$MultiRazonY = $this->GetY();
			$MultiRazonX = $this->GetX();
			$this->MultiCell(40, 4, $myrow['name'],0,'L');
			$MultiRazonYDespues = $this->GetY();
			$this->SetXY($MultiRazonX+40, $MultiRazonY);
			$MultiDireccionY = $this->GetY();
			$MultiDireccionX = $this->GetX();
			$this->MultiCell(45, 4,$Direccion,0,'L');
			$MultiDireccionYDespues = $this->GetY();
			$this->SetXY($MultiDireccionX+45, $MultiDireccionY);
			$this->cell(15,  4, $myrow['dia'].' '.$friendlymes[$myrow['mes']].' '.$myrow['anio'], 0, 0, 'L', true);
			$this->cell(15,  4, $myrow['diavencimiento'].' '.$friendlymes[$myrow['mesvencimiento']].' '.$myrow['yearvencimiento'], 0, 0, 'L', true);
			$Telefono = "";
			$Comentarios = "";
			if($myrow['phoneno'] <> ""){
				$Telefono = $Telefono ."Tel.: ".$myrow['phoneno'].' ';
			}elseif($myrow['movilno'] <> ""){
				$Telefono = $Telefono ."Cel.: ".$myrow['movilno'].' ';
			}elseif($myrow['nextelno'] <> ""){
				$Telefono = $Telefono ."Cel.: ".$myrow['nextelno'].' ';
			}
			$this->cell(20,  4, $Telefono, 0, 0, 'L', true);
			$this->cell(10,  4, $myrow['qtyreqd'], 0, 0, 'C', true);
			$sql2 = "SELECT wocontrolpanel_comentarios.comentario
						FROM wocontrolpanel_comentarios
						WHERE wocontrolpanel_comentarios.wo = '".$myrow['wo']."'
						Limit 1";
			$result2 = DB_query($sql2, $this->db);
			while($myrow2 = DB_fetch_array($result2)){
				$Comentarios = $myrow2['comentario'];
			}
			$MultiComentariosY = $this->GetY();
			$MultiComentariosX = $this->GetX();
			$this->MultiCell(30, 4, $Comentarios,0,'L');
			$MultiComentariosYDespues = $this->GetY();
			$PosArray = array($MultiDescripcionYDespues, $MultiRazonYDespues, $MultiDireccionYDespues, $MultiComentariosYDespues);
			$PosMaxY = max($PosArray);
			if($this->GetY() < $PosMaxY){
				$Diferencia = $PosMaxY - $this->GetY();
			}
			$contador = $contador + 1;
			if($contador = 2){
				//echo $Diferencia;//
			}
			$this->Ln($Diferencia);
			if($this->GetY() > 170){
				$this->AddPage();
			}
			//$this->SetXY($MultiComentariosX+50, $MultiComentariosY);
		}
		
		/*$this->Ln(20);
		$this->cell(20,  4, $MultiDescripcionYDespues, 0, 0, 'L', true);
		$this->cell(20,  4, $MultiRazonYDespues, 0, 0, 'L', true);
		$this->cell(20,  4, $MultiDireccionYDespues, 0, 0, 'L', true);
		$this->cell(20,  4, $MultiComentariosYDespues, 0, 0, 'L', true);
		
		$this->cell(20,  4, $PosMaxY, 0, 0, 'L', true);*/
		
		$this->OutPut('','I');
		die();
	}
}
?>