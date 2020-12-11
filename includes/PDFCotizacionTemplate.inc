<?php

/* 
Elaboro: Desarrollador
FECHA DE MODIFICACION: 30-ene-2013
CAMBIOS:
   1. Se quito del pdf que no muestre texto narrative en la cotizacion
 FIN DE CAMBIOS
*/

	define('FPDF_FONTPATH','./fonts/');
	include_once('fpdf.php');
	include_once('fpdi.php');
	include_once('./Numbers/Words.php');
	 
	class ncrPDF extends FPDI {		
	
		function Header(){
			global $db,$legalname;
			
			$sql="select * ,tags.address4 as ciudad,tags.address5 as estado,tags.phone
			from tags inner join legalbusinessunit on legalbusinessunit.legalid=tags.legalid and tagref=".$_GET['Tagref'];
			$resultlegal=DB_query($sql,$db, $ErrMsg);
			$myrowlegal=DB_fetch_array($resultlegal);

			//$this->Image($myrowlegal['logo'],10,20,50,20);
			$this->Image($myrowlegal['logo'],10,20,55,15);
			$this->SetFont('helvetica','B',10);
			
			$this->SetY(20);
			$this->SetX(50);
			$legalname = $myrowlegal['legalname'];
			$this->cell('',6,strtoupper($legalname),0,1,'C');
			//$this->Ln();
			$this->SetFont('helvetica','B',9);
			$this->SetX(65);
			$this->SetFont('helvetica','',7);

			$qry = "Select * from areas";
			$rs = DB_query($qry,$db);
			while ($myrowi=DB_fetch_array($rs)){
				$sucursal=$myrowi[1].': '.$myrowi[3].' , '.$myrowi[4].' , '.$myrowi[5].' , '.$myrowi[6].', Cp.'.$myrowi['cp'].'  '.$myrowi['address6'];
				$this->SetX(65);				
				$this->MultiCell('',4,$sucursal,0,'L');
			}
			$this->SetX(65);				
			$this->SetFont('helvetica','B',8);
			$this->cell('',4,$myrowlegal['comments'],0,0,'L');

			$this->SetFont('helvetica','',9);
			if ($_GET['tipodocto']==1)
				$this->SetFont('helvetica','B',11);
			
			$this->SetX(80);
			//$this->cell(60,4,'www.iamssa.com.timesmx      informes@iamssa.com.mx',0,1,'C');
			$this->Ln();
			$this->cell('',4,$myrowlegal['ciudad'].' , '.$myrowlegal['estado'].' a '.Date('d').' de '.nombremeslargo(Date('m')).' de '.Date('Y'),0,1,'R');
			
			$sizeTexto = 8;
			if ($_GET['tipodocto']==1)
				$sizeTexto=11;
				
			$this->SetFont('helvetica','B',$sizeTexto);
			$this->cell('190',4,utf8_decode('Cotización: '),0,0,'R');
			$this->SetFont('helvetica','B',$sizeTexto);
			$this->cell('',4,$_GET['TransNo'],0,1,'R');
			if ($_GET['tipodocto']==2){
				
				$vigencia = 30;
				$qry = "SELECT paymentterms.daysbeforedue AS vigencia FROM salesorders INNER JOIN paymentterms ON paymentterms.termsindicator = salesorders.paytermsindicator WHERE orderno = '" . $_GET['TransNo'] . "'";
				$res = DB_query($qry, $db);
				if($row = DB_fetch_array($res)) {
					$vigencia = $row['vigencia'];
				}
				
				$qry = "Select currcode,date_format(orddate,'%d/%m/%Y') as orddate,date_format(date_add(orddate,INTERVAL kilometraje DAY),'%d/%m/%Y') as fvalida FROM salesorders WHERE orderno = ".$_GET['TransNo'];
				$res = DB_query($qry,$db);
				$regs = DB_fetch_array($res);
				
				$this->SetFont('helvetica','',8);
				$this->cell('182',4,utf8_decode('F. Elaboración: '),0,0,'R');
				$this->SetFont('helvetica','B',8);
				$this->cell('',4,$regs['orddate'],0,1,'R');

				$this->SetFont('helvetica','',8);
				$this->cell('182',4,'Vigencia al: ',0,0,'R');	
				$this->SetFont('helvetica','B',8);
				$this->cell('',4,$regs['fvalida'],0,1,'R');	

				$this->SetFont('helvetica','',8);
				$this->cell('190',4,'Moneda: ',0,0,'R');	
				$this->SetFont('helvetica','B',8);
				$this->cell('',4,$regs['currcode'],0,1,'R');	
			}

			$this->Ln();	

		}
		
		function exportPDF($send=0){
			global $db,$legalname; ;
			
			$numdocs = $_SESSION['total'];			
			$arrDatos = $_SESSION['datatoprint'];
			$idoc = 0;
			$i=1;			
	
			$this->fpdf('P','mm','Letter');
			$this->AliasNbPages();
			if ($_GET['tipodocto']==1)
				$this->SetAutoPageBreak(true,35);
			else
				$this->SetAutoPageBreak(true,11);
			
			
			$this->AddPage();

			
			$SQL="select * from salesorders inner join custbranch on custbranch.branchcode=salesorders.branchcode where orderno=".$_GET['TransNo'];
			$resultcliente=DB_query($SQL,$db, $ErrMsg);
			$myrowcliente=DB_fetch_array($resultcliente);

			$this->SetFont('times','B',12);
			$textoate="AT'N:";
			//$this->cell('20',4,$textoate,0,0,'L');
			$this->SetFont('helvetica','',12);

			$sizeTexto = 9;
			if ($_GET['tipodocto']==1)
					$sizeTexto=11;
			
			$sqlheader="select * from  PDFTemplates where tipodocto=".$_GET['tipodocto']." and Ubicacion='Header' order by orden ";
			$resulttext=DB_query($sqlheader,$db, $ErrMsg);
			while ($myrowheader=DB_fetch_array($resulttext)){
				$this->SetFont('helvetica','',9);
				
				if ($myrowheader['Titulo']=="AT'N"){
					//$this->SetX($this->GetX()+5);
					$this->SetFont('helvetica','B',$sizeTexto);
					if (strlen($myrowheader['consulta'])>0){
						$SQL=$myrowheader['consulta'].$_GET['TransNo'];
						$resultcliente=DB_query($SQL,$db, $ErrMsg);
						$myrowcliente=DB_fetch_array($resultcliente);
						
						$txt = $myrowheader['Titulo'].": ".$myrowcliente[0]; 
						$this->cell('',5,$txt,0,1,'C');
					}
						
					$this->Ln();
				}
				
				if (strlen($myrowheader['Titulo'])>0 and $myrowheader['Titulo']!='encabezado' and $myrowheader['Titulo']!="AT'N"){
					//$this->SetX($this->GetX()+5);
					$this->SetFont('helvetica','B',$sizeTexto);
					$this->MultiCell('',4,($myrowheader['Titulo']),0,'L');
					if (!($_GET['tipodocto']==1 && $myrowheader['idtexto']==1))
						$this->Ln();
				}
				$Titulo=$myrowheader['Titulo'];
				if (strlen($myrowheader['Titulo'])>0 and $myrowheader['Titulo']=='encabezado'){
					if (strlen($myrowheader['consulta'])>0){
						$SQL=$myrowheader['consulta'].$_GET['TransNo'];
						$resultcliente=DB_query($SQL,$db, $ErrMsg);
						$myrowcliente=DB_fetch_array($resultcliente);	
						$myrowheader['Titulo']=$myrowcliente[0];
					}
					$this->SetFont('helvetica','B',$sizeTexto);
					$this->MultiCell('',4,($myrowheader['Titulo']),0,'L');
					
					if ($_GET['tipodocto']==1){
						$this->SetFont('helvetica','B',11);

						$qry = "select custbranch.* 
								from salesorders 
									inner join custbranch 
										ON salesorders.branchcode = custbranch.branchcode 
								where salesorders.orderno=".$_GET['TransNo'];
						$res = DB_query($qry,$db);
						$reg = DB_fetch_array($res);
						$this->cell('',4,$reg['braddress1'],0,1,'L');
						$this->cell('',4,$reg['braddress6'],0,1,'L');
						$this->cell('',4,$reg['braddress2'].', '.$reg['braddress3'].'  CP '.$reg['braddress4'],0,1,'L');
						$this->Ln(6);
					}
					else
						$this->Ln();
					
				}
				
				$myrowheader['Texto'] = $myrowheader['Texto'];
				
				if (strlen($myrowheader['consulta'])>0){
					$SQL=$myrowheader['consulta'].$_GET['TransNo'];
					$resultcliente=DB_query($SQL,$db, $ErrMsg);
					$myrowcliente=DB_fetch_array($resultcliente);	
					$myrowheader['Texto']=$myrowcliente[0];
				}
				
				if (strlen($myrowheader['Texto'])>0 and $Titulo!='encabezado' and $myrowheader['Titulo']!="AT'N"){
					$this->SetFont('helvetica','',$sizeTexto);
					//$this->SetX($this->GetX()+5);
					if ($myrowheader['idtexto']==7){
						$txt = $myrowheader['Texto'];
						$arrlineas = explode(chr(13).chr(10),$txt);
						for($i=0;$i<count($arrlineas);$i++){
							$linea = $arrlineas[$i];
							$arrdato = explode(":",$linea);
							$this->cell(90,4,$arrdato[0],0,0,'L');
							$this->cell(90,4,$arrdato[1],0,1,'L');
							
						}
					}
					else
					if($pos = strpos($myrowheader['Texto'], '.jpg')==true){
						//$this->MultiCell('',4,$myrowheader['Texto'].'encontre',0,1,'FJ');
						
						$this->Image($myrowheader['Texto'],$this->GetX()+80,$this->GetY(),50,20);
						$this->Ln();
						$this->Ln();
						$this->Ln();
						$this->Ln();
						$this->Ln();
					}else{
						$alineacion='J';	
						$txt = $myrowheader['Texto'];
						if ($myrowheader['idtexto']==54){
							$alineacion='L';
							$this->SetX(70);
						}
						else{
							$arrtxt = explode("|",$myrowheader['Texto']);
							if (count($arrtxt)==2){
								$this->SetFont('helvetica','B',$sizeTexto);
								$this->cell('',4,$arrtxt[0],0,1,'L');
								$this->SetFont('helvetica','',$sizeTexto);
								$txt = $arrtxt[1];
							}
						}
						$this->MultiCell('',4,$txt,0,$alineacion);
										
					}
					
					
					if ($_GET['tipodocto']==1)
						$this->Ln(10);
					else
						$this->Ln();
				}
			}
			
			
		
			if ($_GET['tipodocto']==1){

				$sql="select distinct stkcode,stockmaster.description,stockmaster.categoryid,stockmaster.kgs,stockmaster.height,
										stockmaster.width,stockmaster.large
						 from  salesorderdetails, stockmaster
						WHERE stkcode = stockid
						and orderno = ".$_GET['TransNo']."
						Order By orderlineno";
						
				$rsstk=DB_query($sql,$db);
				if (DB_num_rows($rsstk) > 0){
					
					
					while ($regstk=DB_fetch_array($rsstk)){
						$catid = $regstk['categoryid'];
						$stkcode = $regstk['stkcode'];
						$descrip = $regstk['description'];
						$peso = $regstk['kgs'];
						$largo = $regstk['large'];
						$alto = $regstk['height'];
						$ancho = $regstk['width'];
						/*
						$qry = "select distinct image,textimage
								from salesorderdetails inner join stockmaster on stockmaster.stockid=salesorderdetails.stkcode
								inner join stockcategory on stockmaster.categoryid=stockcategory.categoryid
								where salesorderdetails.stkcode = '$stkcode'
								and salesorderdetails.orderno=".$_GET['TransNo'];
						
						$resultcliente=DB_query($qry,$db, $ErrMsg);
						$show=false;
						$first=true;
						while ($myrowbody=DB_fetch_array($resultcliente)){
							if (strlen($myrowbody[1]) > 3 || strpos($myrowbody[0], '.jpg')){
								$show=true;
								//$this->SetX($this->GetX()+45);
								if($first){
									$this->AddPage();
									$first=false;
								}
								$this->SetFont('helvetica','B',$sizeTexto);
								$this->cell('',4,$descrip,0,1,'C');
								$this->SetFont('helvetica','',$sizeTexto);
								$this->Ln(8);
								if($pos = strpos($myrowbody[0], '.jpg')==true){
									$this->Image($myrowbody[0],$this->GetX()+70,$this->GetY(),50,20);
									$this->Ln(35);
								}
								$this->MultiCell('',4,$myrowbody[1],0,'J');
							}
						}	
						
						if ($show){
							$this->Ln(4);
						
							$qry = "select stockmaster.longdescription
									from salesorderdetails inner join stockmaster on stockmaster.stockid=salesorderdetails.stkcode
									where salesorderdetails.stkcode = '$stkcode'
									and salesorderdetails.orderno=".$_GET['TransNo'];
									
							$result=DB_query($qry,$db, $ErrMsg);
							$this->SetFont('helvetica','',$sizeTexto);
							while ($myrowbody=DB_fetch_array($result)){
								if ($descrip!=$myrowbody[0])
									$this->MultiCell('',4,$myrowbody[0],0,'J');
								
							}	
							$this->Ln(4);
						}
						*/
						//buscar si producto tiene ficha tecnica
						$qry = "select stockmaster.fichatecnica
								from salesorderdetails inner join stockmaster on stockmaster.stockid=salesorderdetails.stkcode
								where salesorderdetails.stkcode = '$stkcode'
								and salesorderdetails.orderno=".$_GET['TransNo'];
						
						$result=DB_query($qry,$db, $ErrMsg);
						$this->SetFont('helvetica','',$sizeTexto);
						if ($myrowbody = DB_fetch_array($result)){
							if ($myrowbody['fichatecnica']!=""){
								$pagecount = $this->setSourceFile('./productlist/'.$myrowbody['fichatecnica']);
								$this->AddPage();
								for($i=1;$i<=$pagecount;$i++){
									$tplid = $this->importPage($i);
									$arrwh = $this->useTemplate($tplid,$this->GetX(),$this->GetY());	
									if ($arrwh['h'] > 270)
										$this->AddPage();			
								}
								
							}
						}
						
						/*
						if ($catid=="147"){//compresores
							$this->SetFont('helvetica','B',9);
							$this->cell(70,4,'Flujos F. A. D. ( ISO 1217, 1996, Anexo C )',0,0,'L');
							$this->SetFont('helvetica','',9);
							$this->cell('',4,'',0,1,'L');
							
							$this->SetFont('helvetica','B',9);
							$this->cell(70,4,'Medidas y Peso',0,0,'L');
							$this->SetFont('helvetica','',9);
							$posx = $this->GetX();
							$this->cell('',4,'Largo '.$largo.'"',0,1,'L');
							$this->SetX($posx);
							$this->cell('',4,'Ancho '.$ancho.'"',0,1,'L');
							$this->SetX($posx);
							$this->cell('',4,'Alto '.$alto.'"',0,1,'L');
							$this->SetX($posx);
							$this->cell('',4,'Peso (kg) '.$peso,0,1,'L');
							
							$this->SetFont('helvetica','B',9);
							$this->cell(70,4,'Nivel Sonoro dB ( A )',0,0,'L');
							$this->SetFont('helvetica','',9);
							$this->cell('',4,'',0,1,'L');
							
							$this->Ln(4);
							
						}*/
					}
				}
				$this->AddPage();
				
			}		
			
			$this->SetFont('helvetica','B',10);
			$this->cell('',4,'OFERTA COMERCIAL',0,1,'C');
			
			
			$this->SetFillColor(220,220,220);
			$this->SetFont('helvetica','B',9);
			$this->cell(10,5,'Cant.',1,0,'C');
			$this->cell(30,5,'Clave',1,0,'C');
			

			$ordencompra = false;
			$qry = "Select * 
					from purchorders
					where purchorders.requisitionno = '".$_GET['TransNo']."'";
			//$r = DB_query($qry,$db);
			//if (DB_num_rows($r)>0)
				///$ordencompra=true;
				
			$xsize=135;
			if ($ordencompra)
				$xsize=115;
			
			$xsize=110;
				
			$this->cell($xsize,5,'Concepto',1,0,'L');
			if ($ordencompra)
				$this->cell(20,5,'Entrega',1,0,'C');
		
			$this->cell(20,5,'Precio',1,0,'C');
			$this->cell(25,5,'Importe',1,0,'C');
			
			$type = $arrDatos[$idoc]['type'];
			$transno = $arrDatos[$idoc]['transno'];
			$orderno = $arrDatos[$idoc]['orderno'];
			
			$sql = "SELECT 	salesorderdetails.unitprice as precio,
			salesorderdetails.stkcode as codigo,
			salesorderdetails.quantity as cantidad,
			salesorderdetails.discountpercent as descu,
			salesorderdetails.discountpercent1 as desc1,
			salesorderdetails.discountpercent2 as desc2,
			upper(salesorderdetails.narrative) ,
			'' as infext,
			stockmaster.longdescription as descripcion,
			salesorders.orderno,
			salesorders.customerref,	
			salesorders.orddate,
			salesorders.deliverydate,	
			salesorders.comments,
			salesorders.quotation as title,	
			salesorders.deliverto as nombrefrom,
			salesorders.deladd1 as dirfrom1,
			salesorders.deladd2 as dirfrom2,
			salesorders.deladd3 as dirfrom3,
			salesorders.deladd4 as dirfrom4,
			salesorders.deladd5 as dirfrom5,
			salesorders.deladd6 as dirfrom6,
			salesorders.contactphone as tel,
			salesorders.orddate as fecha,
			salesorders.taxtotal as iva,
			salesorders.paytermsindicator as termino,
			salesorders.placa as placas,
			salesorders.serie as serie,
			salesorders.kilometraje as kilometraje,
			salesman.salesmanname as vendedor,
			debtorsmaster.debtorno as cliente,
			debtorsmaster.name as nombre,
			debtorsmaster.address1 as dir1,
			debtorsmaster.address2 as dir2,
			debtorsmaster.address3 as dir3,
			debtorsmaster.address4 as dir4,
			debtorsmaster.address5 as dir5,
			debtorsmaster.address6 as dir6,
			salesorders.currcode as moneda,
			custbranch.brname,
			custbranch.phoneno,
			custbranch.faxno,
			salestypes.sales_type as lista,
			case when salesorders.currcode='MXN' then 1 else 0 end as tipocambio,
			(salesorderdetails.quantity*salesorderdetails.unitprice)-(((salesorderdetails.quantity*salesorderdetails.unitprice)*(1-salesorderdetails.discountpercent))*(1-salesorderdetails.discountpercent1))*(1-salesorderdetails.discountpercent2) as totaldesc
		FROM salesorderdetails INNER JOIN stockmaster
			ON salesorderdetails.stkcode = stockmaster.stockid
			INNER JOIN salesorders ON salesorders.orderno = salesorderdetails.orderno 
			INNER JOIN salesman ON salesman.salesmancode = salesorders.salesman
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = salesorders.debtorno
			INNER JOIN custbranch ON custbranch.branchcode = salesorders.branchcode
			INNER JOIN salestypes ON salestypes.typeabbrev = salesorders.ordertype
		WHERE  salesorderdetails.stkcode = stockmaster.stockid
			AND salesorders.orderno = salesorderdetails.orderno
			AND salesman.salesmancode = salesorders.salesman
			AND debtorsmaster.debtorno = salesorders.debtorno
			AND custbranch.branchcode = salesorders.branchcode
			AND salestypes.typeabbrev = salesorders.ordertype
			AND salesorderdetails.orderno =".$_GET['TransNo']."				
		ORDER BY salesorderdetails.orderlineno";
		
	$result=DB_query($sql,$db, $ErrMsg);

				//$result=DB_query($sql,$db);
				if (DB_num_rows($result)>0){
					$myrow=DB_fetch_array($result);
					$result=DB_query($sql,$db);
					$this->Ln();
					
						$totdesc = 0;
						$tot = 0;
						while ($myrow2=DB_fetch_array($result)){					
							$datos=trim($myrow2['comentarios']);
							$x=$this->GetX();
							$y=$this->GetY();
							$this->SetFont('helvetica','',9);
							$this->cell(10,4,abs($myrow2['cantidad']),'L',0,'C');
							$this->cell(30,4,$myrow2['codigo'],0,0,'L');
							$x=$this->GetX();
							$y=$this->GetY();
							$this->MultiCell($xsize,4,htmlspecialchars_decode($myrow2['descripcion']).chr(13).chr(10).htmlspecialchars_decode(str_replace("&AMP;QUOT;&q","\"",$myrow2['infext'])),0,'L');
							$posYMultiCell = $this->GetY();
							if ($y < $posYMultiCell){								
								$yaux = $y+4;	
								while ($yaux < $posYMultiCell){
									$this->SetY($yaux);
									$this->cell(195,4,'','LR',1,'L');
									$yaux+=4;
								}
							}							
							$this->SetXY($x+$xsize,$y);// para regresar a la posicion sigte
							if ($ordencompra){
								$qry = "Select purchorderdetails.deliverydate  
										from purchorders 
										inner join purchorderdetails
										ON purchorders.orderno = purchorderdetails.orderno 
										where requisitionno=".$_GET['TransNo']."
										and purchorders.status!='Pending' and purchorders.status!='Cancelled'
										and itemcode = '".$myrow2['codigo']."'
										";
								$rsd = DB_query($qry,$db);
								$reg = DB_fetch_array($rsd);
								$this->cell(20,4,$reg['deliverydate'],0,0,'L');									
							}
								
							
							$this->cell(20,4,number_format((($myrow2['cantidad']*$myrow2['precio'])-$myrow2['totaldesc'])/$myrow2['cantidad'],2),0,0,'R');
							//$this->cell(10,4,number_format(abs($myrow2['precio']),2),1,0,'R');
							$this->cell(25,4,number_format(abs(($myrow2['cantidad']*$myrow2['precio'])-$myrow2['totaldesc']),2),'R',1,'R');
							$this->SetY($posYMultiCell);
							
							$this->cell(195,0,'','T',1,'C');
														
							/*$this->SetLineWidth(.1);
							$this->Cell(150,0,'','T');
							$this->Ln();
							*/
							$itemsCount++;
							
							$tot += abs($myrow2['cantidad']*$myrow2['precio']);
							$totdesc += $myrow2['totaldesc'];
						}
						$this->cell(195,0,'','T',1,'C');
						
						/*if ($_GET['tipodocto']==1){
							$txtmoneda = "dólares americanos";
							if ($myrow['moneda']=="MXN")
								$txtmoneda = "pesos mexicanos";
								
							$this->Ln(4);
							$this->SetFont('helvetica','B',9);
							$this->cell('',4,'Total de la Oferta Comercial '.$myrow['moneda'].' $:'.number_format($tot,2),0,1,'C');
							$this->cell('10',4,'Nota: ',0,0,'L');
							$this->SetFont('helvetica','',9);
							$this->cell('',4,utf8_decode('Todos los precios anteriormente descritos son en '.$txtmoneda.' y deberá agregar adicionalmente el 16% de I. V. A.'),0,1,'L');
							$this->Ln();
						}*/
						
						$iva=$tot-$totdesc;
						$iva = $iva*(0.16);
						
						//if ($_GET['tipodocto']==2){
							$this->SetX(165);
							$this->cell(20,4,'Sub Total',0,0,'L');
							$this->cell(20,4,number_format($tot-$totdesc,2),0,1,'R');
							$this->SetX(165);
							//$this->cell(20,4,'Descuento',0,0,'L');
							//$this->cell(20,4,number_format($totdesc,2),0,1,'R');
							//$this->SetX(165);
							$this->cell(20,4,'I.V.A.',0,0,'L');
							$this->cell(20,4,number_format($iva,2),0,1,'R');
							$this->SetX(165);
							$this->SetFont('helvetica','B',9);
							$this->cell(20,4,'Total','T',0,'L');
							$this->cell(20,4,number_format($tot-$totdesc+$iva,2),'T',1,'R');
						//}
						
						$i++;	
						$idoc++;
				}
			
			//if ($_GET['tipodocto']==2){
				$montoctvs1 = round($tot-$totdesc+$iva,2);
                $separa=explode(".",$montoctvs1);
                $montoctvs2 = $separa[1];
				
				if ($montoctvs2=="")
					$montoctvs2="00";
				
                if (left($montoctvs2,3)>956){
                        $montoctvs2=0;
                }
				if (strlen($montoctvs2)==1){
                        $montoctvs2.="0";
                }
				
                $montoletra=Numbers_Words::toWords($separa[0],'es');

                $montocentavos=Numbers_Words::toWords($montoctvs2,'es');
                if ($myrow['moneda']=='MXN'){
                   $montoletra=ucwords($montoletra) . " Pesos ". $montoctvs2 ." /100 M.N.";
                }
                else{
	               $montoletra=ucwords($montoletra) . " Dolares ". $montoctvs2 ."/100 USD" ;
                }
                
                $this->SetFont('helvetica','B',9);
				$this->cell(30,4,'Cantidad en letras: ',0,0,'L');
				$this->SetFont('helvetica','',9);
				$this->cell('',4,$montoletra,0,1,'L');
				$this->Ln();
			//}
			
				
			$sqlfooter="select * from  PDFTemplates where tipodocto=".$_GET['tipodocto']." and Ubicacion='Footer' and Orden > 0 Order by Orden";
			$resulttextf=DB_query($sqlfooter,$db, $ErrMsg);
			$tope=225;
			while ($myrowfooter=DB_fetch_array($resulttextf)){
				
				if ($_GET['tipodocto']==2 )
					$tope = 265;
					
					if ($this->GetY() >= $tope)
						$this->AddPage();
					else
						if($_GET['tipodocto']==2 && $myrowfooter['idtexto']==49 && $this->GetY() > 230)
						 	$this->AddPage();
				
				
				if (strlen($myrowfooter['Titulo'])>0){
					$this->SetFont('helvetica','B',9);
					$posY = $this->GetY();
					$wx = $this->GetStringWidth($myrowfooter['Titulo']);
					$this->MultiCell('',4,$myrowfooter['Titulo'],0,'L');
					if ($myrowfooter['consulta']=='' && $myrowfooter['Texto']=='')
						$this->Ln();
					else{
						$this->SetXY($this->GetX() + $wx + 2,$posY);	
						
					}
				}
				
				$txt = $myrowfooter['Texto'];
				
				//if (strlen($myrowfooter['Texto'])>0){
					$this->SetFont('helvetica','',9);
					if (strlen($myrowfooter['consulta'])>0){
						$SQL=$myrowfooter['consulta'].$_GET['TransNo'];
						$resultcliente=DB_query($SQL,$db, $ErrMsg);
						$myrowcliente=DB_fetch_array($resultcliente);	
						$txt=$myrowcliente[0];
					}
					if (strlen($txt)>0){
						
						$this->MultiCell('',4,$txt,0,'J');
						$this->Ln();
					}
				//}
			}

			$sqlfooter="select * from  PDFTemplates where tipodocto=".$_GET['tipodocto']." and Ubicacion='Footer2' and Orden > 0 Order by Orden";
			$resulttextf=DB_query($sqlfooter,$db, $ErrMsg);
			while ($myrowfooter=DB_fetch_array($resulttextf)){
				
				if ($_GET['tipodocto']==1 && $this->GetY() >=215){
					$this->AddPage();
				}
				
				if (strlen($myrowfooter['Titulo'])>0){
					$this->SetFont('helvetica','B',9);
					$alinear='L';
					$ancho=65;
					if ($myrowfooter['centrarTitulo']==1){
						$this->SetFont('helvetica','B',11);
						$alinear='C';
						$ancho='';
					}
					$this->cell($ancho,4,$myrowfooter['Titulo'],0,0,$alinear);
					if ($_GET['tipodocto']!=1)
						$this->Ln();
				}
				
				$txt = $myrowfooter['Texto'];
				
				//if (strlen($myrowfooter['Texto'])>0){
					$this->SetFont('helvetica','',9);
					if (strlen($myrowfooter['consulta'])>0){
						$SQL=$myrowfooter['consulta'].$_GET['TransNo'];
						$resultcliente=DB_query($SQL,$db, $ErrMsg);
						$myrowcliente=DB_fetch_array($resultcliente);	
						$txt=$myrowcliente[0]. ". ".$txt ;
					}
					if (strlen($txt)>0){
						if ($_GET['tipodocto']==1)
							$this->SetX(70);
						
						$this->MultiCell('',4,$txt,0,'J');
						
					}
					$this->Ln();
				//}
			}
				

			$sqlfooter="select * from  PDFTemplates where tipodocto=".$_GET['tipodocto']." and Ubicacion='Footer3' and Orden > 0 Order by Orden";
			$resulttextf=DB_query($sqlfooter,$db, $ErrMsg);
			while ($myrowfooter=DB_fetch_array($resulttextf)){
				if (strlen($myrowfooter['Titulo'])>0){
					$this->SetFont('helvetica','B',9);
					$this->MultiCell('',4,utf8_decode($myrowfooter['Titulo']),0,'L');
					$this->Ln();
				}
				
				$txt = utf8_decode($myrowfooter['Texto']);
				
				//if (strlen($myrowfooter['Texto'])>0){
					$this->SetFont('helvetica','',9);
					if (strlen($myrowfooter['consulta'])>0){
						$SQL=$myrowfooter['consulta'].$_GET['TransNo'];
						$resultcliente=DB_query($SQL,$db, $ErrMsg);
						$myrowcliente=DB_fetch_array($resultcliente);	
						$txt=$myrowcliente[0];
					}
					if (strlen($txt)>0){
						
						$this->MultiCell('',4,$txt,0,'J');
						$this->Ln();
					}
				//}
			}
			
			/*if ($_GET['tipodocto']==1){
				
				//poner usuario y puesto
				//$qry = "Select realname as firma,ifnull(puesto,'') as puesto FROM www_users WHERE userid='".$_SESSION['UserID']."'";
				$qry = "Select salesman.salesmanname as firma,ifnull(www_users.puesto,'') as puesto
						FROM salesorders 
							inner join salesman
								ON salesorders.salesman = salesman.salesmancode
								and salesorders.orderno = ".$_GET['TransNo']."
							left join www_users
								ON 	salesman.usersales = www_users.userid

							";
				$res = DB_query($qry,$db);
				$reg = DB_fetch_array($res);
				$this->cell('',4,$reg['firma'],0,1,'L');
				$this->cell('',4,$reg['puesto'],0,1,'L');
				$this->cell('',4,$legalname,0,1,'L');
			}*/

			if ($send==1){
				return $this->Output('','S');
			}
			else
				$this->Output('','I');//F
		}
		
		function Footer(){
			global $db;
			
			if ($_GET['tipodocto']==2){
			
				$this->SetY(-10);
				$sqlfooter="select * from  PDFTemplates where tipodocto=".$_GET['tipodocto']." and Ubicacion='Lastline' and Orden > 0 Order by Orden";
				$resulttextf=DB_query($sqlfooter,$db, $ErrMsg);
				while ($myrowfooter=DB_fetch_array($resulttextf)){
					if (strlen($myrowfooter['Titulo'])>0){
						$this->SetFont('helvetica','B',9);
						$this->MultiCell('',4,utf8_decode($myrowfooter['Titulo']),0,'L');
						$this->Ln();
					}
					
					$txt = utf8_decode($myrowfooter['Texto']);
					
					//if (strlen($myrowfooter['Texto'])>0){
						$this->SetFont('helvetica','',7);
						if (strlen($myrowfooter['consulta'])>0){
							$SQL=$myrowfooter['consulta'].$_GET['TransNo'];
							$resultcliente=DB_query($SQL,$db, $ErrMsg);
							$myrowcliente=DB_fetch_array($resultcliente);	
							$txt=$myrowcliente[0];
						}
						if (strlen($txt)>0){
							$this->cell('15',4,$txt,0,0,'L');
							//$this->Ln();
						}
					//}
				}
			}

			if ($_GET['tipodocto']==1){
				$this->SetY(-34);
				$this->Image('images/pie_cotizafull.jpg',11,$this->GetY(),195,30);

				$this->SetTextColor(255,255,255);
				$this->SetY($this->GetY()+26);
			}

			$this->SetFont('helvetica','B',6);
			$this->cell('',5,'Pagina '.$this->PageNo().'/{nb}',0,0,'R');
		}

	}



?>