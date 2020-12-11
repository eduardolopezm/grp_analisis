<?php
	define('FPDF_FONTPATH','./fonts/');
	include ('includes/fpdf.php');
	 
	class ncrPDF extends FPDF {		
		
		function exportPDF($save=0){
			global $db;
			
			
			$ivaret=0;
			$SQL=" SELECT count(*) as iva
				FROM  stockmoves inner join stockmovestaxes on stockmoves.stkmoveno=stockmovestaxes.stkmoveno ,taxauthorities
				WHERE taxauthorities.taxid=stockmovestaxes.taxauthid and taxauthid=2
				and stockmoves.type='" . $_GET['Type']."' and stockmoves.transno=".$_GET['TransNo'] ;
			$Resultd= DB_query($SQL,$db);
			if (DB_num_rows($Resultd)>0) {
				$myrowpag = DB_fetch_array($Resultd);
				$ivaret=$myrowpag['iva'];
			}

						
			$this->fpdf('P','mm','Letter');
			
			$this->AliasNbPages();
			$this->SetAutoPageBreak(true,10);
			$this->AddPage();
						
			$tabla = "salesorders";
			if ($_GET['Type']==12 || $_GET['Type']==21)
				$tabla = "Documentsorders";
				
			if ($_GET['Type']==11 || $_GET['Type']==13)
				$tabla = "notesorders";			  

			
			$sql=" SELECT 
			 		debtortrans.type,
					stockmoves.price as precio,	
				 (stockmoves.qty*stockmoves.price)*-1 as importe2,
				 (((stockmoves.qty*-1)*stockmoves.price)+(((stockmoves.qty*-1)*stockmoves.price)*taxrate)) as importe,
				 stockmoves.stockid as codigo,
				 stockmoves.qty*-1 as cantidad,
				 stockmoves.discountpercent as descu,
				 stockmoves.discountpercent1 as desc1,
				 stockmoves.discountpercent2 as desc2,
				 stockmaster.description as descripcion,
				 stockmaster.units,
				 stockmoves.narrative as comentarios,
				 debtortrans.folio as folio,
				 debtortrans.noagente,
				 debtortrans.showvehicle,
				 debtortrans.showcomments,
				 debtortrans.paymentname,
				 debtortrans.nocuenta,
				 Tabla.customerref,
				 Tabla.confirmeddate,
				 Tabla.deliverydate,
				 debtortrans.trandate as trandatefact,
				 trim(substring(Tabla.comments,1,LOCATE('Inv', Tabla.comments)-1))  as comenrenglon,
				 Tabla.quotation as title,
				 Tabla.deliverto as nombrefrom,
				 custbranch.braddress1 as dirfrom1,
				 custbranch.braddress2 as dirfrom2,
				 custbranch.braddress3 as dirfrom3,
				 custbranch.braddress4 as dirfrom4,
				 custbranch.braddress5 as dirfrom5,
				 custbranch.braddress6 as dirfrom6,
				 custbranch.phoneno as tel,
				 Tabla.confirmeddate as fecha,
				 debtortrans.ovgst as iva,
				 Tabla.UserRegister as userr,
				 Tabla.paytermsindicator as termino,
				 salesman.salesmanname as vendedor,
				 debtorsmaster.debtorno as cliente,
				 debtorsmaster.name as nombre,
				 debtorsmaster.address1 as dir1,
				 debtorsmaster.address2 as dir2,
				 debtorsmaster.address3 as dir3,
				 debtorsmaster.address4 as dir4,
				 debtorsmaster.address5 as dir5,
				 '' as canletra,
				 debtortrans.id as iddocto,
				 debtortrans.currcode as moneda,
				 debtortrans.ovamount as montofactura,
				 debtortrans.ovgst as montoivafact,
				 debtortrans.rate as tipocambio,
				 legalbusinessunit.comments as bancos,
				 custbranch.brname,
				 custbranch.taxid as RFC,debtortrans.ref1 as ref1,
				 stockmoves.narrative as infext,
				 concat(stockmaster.longdescription,'|', debtortrans.transno ,'|', debtortrans.type) as descripcionlarga,
				 Tabla.comments as comentariosrenglon,
				 Tabla.orderno as orderno  ,
				(taxrate*100) as poriva,debtortrans.sello as sello,debtortrans.cadena as cadena ,
				 debtortrans.origtrandate as fechafact,
				  debtortrans.trandate as fechavencimiento,
				 legalbusinessunit.legalname,
				 legalbusinessunit.legalID AS empresaid,
				 legalbusinessunit.address1 AS calleempre,
				 legalbusinessunit.address2 as coloniaempre,
				 legalbusinessunit.address3 as Noextempre,
				 legalbusinessunit.address4 as cdempre,
				 legalbusinessunit.address5 as edoempre,
				 legalbusinessunit.telephone as phoneempre,
				 legalbusinessunit.fax as faxempre,
				 legalbusinessunit.email as emailempre,
				 legalbusinessunit.taxid as rfcempre,
				  tags.logotag as logo,
				  tags.tagref as tagbank,
				  legalbusinessunit.cedula,
				 legalbusinessunit.comments as referenciaempre,
				 tags.address1 AS calleexpe,
				 tags.address2 as Noextexpe,
				 tags.address3 as coloniaexpe,
				 tags.address4 as cdexpe,
				 tags.address5 as edoexpe,
				 tags.email as tagemail,
				tags.address6 as edo,
				tags.phone as telexpe,
				tags.logotag,
				 Tabla.ordertype,
				 stockmaster.decimalplaces,
				 stockmoves.stkmoveno as orderlineno,
				 locations.locationname,
				 Tabla.printedpackingslip,
						 Tabla.currcode,
				 (((stockmoves.qty*stockmoves.price))-((stockmoves.qty*stockmoves.price)-(((stockmoves.qty*stockmoves.price)*(1-stockmoves.discountpercent))*(1-stockmoves.discountpercent1))*(1-stockmoves.discountpercent2)))*-1 as totaldesc,
				 (stockmoves.price - ((stockmoves.price)-(((stockmoves.price)*(1-stockmoves.discountpercent))*(1-stockmoves.discountpercent1))*(1-stockmoves.discountpercent2))) as priceunit,
				taxauthrates.taxrate*100 as percentiva,
				(debtortrans.ovamount+(debtortrans.ovgst+debtortrans.taxret)) as montofact,
						 abs(debtortrans.taxret) as ivaret,
						 debtorsmaster.currcode as monedacliente,
						 tags.tagname as unidadnegocio,
				custbranch.phoneno as telcliente	,
				(taxrate) as poriva,
				 totaldescuento as descuento,
				 ((stockmoves.qty  *-1)*(stockmoves.price))-totaldescuento as subtotal,
				 salestypes.sales_type,
				  stockmoves.showdescription,custbranch.brnumext, custbranch.brnumint,
				  debtortrans.idorigen,
				  legalbusinessunit.regimenfiscal,
				  stockmoves.stockclie,
				  '' as referencia,
				  '' as add_leyendaParcialidad,
				  '' as add_TAXTaxPercentage,
				  '' as com_MetodoPago
			  FROM stockmoves INNER JOIN stockmaster ON stockmoves.stockid = stockmaster.stockid
				INNER JOIN debtortrans ON stockmoves.type=debtortrans.type and stockmoves.transno=debtortrans.transno
				INNER JOIN $tabla as Tabla ON Tabla.orderno = debtortrans.order_
				LEFT JOIN salesman ON salesman.salesmancode = Tabla.salesman
				INNER JOIN locations ON locations.loccode = stockmoves.loccode
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON custbranch.branchcode = debtortrans.branchcode
				INNER JOIN salestypes ON salestypes.typeabbrev = Tabla.ordertype
				
				INNER JOIN tags ON debtortrans.tagref=tags.tagref
				INNER JOIN areas a ON tags.areacode=a.areacode
				INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
				INNER JOIN taxauthrates ON stockmaster.taxcatid=taxauthrates.taxcatid
			WHERE  Tabla.orderno =".$_GET['OrderNo']."
				AND debtortrans.type =".$_GET['Type']."
				AND debtortrans.transno =".$_GET['TransNo']."
			ORDER BY stockmoves.stkmoveno ";
			//echo "<pre>".$sql; exit;
			
			$result=DB_query($sql,$db);
			if (DB_num_rows($result)>0){
				$myrow=DB_fetch_array($result);
				$original = "ORIGINAL";
				$legalname = $myrow['legalname'];
				$type = $myrow['type'];
				for($i=1;$i<=2;$i++){
					//logo
					
					$posY = $this->GetY()-5;
					if ($i==2){
						if ($itemsCount > 3){
							$this->AddPage();
							$posY = $this->GetY()-5;
						}
						else	
							$posY = 148; //mitad de la pagina //$this->GetY()+7;
					}
					$this->Image( './'.$myrow['logo'],5,$posY,45,18);
	
					//emisor				
					$this->SetFont('helvetica','B',8);				
					$this->SetXY(50,$posY);
					$this->cell(50,3,$myrow['legalname'],0,1,'L');
					$this->SetFont('helvetica','',7);				
					$this->SetX(50);
					$this->cell(15,3,'RFC: '.$myrow['rfcempre'],0,1,'L');
					$this->SetX(50);
					$this->cell(50,3,$myrow['calleempre'],0,1,'L');
					$this->SetX(50);
					$this->cell(25,3, $myrow['coloniaempre'],0,1,'L');
					$this->SetX(50);
					$this->cell(25,3, $myrow['Noextempre']. ' ' . $myrow['cdempre'],0,1,'L');
					$this->SetX(50);
					$this->cell(25,3,'Tel: ' . $myrow['phoneempre'] . ' ' . 'Fax: ' . $myrow['faxempre'],0,1,'L');
					$this->SetX(50);				
					$this->cell(25,3, 'Email: ' . $myrow['emailempre'],0,1,'L');
					$this->SetX(50);				
					$this->cell(25,3, utf8_decode('Régimen: ' . utf8_encode($myrow['regimenfiscal'])),0,1,'L');
	
					//expedido en
					$this->SetFont('helvetica','B',8);				
					$this->SetXY(100,$posY);
					$this->cell(50,3,'Expedido en:',0,1,'L');
					$this->SetFont('helvetica','',7);				
					$this->SetX(100);
					$this->cell(50,3,$myrow['cdexpe'] . ',' . $myrow['edoexpe'],0,1,'L');
					$this->SetX(100);
					$this->cell(50,3,$myrow['calleexpe'].' '.$myrow['Noextexpe'],0,1,'L');
					$this->SetX(100);
					$this->cell(25,3, $myrow['coloniaexpe'],0,1,'L');
					$this->SetX(100);
					$this->cell(25,3, $myrow['cdexpe'] . ' ' . $myrow['edoexpe'],0,1,'L');
					$this->SetX(100);
					$this->cell(25,3,'Tel.'.$myrow['telexpe'],0,1,'L');
					$this->SetX(100);				
					$this->cell(25,3, 'Email: ' . $myrow['tagemail'],0,1,'L');
									
					//datos documento
					$foliox=explode('|',$myrow['folio']);
					$serie=$foliox[0];
					$folio=$foliox[1];
					$folioX=$foliox[1];
					$tipodoc = "NOTA";
					$tipodoc1 = "CREDITO";
					$leyenda = "PAGO EN UNA SOLA EXHIBICION";
					if ($myrow['type']==12){
						$leyenda = $myrow['add_leyendaParcialidad']."/".$myrow['com_MetodoPago'];
						$tipodoc = "RECIBO";
						$tipodoc1 = "PAGO";
						$serie=$foliox[1];
						$folio=$foliox[0];
						$folioX=$foliox[0];
					}
					else
						if ($myrow['type']==21)
							$tipodoc1 = "CARGO";
					
					
					$aprobaxfolio=TraeAprobacionxFolio($myrow['rfcempre'],$serie,$folio,$db,$myrow['fechafact']);
					$aprobacionfolios=explode('|',$aprobaxfolio);
					$Certificado=$aprobacionfolios[0];
					$Noaprobacion=$aprobacionfolios[1];
					$anioAprobacion=$aprobacionfolios[2];
					
					$this->SetXY(155,$posY);
					$this->SetFont('helvetica','B',8);
					$this->cell(20,4,$tipodoc,'LTR',0,'C');
					$this->cell(35,4,'SERIE - FOLIO','TR',1,'C');
					$this->SetX(155);
					$this->SetFont('helvetica','',8);
					$this->cell(20,4,$tipodoc1,'LBR',0,'C');
					$this->cell(35,4,$serie.' - '.$folio,'BR',1,'C');
					
					$this->SetX(155);
					$this->cell(55,4,utf8_decode('Fecha Emisión: ').str_replace(' ','T',$myrow['fechafact']),1,1,'L');
					$this->SetX(155);
					$this->cell(55,4,utf8_decode('Año aprobación: '.$anioAprobacion),1,1,'L');
					$this->SetX(155);
					$this->cell(55,4,utf8_decode('Num. aprobación: '.$Noaprobacion),1,1,'L');
					$this->SetX(155);
					$this->cell(55,4,'Num. certificado:'.$Certificado,1,1,'L');
					$this->SetX(155);
					$this->cell(55,4,'Referencia: '.$myrow['referencia'],1,1,'L');
					$this->SetX(155);
					$this->cell(55,4,'Agente: '.$myrow['noagente'],1,1,'L');
					
					
					//datos cliente
					$carpeta="NCredito";
					
					if($_GET['Type']=='21'){
						$carpeta="NCargo";
					}
					
					if($_GET['Type']=='12'){
						$carpeta="Recibo";
					}

					$dir="/var/www/html/".dirname($_SERVER['PHP_SELF'])."/companies/".$_SESSION['DatabaseName']."/SAT/".str_replace(',','',str_replace('.','',str_replace(' ','',$legalname)))."/XML/".$carpeta."/";
					$nufa = $serie.$folioX;
					$mitxt=$dir.$nufa.".xml";
					
					$cfd=file_get_contents($mitxt); 
					$DatosCFD = TraeDatosCFD($cfd,'Receptor');
					
					$this->SetFont('helvetica','B',8);
					$this->cell(20,4,'Datos del Cliente',0,1,'L');
					$this->SetFont('helvetica','',8);
					$this->cell('',4,utf8_decode('Nombre, razón o denominación social: '.$myrow['cliente'].' - '.$DatosCFD['nombre']),'LTR',1,'L');
					$this->cell('',4,'RFC: '.$DatosCFD['rfc'],'LR',1,'L');
					$DomicilioCFD=TraeDatosCFD($cfd,'Domicilio');
					$this->cell('',4,utf8_decode('Domicilio: '.$DomicilioCFD['calle'].' '.$DomicilioCFD['noExterior'].' '.$DomicilioCFD['noInterior'].', '.$DomicilioCFD['colonia'].'   CP: '.$DomicilioCFD['codigoPostal']),'LR',1,'L');
					$this->cell('',4,utf8_decode('Población: '.$DomicilioCFD['estado']),'LBR',1,'L');
					
					//titulos del detalle
					$this->SetFont('helvetica','B',8);
					$this->cell(10,4,'CANT',1,0,'C');
					$this->cell(30,4,'CODIGO',1,0,'C');
					$this->cell(108,4,'DESCRIPCION',1,0,'C');
					$this->cell(10,4,'UM',1,0,'C');
					$this->cell(20,4,'PRECIO UNIT',1,0,'C');
					$this->cell(18,4,'TOTAL',1,1,'C');
	
					//valores del detalle
					$res = DB_query($sql,$db);
					$itemsCount = 0;
					$porciva2 = -1;
					while ($myrow2=DB_fetch_array($res)){					

						$qrypciva = "SELECT taxrate,taxauthorities.description
									 FROM  stockmovestaxes,taxauthorities
									 WHERE taxauthorities.taxid=stockmovestaxes.taxauthid and taxauthid=1
									   AND stkmoveno=".$myrow2['orderlineno'];
									   
						$rstax = DB_query($qrypciva,$db);
						$rowpcica = DB_fetch_array($rstax);
						$porciva2 = $rowpcica['taxrate'];			   

						$datos=trim($myrow2['comentarios']);
						
						$this->SetFont('helvetica','',7);
						$this->cell(10,4,abs($myrow2['cantidad']),0,0,'C');
						$this->cell(30,4,$myrow2['codigo'],0,0,'L');
						
						$actX = $this->GetX();
						$actY = $this->GetY();
						$this->MultiCell(108,4,$datos,0,'L');
						$posYMultiCell = $this->GetY();
						if ($actY < $posYMultiCell){
							$y = $actY+4;	
							while ($y < $posYMultiCell){
								$this->SetY($y);
								$this->cell('',4,'','0',1,'L');
								$y+=4;
							}
						}
						$this->SetXY($actX+108,$actY);// para regresar a la posicion sigte
						
						
						
						$this->cell(10,4,$myrow2['units'],0,0,'C');
						$this->cell(20,4,number_format(abs($myrow2['precio']),2),0,0,'R');
						$this->cell(18,4,number_format(abs($myrow2['cantidad']*$myrow2['precio']),2),0,1,'R');
						
						$itemsCount++;
					}
					$this->Ln();
					
					//pago en una sola exhib o leyenda d epago
					$this->SetFont('helvetica','',8);
					$this->cell('',4,$leyenda,0,1,'C');
					
					//cadena original
					$this->SetFont('helvetica','',4);
					$this->MultiCell('',3,'cadena Original: '.utf8_decode($myrow['cadena']),1,'L');
	
					//obtener total, iva y subtotal
					//$this->Ln();
					$this->Ln();
	
					$sql2="SELECT abs(ovamount) as subtotal,
								abs(ovamount)+abs(ovgst) as total,
								abs(ovgst) as iva,
								currcode 
							FROM debtortrans where type=".$_GET['Type']." and transno=" .$_GET['TransNo'];		
					$rs = DB_query($sql2,$db);
					$myrow2 = DB_fetch_array($rs);
					$subt = $myrow2['subtotal'];		
					$iva = $myrow2['iva'];
					$tot = $myrow2['total'];
					
					if ($ivaret > 0)
						$tot -= $iva;
					
					$totaletras = $tot;
					$separa=explode(".",$totaletras);
					$montoctvs2 = $separa[1];
					$montoctvs1 = $separa[0];
					//$montoctvs1x= $separa[1];
					if (left($montoctvs2,3)>=995){
							$montoctvs1=$montoctvs1+1;
							//$montoctvs1x='entra';
					}
					
					if ($myrow2['currcode']=='EUR'){
						$montoletra=Numbers_Words::toWords($montoctvs1,'en_US');
					}elseif($myrow2['currcode']=='USD'){
								$montoletra=Numbers_Words::toWords($montoctvs1,'en_US');
							}else{
								$montoletra=Numbers_Words::toWords($montoctvs1,'es');
							}
	
	
	
					$totaletras=number_format($totaletras,2);
					$separa=explode(".",$totaletras);
					$montoctvs2 = $separa[1];
					if (left($montoctvs2,3)>956){
							$montoctvs2=0;
					}
					$montocentavos=Numbers_Words::toWords($montoctvs2,'es');
					if ($myrow2['currcode']=='MXN'){
							$montoletra=ucwords($montoletra) . " Pesos ". $montoctvs2 ." /100 M.N.";
					}
					else
					{
							if($myrow2['currcode']=='EUR'){
								$montoletra=ucwords($montoletra) . " Euros ". $montoctvs2 ."/100 EUR";
							}else{
								
								$montoletra=ucwords($montoletra) . " Dollars ". $montoctvs2 ."/100 USD";
							}
					}
	
					//cantidad en letras
					$this->SetFont('helvetica','',8);
					$this->cell('',4,'('.$montoletra.')',0,1,'L');
					
					//sello digital
					$this->SetFont('helvetica','',7);
					$this->cell('20',4,'Sello Digital',0,1,'L');
					$this->MultiCell('',3,$myrow['sello'],0,'L');
					
					//poner metodo de pago y numero de cuenta. Moneda y TC
					$this->SetFont('helvetica','',8);
					$this->Ln();
					if ($this->GetY() > 260)
						$this->AddPage();
					
					$actY = $this->GetY();
					$this->cell(70,'4',utf8_decode('Método de Pago: '.$myrow['paymentname']),0,0,'L');
					$this->cell(20,'4','Moneda: '.$myrow['currcode'],0,1,'L');
					$this->cell(70,'4','Num. Cuenta Pago: '.$myrow['nocuenta'],0,0,'L');
					$this->cell(20,'4','TC: '.number_format((1/$myrow['tipocambio']),4),0,0,'L');
					
					//poner subtotal iva y total
					$this->SetXY(163,$actY);
					if ($porciva2 == -1){
						$porciva = $myrow['percentiva'];
						$porciva = round(($iva*100)/$subt);
					}
					else
						$porciva = ($porciva2*100);

					if($myrow['add_TAXTaxPercentage']>0){
						$porciva = $myrow['add_TAXTaxPercentage'];
					}	
					
					$this->cell(18,4,'SUBTOTAL',0,0,'L');
					$this->cell(25,4,number_format($subt,2),1,1,'R');
					$this->SetX(163);
					$this->cell(18,4,'IVA '.number_format($porciva,2).'%',0,0,'L');
					$this->cell(25,4,number_format($iva,2),1,1,'R');
					$this->SetX(163);
					if ($ivaret > 0){
						$this->cell(18,4,'RET IVA',0,0,'L');
						$this->cell(25,4,number_format($iva,2),1,1,'R');
						$this->SetX(163);
					}
					$this->cell(18,4,'TOTAL',0,0,'L');
					$this->cell(25,4,number_format($tot,2),1,1,'R');
					
					$this->SetFont('helvetica','B',8);
					//poner leyenda
					$this->SetX(15);
					$this->cell(110,4,'ESTE DOCUMENTO ES UNA REPRESENTACION IMPRESA DE UN CFD',0,0,'C');
					$this->cell(30,4,'AUTORIZADO POR','T',0,'C');
					
	
					//original o copia
					$this->cell('',4,$original,0,1,'R');
					
					
					$original = "COPIA";	
					
					$this->Ln();
					//$this->Ln();
				}//for

			}
			
			
			if ($save==1){
				$separa=explode('|',$myrow['folio']);	
				if ($type==12){
					$serie = $separa[1];
					$folio = $separa[0];
					$folder="Recibo";
				}else{
					$serie = $separa[0];
					$folio = $separa[1];
				}
				
				if($type=='10' or $tipofac=='110'){
					$folder="Facturas";
				}
				if($type=='13'){
					$folder="NCreditoDirect";
				}
				
				if($type=='21'){
					$folder="NCargo";
				}
				
				if($type=='11'){
					$folder="NCredito";
				}
				
				$direcciondos="./companies/".$_SESSION['DatabaseName']."/SAT/".str_replace(',','',str_replace('.','',str_replace(' ','',$legalname)))."/";
				$direcciondos=$direcciondos.'XML/'.$folder.'/'.$serie.$folio.'.pdf';
				//echo "<pre>".$direcciondos;
				$pdfcode=$this->Output($direcciondos, 'F');//F

			}
			else{	
				$this->OutPut('','I');		
				die();
			}
			
				
		}
		


	}



?>