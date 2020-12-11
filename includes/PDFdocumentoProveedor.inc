<?php
	/*
	 * Elaboro Jesus Vargas Montes
	 * Fecha Modificacion 30 Abril 2013
	 * Cambios
	 * 1. Se agrego en la consulta el campo invtext para obtener los comentarios de debtortrans y desplegarlo en la columna de comentarios
	 */
	define('FPDF_FONTPATH','./fonts/');
	include ('includes/fpdf.php');
	 
	class PDFDocumentosProveedor extends FPDF {
			
		function Header(){
			global $db,$saldoI;
						
			$cliente =$_GET['proveedor']; 
			
			
			$this->SetFont('helvetica','B',10);
			$this->SetY(10);
			$this->cell('',4,"ESTADO DE CUENTA",0,1,'C');
			$this->cell('',4,'CLIENTE: '.$cliente,0,1,'C');

			$fi = substr($_GET['fechai'],8,2) ."-". substr($_GET['fechai'],5,2) ."-".substr($_GET['fechai'],0,4);
			$ff = substr($_GET['fechaf'],8,2) ."-". substr($_GET['fechaf'],5,2) ."-".substr($_GET['fechaf'],0,4);
				
			
			$this->Ln(10);
			$this->cell('',4,'RELACION DE DOCUMENTOS DESDE '.$fi.' HASTA '.$ff,0,1,'L');
			$this->Ln(4);
			//legalid
			//saldo inicial
			$qry = "Select IFNULL(SUM(cargos-abonos),0) as saldo from(
						Select 
							if(ovamount >0,(ovamount+ovgst),0) as cargos,
							if(ovamount < 0,-1*(ovamount+ovgst),0) as abonos 
						FROM supptrans left join tags on tags.tagref=supptrans.tagref
						WHERE origtrandate < '".$_GET['fechai']."' 
							and supplierno = '".$_GET['supplierno']."'";
							
			if($_GET['legalid']!='' and $_GET['legalid']!=0 ){
					$qry = $qry ." And tags.legalid=".$_GET['legalid'];
			}
			if (isset($_GET['todaslasunidades']) AND strlen($_GET['todaslasunidades']) > 0 and  $_GET['todaslasunidades']!='0') {
					$qry .= " AND supptrans.tagref='".$_GET['todaslasunidades']."'";
			}
			if (!isset($_GET['todaslasunidades'])){
					$qry .= " AND supptrans.tagref  in (select tagref from sec_unegsxuser where userid='".$_SESSION['UserID']."')";
			}
						
			$qry = $qry ." and ovamount <> 0) R";
			if ($_SESSION['UserID']=='admin'){
				//echo '<pre><br>'.$qry;
				//exit;
			}
			$rs = DB_query($qry,$db);
			$reg = DB_fetch_array($rs);
			
			$saldoI = $reg['saldo'];
			
			
			
			$this->SetFont('helvetica','B',9);
			$this->cell('190',4,'Saldo Inicial  '.number_format($saldoI,2),0,1,'R');
			$this->Ln();
			
			$this->SetFont('helvetica','B',7);

			$this->cell(15,4,'Fecha',0,0,'L');
			$this->cell(8,4,'Folio',0,0,'L');
			$this->cell(40,4,'Tipo Documento',0,0,'L');		
			$this->cell(80,4,'Comentario',0,0,'L');
			$this->cell(25,4,'Cargo',0,0,'R');
			$this->cell(25,4,'Abono',0,1,'R');
				
			
		}
		
		function exportPDF(){
			global $db,$saldoI;

			$this->fpdf('P','mm','Letter');
			$this->SetAutoPageBreak(true,10);
			$this->AliasNbPages();
			$this->AddPage();
			
			$this->SetFillColor(255,255,255);
			$this->SetFont('helvetica','',7);
			
			$sql = "Select typename,
							type,
							transno,
							origtrandate,
							transtext,
							date_format(origtrandate,'%d/%m/%Y') as fecha,
							if(ovamount >0,(ovamount+ovgst),0) as cargos,
							if(ovamount < 0,-1*(ovamount+ovgst),0) as abonos 
						FROM supptrans
						LEFT JOIN systypescat
						ON supptrans.type = systypescat.typeid
						left join tags on tags.tagref=supptrans.tagref
						LEFT JOIN areas a ON tags.areacode = a.areacode
						LEFT JOIN departments d ON tags.u_department = d.u_department
			
						WHERE origtrandate BETWEEN '".$_GET['fechai']."' and '".$_GET['fechaf']."'
						and supplierno = '".$_GET['supplierno']."'
						and ovamount <> 0
						";
					if($_GET['legalid']!='' and $_GET['legalid']!=0 ){
						$sql = $sql ." And tags.legalid=".$_GET['legalid'];
					}
					if (isset($_GET['todaslasunidades']) and strlen($_GET['todaslasunidades'])>0) {
						$sql .= " AND supptrans.tagref=".$_GET['todaslasunidades'];
					}
					
					if (!isset($_GET['todaslasunidades'])){
						$sql .= " AND supptrans.tagref  in (select tagref from sec_unegsxuser where userid='".$_SESSION['UserID']."')";
					}
					if (!isset($_GET['solosaldo'])) { 
						$sql .= " AND (supptrans.ovamount + supptrans.ovgst-supptrans.alloc)>0
		  				AND ABS((supptrans.ovamount+supptrans.ovgst)-supptrans.alloc)>".$_SESSION['MaxDecimalcxc'];
					}  
						
				
			$sql = $sql ." Order By origtrandate ";
			$rs = DB_query($sql,$db);
			if($_SESSION['UserID'] == "admin"){
				echo '<pre>'.$sql;
			}
			$totalAbonos=0;
			$totalCargos=0;
			
			while ($myrow = DB_fetch_array($rs)){
				$cargos = "";
				if ($myrow['cargos'] > 0)
					$cargos = number_format($myrow['cargos'],2);
				
				$abonos="";
				if ($myrow['abonos'] > 0)
					$abonos = number_format($myrow['abonos'],2);

				$coment="";
				if ($myrow['type']==10){
					$qry = "Select narrative from stockmoves
								where type=10 and transno = '".$myrow['transno']."'";
					$r = DB_query($qry,$db);
					$reg = DB_fetch_array($r);
					$coment = $reg['narrative'];
				}
				$coment = $coment.$myrow['invtext'];	
				
				$this->cell(15,4,$myrow['fecha'],0,0,'L');
				$this->cell(8,4,$myrow['transno'],0,0,'L');
				$this->cell(40,4,$myrow['typename'],0,0,'L');
				$posY = $this->GetY();
				$posX = $this->GetX();
				$newline=$posY;
				$this->Multicell(80,4,$coment,0,'L');
				if ($this->GetY() > $posY){
					$newline = $this->GetY();
					$this->SetXY($posX+80,$posY);
				}
					
				$this->cell(25,4,$cargos,0,0,'R');
				$this->cell(25,4,$abonos,0,1,'R');

				if ($this->GetY() < $newline)
					$this->SetY($newline);
						
				$totalCargos+=$myrow['cargos'];
				$totalAbonos+=$myrow['abonos'];
			}
					
			$this->Ln(6);
			$this->SetFont('helvetica','B',9);
			$this->cell('143',4,'Totales',0,0,'L');
			$this->cell('25',4,number_format($totalCargos,2),0,0,'R');
			$this->cell('25',4,number_format($totalAbonos,2),0,1,'R');
			$this->Ln();
			$this->cell('190',4,'Saldo del Periodo '.number_format($totalCargos - $totalAbonos,2),0,1,'R');
			$this->Ln();
			if(($saldoI + ($totalCargos - $totalAbonos))){
				$this->cell('190',4,'Saldo A Cargo de Cliente '.number_format($saldoI + ($totalCargos - $totalAbonos),2),0,1,'R');
			}else{
				$this->cell('190',4,'Saldo A Favor de Cliente '.number_format($saldoI + ($totalCargos - $totalAbonos),2),0,1,'R');
			}
				
			
			
			$this->OutPut('','I');
			
		}
		
		function Footer(){			
			$this->SetFont('helvetica','B',8);
			$this->SetY(-10);
			$this->cell('60',5,utf8_decode('Fecha de impresión: ').date("d/m/Y H:i"),0,0,'C');
			$this->cell('',5,utf8_decode('Página ').$this->PageNo().'/{nb}',0,0,'R');
		}
	
	}



?>