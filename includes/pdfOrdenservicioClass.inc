<?php
	define('FPDF_FONTPATH','./fonts/');
	include ('fpdf.php');

	class pdfOrdenServicio extends FPDF {
	
		function printPDF(){
			global $db,$myrow;

			
			$SQL="select t.tagname,
					  concat(t.address1,' ',t.address2) as calletag,
					  t.address3 as coloniatag,
					  t.phone as telefonotag,
					  s.orddate,
					  date_format(s.deliverydate,'%d/%m/%Y') as fechaentrega,
					  date_format(s.deliverydate,'%H:%i') as horaentrega,
					  d.name as cliente,
					  c.taxid as rfc,
					  c.braddress1 as calle,
					  c.braddress2 as colonia,
					  c.braddress5 as cp,
					  c.braddress4 as estado, 
					  c.braddress6 as ciudad,
					  c.phoneno as telefono,
					  c.email,
					  V.*,
					  s.taxtotal,
					  s.totaltaxret,
					  case when sd.showdescrip=0 then sd.narrative else sm.description end as descripcion,
					  sd.unitprice as preciou,
					  sd.quantity,
					  legalbusinessunit.logo
				  from salesorders s 
					  left join salesorderdetails sd
					  ON s.orderno=sd.orderno
					  
					  left join stockmaster sm
					  ON sm.stockid = sd.stkcode
					  
					  left join debtorsmaster d
					  ON s.debtorno = d.debtorno
				  
					  left join tags t
					  ON s.tagref = t.tagref
				  
					  left join custbranch c
					  ON d.debtorno = c.debtorno
					  and s.branchcode = c.branchcode
					
					  left join legalbusinessunit ON t.legalid = legalbusinessunit.legalid
				  
					  left join (Select v.vehicleno,v.plate,v.serie,v.yearvehicle as tipo,mark,model,lastmilage ,cl.color, v.numeco
							  FROM vehiclesbycostumer v 
							  left join vehiclemodels vm on v.idmodel=vm.idmodel
							  left join vehiclemarks vk on v.idmark = vk.idmark
							  left join vehiclecolors cl on v.color = cl.numbercolor
						  ) V
					  ON s.vehicleno = V.vehicleno
					 
					WHERE s.orderno='" . $_GET['orderno']."'" ;

			//echo "<pre>".$SQL; exit;
			$res= DB_query($SQL,$db);
			if (DB_num_rows($res)>0) {
				$myrow = DB_fetch_array($res);

				$this->fpdf('P','mm','Letter');			
				$this->SetAutoPageBreak(true,10);
				$this->AddPage();				

				//valores del detalle

				//encabezado de secciones
				$this->SetFont('helvetica','B',8);
				$this->SetFillColor(255,200,0);
				$this->SetX($this->GetX()+30);
				$this->Cell(40,4,'INSPECCION VEHICULAR',0,0,'C',1);			
				$this->SetX($this->GetX()+30);
				$posXrightside = $this->GetX();
				$this->Cell(40,4,strtoupper('Servicios a Realizar'),0,1,'C',1);
				$this->SetFillColor(255,255,255);
				$this->SetFont('helvetica','',7);
				$posY = $this->GetY();

				//inspeccion vehicular
				$qry = "select * 
						FROM PDFTemplates
						WHERE tipodocto=3
						/*and visible=1*/
						and Ubicacion='detalle'
						Order by Orden ";

				$rsinsp = DB_query($qry,$db);
				while ($myregs = DB_fetch_array($rsinsp)){
					$imgfile = $myregs['imagename'];
					$sizeX = $myregs['xsize'];
					$sizeY = $myregs['ysize'];
					$lines = $myregs['linesunder'];
					$this->Image('./images/'.$imgfile,$this->GetX(),$this->GetY(),$sizeX,$sizeY);
					
					
					
					$this->Ln($sizeY*1);
					for($l=1;$l <= $lines;$l++){
						$this->Cell(80,4,'',1,1,'L');
					}
					
					$this->Ln(2);		
				}
				//texto inferior izquierdo
				$qry = "select * 
						FROM PDFTemplates
						WHERE tipodocto=3
						/*and visible=1*/
						and Ubicacion='detalle final izq'
						";
				$rstxt = DB_query($qry,$db);
				
				if ($myregs = DB_fetch_array($rstxt)){
					$this->SetFont('helvetica','',5);
					$this->MultiCell(80,2,$myregs['Texto'],0,'L');
					
				}
				//$this->Ln(2);
				//$this->SetFont('helvetica','B',7);
				//$this->Cell('',3,'ESTE NO ES UN COMPROBANTE FISCAL',0,0,'L');
			
				//cotizacion
				$res = DB_query($SQL,$db);
				$totitems = DB_num_rows($res);
				$itemsCount = 0;
				$newPage=false;
				$this->SetFont('helvetica','',6);
				$posXrightside-=10;
				$this->SetXY($posXrightside,$posY);
				$this->Cell(10,3,'CANTIDAD',0,0,'C');
				$this->Cell(75,3,'DESCRIPCION',0,0,'C');
				$this->Cell(15,3,'IMPORTE',0,1,'C');
				$filas=0;
				$totdesc=18;
				$subt=0;
				while ($myrow2=DB_fetch_array($res)){					
					$preciou = $myrow2['preciou'];
					$cant = $myrow2['quantity'];
					$desc = $myrow2['descripcion'];	
						
					$this->SetX($posXrightside);
					$this->Cell(10,3,$cant,1,0,'C');
					
					$actX = $this->GetX();
					$actY = $this->GetY();
					$this->MultiCell(75,3,$desc,1,'L');
					$posYMultiCell = $this->GetY();
					if ($actY < $posYMultiCell){
						$y = $actY+3;	
						while ($y < $posYMultiCell){
							$this->SetY($y);
							$this->cell('75',3,'','LR',1,'L');
							$y+=3;
							$filas++;
						}
					}
					$this->SetXY($actX+75,$actY);// para regresar a la posicion sigte
					
					//$this->Cell(75,3,left($desc,60),1,0,'L');
					$this->Cell(15,3,number_format($preciou*$cant,2),1,1,'R');
					
					$impnum = str_replace(",","",number_format($preciou*$cant,2));
					
					$subt+=$impnum;
					
					$filas++;	
				}
				for($i=$filas;$i<$totdesc;$i++){
					$this->SetX($posXrightside);
					$this->Cell(10,3,'',1,0,'C');
					$this->Cell(75,3,'',1,0,'L');
					$this->Cell(15,3,'',1,1,'R');
					
				}
				$totservicios = 10;				
				if ($filas > $totdesc)
					$totservicios = 28 - $filas;
				
				if ($totservicios > 0){
					/*$this->SetFillColor(255,200,0);
					$this->SetX($posXrightside);
					$this->SetFont('helvetica','B',6);
					$this->Cell(30,3,'Servicios a Realizar',0,1,'C',1);
					$this->SetFillColor(255,255,255);
					*/
					$this->SetFont('helvetica','',6);
					for($i=1;$i<=$totservicios;$i++){
						$this->SetX($posXrightside);
						$this->Cell(10,3,'',1,0,'C');
						$this->Cell(75,3,'',1,0,'L');
						$this->Cell(15,3,'',1,1,'R');
					}
				}
					
				//subtotal iva y total
				$iva = $myrow['taxtotal'];
				$ivanum = str_replace(",","",number_format($iva,2));
				$total = $subt + $ivanum;
				
				$this->Ln();
				$posX = $posXrightside + 75;
				$this->SetX($posX);
				$this->Cell(10,3,'SUBTOTAL',0,0,'R');
				$this->Cell(15,3,number_format($subt,2),1,1,'R');
				$this->SetX($posX);
				$this->Cell(10,3,'IVA',0,0,'R');
				$this->Cell(15,3,number_format($iva,2),1,1,'R');
				$this->SetX($posX);
				$this->Cell(10,3,'TOTAL',0,0,'R');
				$this->Cell(15,3,number_format($total,2),1,1,'R');

				$this->Ln();
				//texto medio derecho
				$qry = "select * 
						FROM PDFTemplates
						WHERE tipodocto=3
						/*and visible=1*/
						and Ubicacion='detalle medio der'
						";
				$rstxt = DB_query($qry,$db);
				
				if ($myregs = DB_fetch_array($rstxt)){
					$this->SetX($posXrightside);
					$this->SetFont('helvetica','',5);
					$this->MultiCell(100,2,$myregs['Texto'],0,'L');

					$this->Ln();
					$this->SetX($posX);
					$this->Cell(25,3,'Firma del cliente','T',1,'C');
					
				}

				$this->SetFont('helvetica','',7);				
				$this->SetX($posXrightside);
				//recepcion del vehiculo
				$this->Image('./images/recepcionvehiculo.jpg',$this->GetX(),$this->GetY(),90,45);					
				$this->Ln(45);
				$this->Ln();
				//talon de entrega
				$this->SetX($posXrightside);
				$this->Cell(90,4,'TALON DE ENTREGA','LTR',1,'C');
				$this->SetX($posXrightside);
				$this->Cell(70,4,'','L',0,'L');
				$this->Cell(20,4,'FOLIO '.$_GET['orderno'],'R',1,'L');
				$this->SetX($posXrightside);
				$this->Cell(50,4,'VEHICULO','L',0,'L');
				$this->Cell(40,4,'PLACAS','R',1,'L');
				$this->SetX($posXrightside);
				$this->Cell(20,4,$myrow['mark'],'LB',0,'C');
				$this->Cell(20,4,'',0,0,'L');
				$this->Cell(20,4,$myrow['plate'],'B',0,'C');
				$this->Cell(30,4,'','R',1,'L');
				$this->SetX($posXrightside);
				$this->Cell(90,2,'','LR',1,'L');
				$this->SetX($posXrightside);
				$this->Cell(50,4,'HORA DE ENTREGA','L',0,'L');
				$this->Cell(40,4,'TOTAL','R',1,'L');
				$this->SetX($posXrightside);
				$this->Cell(20,4,'','LB',0,'L');
				$this->Cell(20,4,'',0,0,'L');
				$this->Cell(20,4,'$','B',0,'L');
				$this->Cell(30,4,'','R',1,'L');
				$this->SetX($posXrightside);
				$this->Cell(90,2,'','LBR',1,'L');
				$this->SetX($posXrightside);
				$this->SetFont('helvetica','',6);				
				$this->Cell(90,3,'ENCUESTA: COMO SE ENTERO DE NOSOTROS','LTR',1,'C');
				$this->SetX($posXrightside);
				$this->Cell(90,3,'1-PRENSA  2-RADIO  3-AL PASAR  4-VOLANTE  5-YA ES CLIENTE   6-OTROS','LBR',1,'C');
				$this->Ln(8);
				$this->SetFont('helvetica','B',7);
				$this->Cell(200,4,'ESTE NO ES UN COMPROBANTE FISCAL',0,0,'L');
			}//if si hay registros

			$this->OutPut('','I');		
			die();

		}
		

		function Header(){
			global $db,$myrow;
			  
			$tagname = $myrow['tagname'];
			  
		    $posY = $this->GetY()-3;
		    $this->Image($myrow['logo'],5,$posY,55,15);
		    
		    	 //$this->Image( './images/logomultirefax.jpg',5,$posY,55,15);	  
		   

			//horario
			$this->Ln(2);
			$this->SetX(80);
			$this->SetFont('helvetica','B',8);
			$this->Cell(60,4,'LUNES A VIERNES DE 8:30 A.M. A 19:00 HRS.',0,1,'C');  	
			$this->SetX(80);
			$this->Cell(60,4,'SABADOS DE 9:00 A.M. A 17:00 HRS.',0,1,'C');  	
			
			//orden
			$this->SetXY(160,$posY);
			$this->Cell(40,4,'ORDEN',1,1,'C');
			$this->SetX(160);
			$this->MultiCell(40,8,$_GET['orderno'],1,'C');
			$this->SetX(160);
			$this->Cell(40,4,'FECHA',1,1,'C');
			$this->SetX(160);
			$this->SetFont('helvetica','',7);
			$this->Cell(12,4,substr($myrow['orddate'],8,2),'L',0,'C');
			$this->Cell(12,4,substr($myrow['orddate'],5,2),'',0,'C');
			$this->Cell(16,4,substr($myrow['orddate'],0,4),'R',1,'C');
			$this->SetX(160);
			$this->SetFont('helvetica','',6);
			$this->Cell(12,3,'DIA','LB',0,'C');
			$this->Cell(12,3,'MES','B',0,'C');
			$this->Cell(16,3,utf8_decode('A�O'),'BR',1,'C');

			//datos del tag
			$this->SetFont('helvetica','',8);
			$this->Cell(30,4,$myrow['tagname'],0,0,'L');
			$this->Cell(60,4,$myrow['calletag'],0,0,'L');
			$this->Cell(40,4,'Col. '.$myrow['coloniatag'],0,0,'L');
			$this->Cell(60,4,$myrow['telefonotag'],0,1,'L');
			
			//datos cliente
			$orddate = substr($myrow['orddate'],8,2)."/".substr($myrow['orddate'],5,2)."/".substr($myrow['orddate'],0,4);
			$this->Cell(190,4,'NOMBRE: '.$myrow['cliente'],'1',1,'L');
			$this->Cell(130,4,'DIRECCION: '.$myrow['calle'].'   COL. '.strtoupper($myrow['colonia']),'LT',0,'L');
			$this->Cell(60,4,'ESTADO: '.$myrow['estado'],'TR',1,'L');
			$this->Cell(20,4,'CP: '.$myrow['cp'],'LT',0,'L');
			$this->Cell(60,4,'TELEFONO: '.$myrow['telefono'],'T',0,'L');
			$this->Cell(50,4,'No ECO: '.$myrow['numeco'],'T',0,'L');			
			$this->Cell(60,4,'RFC: '.$myrow['rfc'],'TR',1,'L');
			$this->Cell(190,4,'E-MAIL: '.$myrow['email'],1,1,'L');
			$this->Cell(40,4,'MODELO: '.$myrow['model'],'LT',0,'L');
			$this->Cell(30,4,'ANO: '.$myrow['tipo'],'T',0,'L');
			$this->Cell(60,4,'MARCA: '.$myrow['mark'],'T',0,'L');
			$this->Cell(30,4,'COLOR: '.$myrow['color'],'T',0,'L');
			$this->Cell(30,4,'PLACAS: '.$myrow['plate'],'TR','1','L');
			$this->Cell(60,4,'FECHA DE INGRESO: '.$orddate,'LT',0,'L');
			$this->Cell(35,4,'HORA: ','T',0,'L');
			$this->Cell(60,4,'FECHA DE ENTREGA: '.$myrow['fechaentrega'],'T',0,'L');
			$this->Cell(35,4,'HORA: '.$myrow['horaentrega'],'TR',1,'L');
			$this->Cell(60,4,'No DE SERIE: '.$myrow['serie'],'LBT',0,'L');
			$this->Cell(70,4,'No DE MOTOR: ','TB',0,'L');
			$this->Cell(60,4,'KMS RECORRIDOS: '.$myrow['lastmilage'],'TBR',1,'L');

			$this->Ln();			
						
		}
		
	}
?>