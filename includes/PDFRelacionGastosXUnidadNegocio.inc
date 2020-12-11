<?php
	define('FPDF_FONTPATH','./fonts/');
	include ('includes/fpdf.php');
	 
	class PDFRelacionGastosUnidad extends FPDF {
			
		function Header(){
			global $db;
			
			$qry = "Select tagref,reference,date_format(origtrandate,'%d/%m/%Y') as fecha FROM debtortrans 
					WHERE type=".$_GET['type']."
					and transno = ".$_GET['transno']."
					";

			if ($_GET['type']==160)
				$qry = "Select tagref,suppreference as reference,date_format(origtrandate,'%d/%m/%Y') as fecha FROM supptrans 
						WHERE type=".$_GET['type']."
						and transno = ".$_GET['transno']."
						";

			$rs = DB_query($qry,$db);
			$reg = DB_fetch_array($rs);
			
			$tagref =$reg['tagref']; 
			$docs = $reg['reference'];
			$fecha = $reg['fecha'];
			$arrdocs = explode("|",$docs);
			$tipodoc = $arrdocs[0];
			$arrtipodoc = explode("-",$tipodoc);
			$tipo = $arrtipodoc[0];
			$transno = $arrtipodoc[1];
			
			$qry = "Select legalbusinessunit.*
					From supptrans, tags, legalbusinessunit
					WHERE supptrans.tagref = tags.tagref
					and tags.legalid = legalbusinessunit.legalid
					and supptrans.type = '$tipo'
					and supptrans.transno = '$transno'
					";
			$r = DB_query($qry,$db);
			$regs = DB_fetch_array($r);
			$legalname = $regs['legalname'];
					
			
			$qry = "Select legalname, tagname 
					FROM tags, legalbusinessunit
					WHERE tags.legalid = legalbusinessunit.legalid
					and tags.tagref = '$tagref'
					";
			$res = DB_query($qry,$db);
			$myrow = DB_fetch_array($res);
			$razonsocial = $myrow['legalname'];
			$unidadnegocio = $myrow['tagname'];
					
			
			
			$this->Image('.'.$rootpath . '/' . $_SESSION['LogoFile'],10,5,10,15);
			$this->SetFont('helvetica','B',10);
			$this->SetY(10);
			$this->cell('',4,"RELACION DE GASTOS",0,1,'C');
			$this->cell('',4,$razonsocial,0,1,'C');
			$this->cell('',4,$unidadnegocio,0,1,'C');
			$this->SetFont('helvetica','B',8);
			//$this->cell('',4,'Fecha: '.date("d-m-Y"),0,1,'R');
			$this->Ln();
			$this->cell('',4,'RAZON SOCIAL ORIGEN: '.$legalname,0,0,'L');
			
			$this->Ln(10);
			$this->cell('',4,'FECHA: '.$fecha,0,1,'L');
			$this->cell('',4,'FOLIO: '.$_GET['transno'],0,1,'L');
				
			$this->cell('',4,'RELACION DE DOCUMENTOS EN TRASPASO',0,1,'L');
			$this->Ln(4);
			
			if ($_GET['global']==1){
				
				$this->SetFont('helvetica','B',7);
				
				$this->cell(40,4,'Tipo Documento',0,0,'L');
				$this->cell(30,4,'Folio',0,0,'L');
				$this->cell(20,4,'Fecha',0,0,'L');
				$this->cell(80,4,'Acreedor',0,0,'L');
				$this->cell(70,4,'Concepto',0,0,'L');
				$this->cell(20,4,'Total',0,1,'R');
				
				
			}
			
		}
		
		function exportPDF(){
			global $db;

			if ($_GET['global']==1){
				$this->fpdf('L','mm','Letter');
				$this->SetAutoPageBreak(true,20);
					
			}
			else{	
				$this->fpdf('P','mm','Letter');
				$this->SetAutoPageBreak(true,10);
					
			}
			$this->AliasNbPages();
			$this->AddPage();
			
			$this->SetFillColor(255,255,255);
			$this->SetFont('helvetica','',7);
			
			$qry = "Select * FROM debtortrans 
					WHERE type=".$_GET['type']."
					and transno = ".$_GET['transno']."
					";
			if ($_GET['type']==160)
				$qry = "Select suppreference as reference FROM supptrans 
						WHERE type=".$_GET['type']."
						and transno = ".$_GET['transno']."
						";
			//echo $qry;
			$rs = DB_query($qry,$db);
			$reg = DB_fetch_array($rs);
			$docs = $reg['reference'];
			$total=0;
			$arrdoc = explode("|",$docs);
			
			if ($_GET['global']==1){
				//echo 'entro';
				foreach($arrdoc as $tipodoc){
					$arrtipodoc = explode("-",$tipodoc);
					$type = $arrtipodoc[0];
					$transno = $arrtipodoc[1];
						
					$sql = "Select typename,
					supptrans.id,
					suppreference as folio,
					suppliers.suppname,
					date_format(origtrandate,'%d/%m/%Y') as fecha,
					transtext,
					supptrans.ovamount + supptrans.ovgst as total
					FROM supptrans
					left join suppliers
					ON supptrans.supplierno = suppliers.supplierid
					inner join systypescat
					ON supptrans.type = systypescat.typeid
					WHERE type = $type
					and transno = $transno
					";
					$res = DB_query($sql,$db);
					while ($myrow = DB_fetch_array($res)){
						
						$this->cell(40,4,$myrow['typename'],0,0,'L');
						$this->cell(30,4,$myrow['folio'],0,0,'L');
						$this->cell(20,4,$myrow['fecha'],0,0,'L');
						$this->cell(80,4,$myrow['suppname'],0,0,'L');
						$this->cell(70,4,$myrow['transtext'],0,0,'L');
						$this->cell(20,4,number_format($myrow['total'],2),0,1,'R');
						
						$total+=$myrow['total'];
					}
					
				}//foreach
				$this->Ln(6);
				$this->SetFont('helvetica','B',9);
				$this->cell('240',4,'Total Relacion Gastos',0,0,'L');
				$this->cell('20',4,number_format($total,2),0,1,'R');
				
			}
			else{
				
				foreach($arrdoc as $tipodoc){
					$arrtipodoc = explode("-",$tipodoc);
					$type = $arrtipodoc[0];
					$transno = $arrtipodoc[1];
					
					$sql = "Select typename,
									supptrans.id,
									suppreference as folio,
									suppliers.suppname,
									date_format(origtrandate,'%d/%m/%Y') as fecha,
									transtext,
									supptransdetails.stockid,
									ifnull(supptransdetails.description,supptrans.transtext) as description,
									ifnull((price*qty)+(price*qty*(supptrans.ovgst/supptrans.ovamount)),(supptrans.ovamount+supptrans.ovgst)) as total
							FROM supptrans
							left join suppliers
								ON supptrans.supplierno = suppliers.supplierid
							left join supptransdetails
								ON id = supptransid
							left join stockmaster
								ON supptransdetails.stockid = stockmaster.stockid
							inner join systypescat
								ON supptrans.type = systypescat.typeid			
							WHERE type = '".$type."'
							and transno = '".$transno."'
							";
					//echo $sql;
				    $res = DB_query($sql,$db);
					$tiponombre="";
					$totdoc = 0;
					$iddoc = 0;
					while ($myrow = DB_fetch_array($res)){
						$ArrayId .= $myrow['id'].',';
					}					
				}	
				$tamano = strlen($ArrayId);
				$tamano = $tamano -1;
				$ArrayId = substr($ArrayId, 0,$tamano);
				$negocio = 0;
				$bandera = 1;
				$totalnegocio = 0;
				$totalGeneral = 0;
				$sql = "Select typename,
								supptrans.id,
								supptrans.tagref,
								suppreference as folio,
								suppliers.suppname,
								IF(locations.locationname IS NULL, tags.tagname, locations.locationname) AS tagname,
								date_format(origtrandate,'%d/%m/%Y') as fecha,
								transtext,
								supptransdetails.stockid,
								ifnull(supptransdetails.description,supptrans.transtext) as description,
								ifnull((price*qty)+(price*qty*(supptrans.ovgst/supptrans.ovamount)),(supptrans.ovamount+supptrans.ovgst)) as total
						FROM supptrans
						left join suppliers ON supptrans.supplierno = suppliers.supplierid
						left join supptransdetails ON id = supptransid
						left join stockmaster ON supptransdetails.stockid = stockmaster.stockid
						inner join systypescat ON supptrans.type = systypescat.typeid
						inner join tags on tags.tagref = supptrans.tagref
						left join purchorders  on  purchorders.orderno = supptransdetails.orderno
						left join locations on purchorders.intostocklocation = locations.loccode
						WHERE supptrans.id in (".$ArrayId.")
						ORDER BY supptrans.tagref";
				// echo $sql;
				$result = DB_query($sql, $db);
				
				while($myrow = DB_fetch_array($result)){
					
					if($negocio <> $myrow['tagref']){
						if($bandera == 0){
							$this->Ln(4);
							$this->cell('200',0,'',1,0,'C');
							$this->Ln();
							$this->SetFont('helvetica','B',0);
							$this->cell('40',4,'Total Obra',0,0,'L');
							$this->SetX(125);
							$this->cell('20',4,number_format($totalnegocio,2),0,0,'R');
							$negocio = $myrow['tagref'];
							$this->Ln(4);
							$totalnegocio = 0;
						}
						$this->Ln(8);
						$this->SetFont('helvetica','B',10);
						$this->cell('40',4,$myrow['tagname'],0,0,'L');
						$negocio = $myrow['tagref'];
						$this->Ln(8);
						
						$bandera = 0;
					}
					
					
					$this->SetFont('helvetica','',8);
					$this->cell('40',4,$myrow['stockid'],0,0,'L');
					$x=$this->GetX();
					$y=$this->GetY();
					$this->MultiCell('80',4,$myrow['description'],0,'L');
					$posYMultiCell = $this->GetY();
					if ($y < $posYMultiCell){
						$yaux = $y+4;
						while ($yaux < $posYMultiCell){
							$this->SetY($yaux);
							$yaux+=4;
						}
					}
					$this->SetXY($x+80,$y);// para regresar a la posicion sigte
					$totalnegocio = $totalnegocio + $myrow['total'];
					$this->cell('15',4,number_format($myrow['total'],2),0,0,'R');
					//$this->cell('15',4,number_format($totalnegocio,2),0,0,'R');
					$this->Ln();
					$this->SetY($posYMultiCell);
					
					//
					$totalGeneral = $totalGeneral + $myrow['total'];
					$qry = "Select typename, amt
					FROM suppallocs inner join supptrans
					on suppallocs.transid_allocfrom = supptrans.id
					left join systypescat
					on supptrans.type = systypescat.typeid
					WHERE transid_allocto = ".$myrow['id']."
					and supptrans.type<>27
					";
					//echo "<pre>".$qry;exit;
					$rsa = DB_query($qry,$db);
					if (DB_num_rows($rsa) > 0){
					$this->SetFont('helvetica','B',8);
						$this->cell('',5,'ABONOS APLICADOS',0,1,'L');
						$this->SetFont('helvetica','',8);
								while ($regs = DB_fetch_array($rsa)){
										
									$this->cell('40',4,$regs['typename'],0,0,'L');
									$this->cell('80',4,'',0,0,'L');
											$this->cell('15',4,number_format($regs['amt'],2),0,0,'R');
											$this->Ln();
					
											$totdoc-=$regs['amt'];
											$total-=$regs['amt'];
												
												
								}
								}
				}
				$this->Ln(4);
				$this->SetFont('helvetica','B',0);
				$this->cell('200',0,'',1,0,'C');
				$this->Ln();
				$this->cell('40',4,'Total Obra',0,0,'L');
				$this->SetX(125);
				$this->cell('20',4,number_format($totalnegocio,2),0,0,'R');
				$negocio = $myrow['tagref'];
				$this->Ln(6);
				$this->SetFont('helvetica','B',9);
				$this->cell('120',4,'Total Relacion Gastos',0,0,'L');
				$this->cell('15',4,number_format($totalGeneral,2),0,1,'R');
				
			}
			
			$this->OutPut('','I');
			
		}
		
		function Footer(){			
			$this->SetFont('helvetica','B',8);
			$this->SetY(-10);
			$this->cell('60',5,utf8_decode('Fecha de elaboración: ').date("d/m/Y H:i"),0,0,'C');
			$this->cell('',5,utf8_decode('Página ').$this->PageNo().'/{nb}',0,0,'R');
		}
	
	}



?>