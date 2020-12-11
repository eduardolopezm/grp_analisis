<?php
	define('FPDF_FONTPATH','./fonts/');
	include ('includes/fpdf.php');
	 
	class PDFShipment extends FPDF {
			
		function Header(){
			global $db,$shiptref;
						
			$shiptref =$_GET['shiptref']; 
			//obtener razon social y logo
			$qry = "Select legalname,logo,vessel from shipments 
						inner join locations ON shipments.location = locations.loccode
						inner join tags ON locations.tagref = tags.tagref
						inner join legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
					where shiptref=$shiptref";
			
			//echo "<pre>$qry";
			
			$rs = DB_query($qry,$db);
			$row = DB_fetch_array($rs);
			$razonsocial = $row['legalname'];
			$logo = $row['logo'];
			$referencia = $row['vessel'];
			
			//buscar fecha cierre de embarque
			$sql = "Select distinct DATE_FORMAT(trandate,'%d/%m/%Y') as fecha 
					from gltrans
					where type=31
					and typeno=$shiptref";
			$res = DB_query($sql,$db);
			$reg = DB_fetch_array($res);
			$fechacierre = $reg['fecha'];
					
			
			$this->Image($logo, 10, 5,20,15);
			
			$this->SetFont('helvetica','B',10);
			$this->SetY(25);
			$this->cell('',5,$razonsocial,0,0,'L');
			$this->SetFont('helvetica','B',12);
			$this->cell('',5,'INTEGRACION DE COSTOS',0,1,'R');
			$this->SetFont('helvetica','B',10);
				
			$this->cell(15,5,utf8_decode('Fecha: '),0,0,'L');
			$this->SetFont('helvetica','',10);
			$this->cell(15,5,date("d/m/Y"),0,0,'L');
			$this->SetFont('helvetica','B',10);
			$this->cell(150,5,utf8_decode('Folio: '),0,0,'R');
			$this->SetFont('helvetica','',10);
			$this->cell(17,5,$shiptref,0,1,'R');
			$this->SetFont('helvetica','B',10);
			$this->cell(15,5,utf8_decode('Hora: '),0,0,'L');
			$this->SetFont('helvetica','',10);
			$this->cell(15,5,date("H:i:s"),0,0,'L');
			$this->SetFont('helvetica','B',10);
			$this->cell(150,5,utf8_decode('Fecha: '),0,0,'R');
			$this->SetFont('helvetica','',10);
			$this->cell(17,5,$fechacierre,0,1,'R');
			$this->SetFont('helvetica','B',10);
			$this->cell(185,5,utf8_decode('Página: '),0,0,'R');
			$this->SetFont('helvetica','',10);
			$this->cell(17,5,$this->PageNo().'/{nb}',0,1,'R');
			
			$this->SetFont('helvetica','B',10);
			$this->cell(20,5,'Referencia: ','B',0,'L');
			$this->SetFont('helvetica','',10);
			$this->cell('',5,$referencia,'B',1,'L');
			
		}
		
		function exportPDF(){
			global $db,$shiptref;

			$this->fpdf('P','mm','Letter');
			$this->SetAutoPageBreak(true,10);
			$this->AliasNbPages();
			$this->AddPage();
			
			$this->SetFont('helvetica','B',8);
			$this->cell('',4,'Facturas Relacionadas',0,1,'L');	
			$this->SetFont('helvetica','',8);
			
			$this->cell(15,4,'Factura',0,0,'C');
			$this->cell(15,4,'Prov.',0,0,'C');
			$this->cell(100,4,'Nombre',0,0,'C');
			$this->cell(25,4,'Prorrateo',0,0,'C');
			$this->cell(20,4,'Sub Total MN',0,1,'C');
				
			//buscar facturas relacionadas
			$sql = "SELECT distinct 
						supptrans.supplierno,
						suppliers.suppname,
						supptrans.type,
						supptrans.transno,
						shiptypecost.costname,
						supptrans.ovamount/supptrans.rate as subtotalMN,
						(supptrans.ovamount+supptrans.ovgst)/supptrans.rate as totalMN
					FROM supptrans 
					INNER JOIN shipmentcharges
						ON shipmentcharges.transtype=supptrans.type
						AND shipmentcharges.transno=supptrans.transno
					INNER JOIN suppliers
						ON suppliers.supplierid=supptrans.supplierno
					INNER JOIN systypescat
						ON systypescat.typeid=supptrans.type
					LEFT JOIN shiptypecost
						ON shipmentcharges.typecost = shiptypecost.typecostid	
					WHERE shipmentcharges.shiptref=$shiptref
					and supptrans.type=20
					ORDER BY supptrans.supplierno,
						supptrans.transno";
			$rs = DB_query($sql,$db);
			$subT = 0;
			$transno="";
			$suppcode="";
			$suppname="";
			$subt = 0;
			while ($rows = DB_fetch_array($rs)){
				
				if ($transno != $rows['transno']){
					
					if ($transno!=""){
						$this->cell(15,4,$transno,0,0,'C');
						$this->cell(15,4,$suppcode,0,0,'C');
						$posY = $this->GetY();
						$posX = $this->GetX();
						$newlineY = $posY;
						$this->MultiCell(100,4,$suppname,0,'L');
						if ($this->GetY() > $posY){
							$newlineY = $this->GetY();
							$this->SetXY($posX+100,$posY);
						}
						
						$this->cell(25,4,$costname,0,0,'C');
						$this->cell(20,4,number_format($subt,2),0,1,'R');
						if ($this->GetY() < $newlineY)
							$this->SetY($newlineY);
						
					}
					
					$transno = $rows['transno'];
					$suppcode = $rows['supplierno'];
					$suppname = $rows['suppname'];
					$costname=$rows['costname'];
					$subt=$rows['subtotalMN'];
					
					$subT+=$rows['subtotalMN'];
				}
				else
					$costname.=" ".$rows['costname'];
				
				
			}
			
			if ($transno!=""){
				$this->cell(15,4,$transno,0,0,'C');
				$this->cell(15,4,$suppcode,0,0,'C');
				$posY = $this->GetY();
				$posX = $this->GetX();
				$newlineY = $posY;
				$this->MultiCell(100,4,$suppname,0,'L');
				if ($this->GetY() > $posY){
					$newlineY = $this->GetY();
					$this->SetXY($posX+100,$posY);
				}
			
				$this->cell(25,4,$costname,0,0,'C');
				$this->cell(20,4,number_format($subt,2),0,1,'R');
				if ($this->GetY() < $newlineY)
					$this->SetY($newlineY);
			
			}
			
			
			$this->cell(155,4,'',0,0,'R');;
			$this->cell(20,4,number_format($subT,2),'T',1,'R');;
			
			$this->Ln(8);
			$this->SetFont('helvetica','B',8);
			$this->cell('',4,'Movimientos',0,1,'L');
			
			$this->SetFont('helvetica','',7);
			//$this->cell(7,4,'No.Mov','B',0,'C');
			$this->cell(12,4,'Factura','B',0,'C');
			$this->cell(25,4,'Prod','B',0,'C');
			$this->cell(60,4,'Descripcion','B',0,'C');
			$this->cell(7,4,'Unid.','B',0,'C');
			$this->cell(10,4,'Cant.','B',0,'C');
			$this->cell(15,4,'Cost.Rec.','B',0,'C');
			$this->cell(15,4,'Ajuste Tot','B',0,'C');
			$this->cell(15,4,'Cargos Fact.','B',0,'C');
			$this->cell(15,4,'Cargos Emb.','B',0,'C');
			$this->cell(20,4,'Cost.Unit','B',1,'C');
			
			$TItemCharges = 0;
			$TPortionOfCharges = 0;
			$TItemShipmentCost = 0;
			
			$sql = "SELECT SUM(value) FROM shipmentcharges WHERE stockid='' and typecost=1 AND shiptref =" . $shiptref;
			
			$ErrMsg = _('Embarque') . ' ' . $_GET['SelectedShipment'] . ' ' . _('costos generales no pueden ser recuperados de la base de datos');
			$GetShiptCostsResult = DB_query($sql,$db, $ErrMsg);
			$myrow = DB_fetch_row($GetShiptCostsResult);
			
			$TotalCostsToApportion = $myrow[0];
			
			
			$sql = "SELECT SUM(value) FROM shipmentcharges WHERE stockid<>'' AND shiptref =" . $shiptref;
			
			$ErrMsg = _('Embarque') . ' ' . $_GET['SelectedShipment'] . ' ' . _('Costo de Productos no se encontraron');
			$GetShiptCostsResult = DB_query($sql,$db);
			$myrow = DB_fetch_row($GetShiptCostsResult);
			$TotalInvoiceValueOfShipment = $myrow[0];
			
			$sql = "SELECT SUM(kgs) , SUM(percentfactorigi)
					FROM stockmaster
						inner join shipmentcharges
					WHERE stockmaster.stockid = shipmentcharges.stockid
					AND shiptref =" . $shiptref;
			
			$GetShiptWeightResult = DB_query($sql,$db);
			$myrow = DB_fetch_row($GetShiptWeightResult);
			$TotalCostsWeight  = $myrow[0];
			$TotalIGI = $myrow[1];
			
			
			$sql = "SELECT SUM(value) AS invoicedcharges
					FROM shipmentcharges
					WHERE typecost=2
					and shiptref =" . $shiptref;
						
			$res = DB_query($sql,$db);
			$myrow = DB_fetch_row($res);
			$TotalWeightInvoiced  = $myrow[0];
			
			$sql = "SELECT SUM(value) AS invoicedcharges
					FROM shipmentcharges
					WHERE typecost=3
					and shiptref =" . $shiptref;
			
			$rs = DB_query($sql,$db);
			$myrow = DB_fetch_row($rs);
			$TotalIGIInvoiced  = $myrow[0];

			$arrCostDetail = array();
			
			$qry="SELECT purchorderdetails.itemcode,
						stockserialmoves.serialno,
						purchorderdetails.itemdescription,
						purchorderdetails.qtyinvoiced as totqtyinvoiced,
						purchorderdetails.quantityrecd as totqtyrecd,
						shipmentcharges.transno
					FROM purchorderdetails JOIN grns ON grns.podetailitem = purchorderdetails.podetailitem
							LEFT JOIN stockmoves ON grns.grnbatch =  stockmoves.transno and grns.itemcode=stockmoves.stockid and stockmoves.qty=(grns.qtyrecd*factorconversion)
							LEFT JOIN stockserialmoves ON stockserialmoves.stockmoveno = stockmoves.stkmoveno and stockserialmoves.stockid=stockmoves.stockid
							LEFT JOIN shipmentcharges ON purchorderdetails.shiptref = shipmentcharges.shiptref
													and purchorderdetails.itemcode = shipmentcharges.stockid
					WHERE purchorderdetails.shiptref=" . $shiptref . " and stockmoves.type = 25 and grns.qtyrecd <> 0
					Order By transno";
			$res = DB_query($qry,$db);
			while ($myrow = DB_fetch_array($res)){
				
				$qry = "Select kgs as peso, percentfactorigi,units
						FROM stockmaster
						WHERE stockid = '".$myrow['itemcode'] ."'";
				$rs = DB_query($qry,$db);
				$row = DB_fetch_array($rs);
				$peso = $row['peso'];
				$igi = $row['percentfactorigi'];
				$porcigi = $igi/100;
				
				$sql = "SELECT
				SUM(purchorderdetails.qtyinvoiced) AS totqtyinvoiced
				FROM purchorderdetails JOIN grns ON grns.podetailitem = purchorderdetails.podetailitem
				LEFT JOIN shipmentcharges ON purchorderdetails.shiptref = shipmentcharges.shiptref
				AND purchorderdetails.itemcode = shipmentcharges.stockid
				WHERE purchorderdetails.shiptref = '$shiptref' AND grns.qtyrecd <> 0
				AND purchorderdetails.itemcode = '{$myrow['itemcode']}'";
				$itemTotalInvoiced = DB_query($sql, $db);
				$itemTotalInvoiced = DB_fetch_row($itemTotalInvoiced);
				$itemTotalInvoiced = $itemTotalInvoiced[0];
				
				$sql = "SELECT SUM(shipmentcharges.value) AS invoicedcharges
                             FROM shipmentcharges
                             WHERE shipmentcharges.stockid ='" . $myrow['itemcode'] . "'
                             AND shipmentcharges.shiptref=" . $shiptref;
				$ItemChargesResult = DB_query($sql,$db);
				$ItemChargesRow = DB_fetch_row($ItemChargesResult);
				$ItemCharges = $ItemChargesRow[0] / $itemTotalInvoiced * $myrow['totqtyinvoiced'];
				$PortionOfCharges=0;
				
				 
				$txtprrorateo="";
				
				if ($TotalInvoiceValueOfShipment>0){
					$PortionOfCharges = $TotalCostsToApportion *($ItemCharges/$TotalInvoiceValueOfShipment);
					$arrCostDetail['Costeo Directo'][] = array('mov'=>$myrow['transno'],
																'prod'=>$myrow['itemcode'],
																'desc'=>$myrow['itemdescription'],
																'costo'=>$ItemCharges,
																'porc'=>1,
																'tot'=>$ItemCharges
															  );
					
				} else {
					$PortionOfCharges = 0;
				}
				$txtprrorateo="Costo";
				
				if ($peso > 0){
					if ($TotalCostsWeight>0){
						$PortionOfCharges+= $TotalWeightInvoiced *($peso/$TotalCostsWeight);
						$arrCostDetail['Pro-rateo por Peso'][] = array('mov'=>$myrow['transno'],
																	'prod'=>$myrow['itemcode'],
																	'desc'=>$myrow['itemdescription'],
																	'costo'=>$ItemCharges,
																	'porc'=>($peso/$TotalCostsWeight),
																	'tot'=>$TotalWeightInvoiced *($peso/$TotalCostsWeight)
																);
					} else {
						$PortionOfCharges+= 0;
					}
					$txtprrorateo.=" Peso";
				}
				
				if ($igi > 0){
					if ($TotalIGI>0){
						$PortionOfCharges+= $TotalIGIInvoiced*($igi/$TotalIGI);
						$arrCostDetail['Pro-rateo por Advalorem'][] = array('mov'=>$myrow['transno'],
																		'prod'=>$myrow['itemcode'],
																		'desc'=>$myrow['itemdescription'],
																		'costo'=>$ItemCharges,
																		'porc'=>($igi/$TotalIGI),
																		'tot'=>$TotalIGIInvoiced*($igi/$TotalIGI)
																);
					} else {
						$PortionOfCharges+= 0;
					}
					$txtprrorateo.=" IGI";
				}
				
				$sql = 'SELECT /*grns.stdcostunit*grns.qtyrecd) AS costrecd*/
						SUM(grns.stdcostunit*grns.qtyrecd/rategr/(1+stockcategory.margenautcost/100)) AS costrecd
		               FROM grns INNER JOIN purchorderdetails
		               ON grns.podetailitem=purchorderdetails.podetailitem
						INNER JOIN stockmaster ON purchorderdetails.itemcode=stockmaster.stockid
						JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
                 		WHERE purchorderdetails.shiptref=' . $shiptref . "
                 		AND purchorderdetails.itemcode = '" . $myrow['itemcode'] . "'";
				
				$StdCostResult = DB_query($sql,$db);
				$StdCostRow = DB_fetch_row($StdCostResult);
				$CostRecd = $StdCostRow[0];
				$StdCostUnit = $StdCostRow[0]/$myrow['totqtyrecd'];
				$StdCostUnit = $ItemCharges / $myrow['totqtyrecd'];
				
				if ($myrow['totqtyinvoiced']>0){
					$ItemShipmentCost = ($ItemCharges+$PortionOfCharges)/$myrow['totqtyrecd'];
				} else {
					$ItemShipmentCost =0;
				}
				
				//escribir los datos de la tabla
				$this->SetFont('helvetica','',7);
				//$this->cell(7,4,'',0,0,'C');
				$this->cell(12,4,$myrow['transno'],0,0,'C');
				$this->cell(25,4,$myrow['itemcode'],0,0,'L');
				$this->cell(60,4,$myrow['itemdescription'],0,0,'L');
				$this->cell(7,4,$myrow['units'],0,0,'C');
				$this->cell(10,4,$myrow['totqtyrecd'],0,0,'R');
				$this->cell(15,4,number_format($StdCostUnit,2),0,0,'R');
				$this->cell(15,4,'',0,0,'R');
				$this->cell(15,4,number_format($ItemCharges,2),0,0,'R');
				$this->cell(15,4,number_format($PortionOfCharges,2),0,0,'R');
				$this->cell(20,4,number_format($ItemShipmentCost,2),0,1,'R');

				$TItemCharges += $ItemCharges;
				$TPortionOfCharges += $PortionOfCharges;
				$TItemShipmentCost += $ItemShipmentCost;			
			}
			
			$this->SetFont('helvetica','B',7);
			$this->cell(129,4,'Total',0,0,'L');
			$this->cell(15,4,'','T',0,'R');
			$this->cell(15,4,number_format($TItemCharges,2),'T',0,'R');
			$this->cell(15,4,number_format($TPortionOfCharges,2),'T',0,'R');
			$this->cell(20,4,number_format($TItemShipmentCost,2),'T',1,'R');

			$this->Ln(10);
			
			
			
			//detalle de costeo
			foreach($arrCostDetail as $titulo => $arrdata){
				$this->SetFont('helvetica','B',8);
				$this->cell('',4,$titulo,0,1,'L');
				
				$this->SetFont('helvetica','',7);
				$this->cell(12,4,'Factura','B',0,'C');
				$this->cell(25,4,'Prod','B',0,'C');
				$this->cell(60,4,'Descripcion','B',0,'C');
				$this->cell(15,4,'Costo','B',0,'C');
				$this->cell(15,4,'% Part','B',0,'C');
				$this->cell(20,4,'Total','B',1,'C');
				
				$tot = 0;
				foreach ($arrdata as $items){
					$this->cell(12,4,$items['mov'],0,0,'C');
					$this->cell(25,4,$items['prod'],0,0,'L');
					$this->cell(60,4,$items['desc'],0,0,'L');
					$this->cell(15,4,number_format($items['costo'],2),0,0,'R');
					$this->cell(15,4,number_format($items['porc']*100,2),0,0,'R');
					$this->cell(20,4,number_format($items['tot'],2),0,1,'R');
					$tot+=$items['tot'];
				}
				$this->SetFont('helvetica','B',7);
				$this->cell(127,4,'Total',0,0,'L');
				$this->cell(20,4,number_format($tot,2),'T',1,'R');
				$this->Ln();
				
			}
			
			
			$this->OutPut('','I');
			
		}
		/*
		function Footer(){			
			$this->SetFont('helvetica','B',8);
			$this->SetY(-10);
			$this->cell('60',5,utf8_decode('Fecha de impresión: ').date("d/m/Y H:i"),0,0,'C');
			$this->cell('',5,utf8_decode('Página ').$this->PageNo().'/{nb}',0,0,'R');
		}
		*/
	}



?>