<?php
	define('FPDF_FONTPATH','./fonts/');
	include ('fpdf.php');
	
	class pdfpurchorder extends FPDF {
		
		// Atributo de clase agregado, ya que en el header se saca esta informacion y la requiero en el llamado del metodo export
		var $leyendaPiePagina = '';
		
		function printPDF($archivo=""){
			global $db;
			
			$this->fpdf('P','mm','Letter');
			$this->SetAutoPageBreak(true,10);
			
			$this->AddPage();
			
			$this->SetFont('helvetica','',7);
				
			$sql = "SELECT purchorderdetails.itemcode,
			   			CASE purchorderdetails.deliverydate
						WHEN '0000-00-00' THEN purchorders.orddate
						ELSE purchorderdetails.deliverydate
						END AS deliverydate,
						itemdescription,
						sum(unitprice ) as unitprice,
						sum(unitprice)/tabla.contador as unitprice,
						tabla.contador,
						units,
						sum(quantityord) as quantityord,
						decimalplaces,
						sum(purchorderdetails.discountpercent1)/tabla.contador as discountpercent1,
						sum(purchorderdetails.discountpercent2)/tabla.contador as discountpercent2,
						sum(purchorderdetails.discountpercent3)/tabla.contador as discountpercent3,
						sum(ifnull(taxauthrates.taxrate,0))/tabla.contador as taxrate,
						purchorderdetails.narrative,
						justification,
						purchorders.currcode,
						barcode,stockautor,stockmanufacturer.manufacturer,
						purchorders.supplierno,
						suppliers.taxgroupid,
						purchorderdetails.narrative,
						stockcategory.cattipodescripcion,
						stockcategory.MensajeOC,
						stockmaster.longdescription as DescipcionProd,
						sum(taxgroups.taxrate)/tabla.contador AS taxgrouprate,
						stockmaster.taxcatid,
						stockcategory.MensajeOC,
						stockmaster.decimalplaces
					FROM purchorderdetails
			   			LEFT JOIN stockmaster ON purchorderdetails.itemcode=stockmaster.stockid
						LEFT JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
			   			LEFT JOIN purchorders ON purchorders.orderno = purchorderdetails.orderno
						INNER JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
						LEFT JOIN stockmanufacturer ON stockmanufacturer.manufacturerid=stockmaster.manufacturer
						LEFT JOIN taxauthrates ON stockmaster.taxcatid=taxauthrates.taxcatid AND taxauthrates.dispatchtaxprovince=suppliers.taxgroupid
						LEFT JOIN taxgroups ON suppliers.taxgroupid = taxgroups.taxgroupid
						INNER JOIN (SELECT COUNT(purchorders.orderno) as contador,
											purchorders.supplierorderno,
											purchorderdetails.itemcode
									FROM purchorders
										INNER JOIN purchorderdetails on purchorderdetails.orderno = purchorders.orderno
									WHERE purchorders.supplierorderno ='".$_GET['supplierorderno']."'
									GROUP BY  purchorderdetails.itemcode
									) as tabla ON  purchorders.supplierorderno = tabla.supplierorderno and purchorderdetails.itemcode = tabla.itemcode
					WHERE purchorders.supplierorderno = '".$_GET['supplierorderno']."'
					GROUP BY  purchorderdetails.itemcode";
			/*if($_SESSION['UserID'] == "admin"){
				echo '<pre>'.$sql;
			}*/		
			$result=DB_query($sql,$db);
			$taxtotal = 0;
			$subtotal = 0;
			while ($rows = DB_fetch_array($result)){
				$currcode = $rows['currcode'];
				$MensajeOC = $rows['MensajeOC'];
				$d1=$rows['discountpercent1'];
				$d2=$rows['discountpercent2'];
				$d3=$rows['discountpercent3'];
				$taxgroupid = $rows['taxgroupid'];
				$taxcatid = $rows['taxcatid'];
				$taxrate = $rows['taxrate'];
				if(empty($rows['decimalplaces']) == TRUE){
					$decimales = 0;
				}else{
					$decimales = $rows['decimalplaces'];
				}
				if($taxcatid == null) { // si viene nulo ir sobre el rate del proveedor
					$taxrate = $rows['taxgrouprate'];
				}
				$totalline = ($rows['unitprice']*$rows['quantityord'])*(1-($d1/100))*(1-($d2/100))*(1-($d3/100));
				$subtotal += $totalline;
				
				$LineTax = ($rows['unitprice']*$rows['quantityord'])*(1-($d1/100))*(1-($d2/100))*(1-($d3/100))*$taxrate;
				$taxtotal += $LineTax;
				if($rows['cattipodescripcion'] == 1){
					$rows['itemdescription'] = $rows['DescipcionProd'].chr(13).chr(10).$rows['narrative'].chr(13).chr(10);
				}else{
					$rows['itemdescription'] = $rows['itemdescription'].chr(13).chr(10).$rows['narrative'].chr(13).chr(10);
				}
				
				$this->cell(15,4,$rows['quantityord'],'L',0,'L');
				//$this->cell(25,3,$cod,0,0,'L');
				$this->cell(40,4,$rows['itemcode'],0,0,'L');
				$actX = $this->GetX();
				$actY = $this->GetY();
				$rows['itemdescription']=trim(str_replace('\r\n', chr(13).chr(10), trim($rows['itemdescription'])));
				$MensajeOC = trim(str_replace('\r\n', chr(13).chr(10), trim($MensajeOC))); 
				$this->SetFont('helvetica','',6);
				$descripcionnorma = $rows['itemdescription'];
				$this->MultiCell(100,3,$descripcionnorma,0,'L');
				$desp = $this->GetY();
				$posYMultiCell = $this->GetY();
				if ($actY < $posYMultiCell){
					$y = $actY+3;
					while ($y < $posYMultiCell){
						$this->SetY($y);
						$this->cell(195,3,'','LR',1,'L');
						$y+=3;
					}
				}
				$this->SetXY($actX+100,$actY);// para regresar a la posicion sigte
				//$this->MultiCell(100,4,$comments,0,'L',1);
				$this->SetFont('helvetica','',7);
				$this->cell(20,4,number_format($rows['unitprice'],$decimales),0,0,'R');
				$this->cell(20,4,number_format($totalline,$decimales),'R',1,'R');
				//$this->cell(30,3,number_format($stockprecio,2),0,0,'R');
				//$this->Ln(6);
				if($desp > $actY){
					$despln = $desp -$actY;
					$this->Ln($despln-4);
				}
				
			}
			
			$posYMultiCell=$posYMultiCell-3;
			$this->SetY($posYMultiCell);
			$this->cell(195,3,'','B',1,'C');
			//
			//$this->Ln(4);
			$posY = $this->GetY();
			//$this->SetX(30);
			//$this->cell(150,6,'Norma '.$MensajeOC,0,0,'R');
			$this->cell(155,6,'Subtotal',0,0,'R');
			$this->cell('',6,number_format($subtotal,$decimales),0,1,'R');
			if($taxgroupid == 2){
			}else{
				$this->cell(180,6,'IVA',0,0,'R');
				$this->cell('',6,number_format($taxtotal,$decimales),0,1,'R');
			}
			$this->SetFont('helvetica','B',8);
			$this->SetFillColor(205,205,205);
			$this->SetX($this->GetX()+140);
			$this->cell(35,5,'Total',0,0,'R',1);
			$this->SetFillColor(254,154,46);
			$this->cell(20,5,number_format($subtotal+$taxtotal,$decimales),0,1,'R',1);
			
			$montoctvs1 = round($subtotal+$taxtotal,$decimales);
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
			
			
			if ($currcode=="MXN"){
				$montoletra=Numbers_Words::toWords($separa[0],'es');
				$montocentavos=Numbers_Words::toWords($montoctvs2,'es');
				$montoletra=ucwords($montoletra) . " Pesos ". $montoctvs2 ." /100 M.N.";
			}
			else{
				$qry = "Select currency from currencies where currabrev = '$currcode'";
				$rmon = DB_query($qry,$db);
				$row = DB_fetch_array($rmon);
				$nombremoneda = " ".$row[0]." ";
				
				$montoletra=Numbers_Words::toWords($separa[0],'en_US');
				$montocentavos=Numbers_Words::toWords($montoctvs2,'en_US');
				$montoletra=ucwords($montoletra) . $nombremoneda . $montoctvs2 ."/100 ".$currcode ;
			}
			
			$this->SetFont('helvetica','',7);
			$this->cell('',4,$montoletra,0,1,'R');
			$lastY = $this->GetY();
			
			
			$this->Ln();
			$this->SetY($posY);
			$leyenda = $this->leyendaPiePagina;
			$this->MultiCell(100,4, $leyenda,0,'L');
			
			
			//comments
			$this->SetY($lastY);
			$this->Ln(2);
			$sql = "Select comments FROM purchorders where orderno = ".$_GET['OrderNo'];
			$res = DB_query($sql,$db);
			$reg = DB_fetch_array($res);
			if ($reg[0]!=""){
				$this->SetFillColor(205,205,205);
				$comments = "Comments: ".chr(13).chr(10).$reg[0];
				$this->MultiCell(100,4,$comments,0,'L',1);	
			}
			
			if ($archivo=="")
				$this->Output('','I');
			else
				$this->Output($archivo,'F');
				
			
		}
		function Footer()
		{
			global $db;
			$this->SetFillColor(205,205,205);
			$this->Ln(4);
			$this->SetFont('helvetica','',5);
			$sqlleyen = "SELECT distinct stockcategory.MensajeOC
						 FROM purchorderdetails
							LEFT JOIN stockmaster ON purchorderdetails.itemcode=stockmaster.stockid
							LEFT JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
							WHERE purchorderdetails.orderno = '".$_GET['OrderNo']."'";
			$resultleyen = DB_query($sqlleyen, $db);
			while ($myrowleyen = DB_fetch_array($resultleyen)){
				$leyenda = $leyenda.$myrowleyen['MensajeOC'].chr(13).chr(10);
			}
			$this->MultiCell(200,4,$leyenda,0,'L');
			$this->Ln(4);
			$this->SetFont('helvetica','B',10);
			$this->cell(25,6,'Elaboro',0,0,'L');
			
			$sqluser = "SELECT www_users.realname
						FROM purchorders
							INNER JOIN www_users ON purchorders.initiator = www_users.userid
						WHERE purchorders.orderno = '".$_GET['OrderNo']."'";
			$resultuser = DB_query($sqluser, $db);
			while($myrowuser = DB_fetch_array($resultuser)){
				$user = $myrowuser['realname'];
			}
			$sqluserauto = "SELECT www_users.realname
							FROM logpurchorderstatus
							INNER JOIN www_users ON www_users.userid = logpurchorderstatus.userid
							WHERE logpurchorderstatus.orderno = '".$_GET['OrderNo']."'
							AND logpurchorderstatus.status = 'Authorised'";
			$resultuserauto = DB_query($sqluserauto, $db);
			while($myrowuserauto = DB_fetch_array($resultuserauto)){
				$userauto = $myrowuserauto['realname'];
			}
			$sqluser2 = "SELECT www_users.realname
						FROM purchorders
							INNER JOIN www_users ON purchorders.autorizausuario = www_users.userid
						WHERE purchorders.orderno = '".$_GET['OrderNo']."'";
			$resultuser2 = DB_query($sqluser2, $db);
			while($myrowuser2 = DB_fetch_array($resultuser2)){
				$userauto2 = $myrowuser2['realname'];
			}
			if(DB_num_rows($resultuserauto) > 0){
				$this->cell(125,6,'Autorizo',0,1,'R');
			}elseif(DB_num_rows($resultuser2) > 0){
				$this->cell(125,6,'Autorizo',0,1,'R');
			}
			$this->SetFillColor(205,205,205);
			$this->SetFont('helvetica','B',10);
			$this->Ln(4);
			$this->cell(25,6,$user,0,0,'L');
			if(DB_num_rows($resultuserauto) > 0){
				$this->cell(135,6,$userauto,0,1,'R');
			}elseif(DB_num_rows($resultuser2) > 0){
				
				$this->cell(135,6,$userauto2,0,1,'R');//
			}
			$this->Ln(4);
			
			
		}
		function Header(){
			global $db;
			
			$sql = "Select terms,shippers.shippername,suppliers.*,purchorders.*
					from purchorders
						inner join suppliers ON purchorders.supplierno = suppliers.supplierid
						inner join paymentterms ON paymentterms.termsindicator = suppliers.paymentterms
						left join shippers ON purchorders.deliveryby = shippers.shipper_id
					where orderno=".$_GET['OrderNo'];
			$res = DB_query($sql,$db);
			$reg = DB_fetch_array($res);
			$paymentterm = $reg[0];
			$suppname = $reg['suppname'];
			$address1 = $reg['address1'];
			$address2 = $reg['address2'];
			$address3 = $reg['address3'];
			$address4 = $reg['address4'];
			$address5 = $reg['address5'];
			$fecha = $reg['orddate'];
			$shipname = $reg['shippername'];
			
			$supplierdata = $suppname.chr(13).chr(10).$address1." ".$address2.chr(13).chr(10).$address3." ".$address4.chr(13).chr(10).$address5.chr(13).chr(10)." ".chr(13).chr(10)." ";
			//
			if (strlen($reg['noag_ad']) == 0 or $reg['noag_ad'] == 0){
				$deliverydata = $reg['deladd6'].chr(13).chr(10).$reg['deladd1'].chr(13).chr(10).$reg['deladd3']." ".$reg['deladd4'].chr(13).chr(10).$reg['deladd5'].chr(13).chr(10)."Contact ".$reg['contact'].chr(13).chr(10)."Phone ".$reg['telephoneContact'] ;
			}else{
				$SqlSupplier = "SELECT suppliers.address1,
										suppliers.address2,
										suppliers.address3,
										suppliers.address4,
										suppliers.address5,
										suppliers.address6
								FROM suppliers
								Where suppliers.supplierid = '".$reg['noag_ad']."'";
				$result = DB_query($SqlSupplier, $db);
				while($myrow = DB_fetch_array($result)){
					$supaddress1 = $myrow['address1'];
					$supaddress2 = $myrow['address2'];
					$supaddress3 = $myrow['address3'];
					$supaddress4 = $myrow['address4'];
					$supaddress5 = $myrow['address5'];
					$supaddress6 = $myrow['address6'];
				}
				$deliverydata = $supaddress1." ".$supaddress2." ".$supaddress3.chr(13).chr(10).$supaddress4.chr(13).chr(10)."C.P. ".$supaddress5.chr(13).chr(10)."Phone ".$supaddress6;
			}
			
			
			
			$sql="select legalbusinessunit.address1 as calle,legalbusinessunit.address2 as colonia,
							legalbusinessunit.address3 as region,legalbusinessunit.address4 as estado,
							legalbusinessunit.address5 as cp , legalbusinessunit.logo, legalbusinessunit.legalname,
							legalbusinessunit.email,legalbusinessunit.comments,
							legalbusinessunit.telephone, legalbusinessunit.fax,legalbusinessunit.taxid,tags.pofootertext,legalbusinessunit.legalurl,
							legalbusinessunit.anchologo, legalbusinessunit.altologo
			from purchorders 
					inner join tags ON purchorders.tagref = tags.tagref
					inner join legalbusinessunit on legalbusinessunit.legalid=tags.legalid
			WHERE purchorders.orderno = ".$_GET['OrderNo'];
			
			$resultlegal=DB_query($sql,$db);
			$myrowlegal=DB_fetch_array($resultlegal);
			
			$posY = $this->GetY()-3;
			$this->Image( './'.$myrowlegal['logo'] ,5,$posY,$myrowlegal['anchologo'],$myrowlegal['altologo']);
			
			$this->SetY($posY);
			//datos de la empresa
			$legalname = $myrowlegal['legalname'];
			
			$this->SetFont('helvetica','B',9);
			$this->cell('',4,$legalname,0,1,'R');
			$this->cell('',4,'RFC. '.$myrowlegal['taxid'],0,1,'R');
			$this->SetTextColor(254,154,46);
			$this->cell('',4,$myrowlegal['legalurl'],0,1,'R');
			$this->SetTextColor(0,0,0);
			$this->SetFont('helvetica','',8);
			$this->cell('',4,$myrowlegal['calle'].' '.$myrowlegal['colonia'],0,1,'R');
			$this->cell('',4,$myrowlegal['region'].', '.$myrowlegal['estado'].', CP. '.$myrowlegal['cp'],0,1,'R');
			$this->cell('',4,'Tel.: '.$myrowlegal['telephone'].' Fax: '.$myrowlegal['fax'],0,1,'R');
			$this->cell('',4,$myrowlegal['comments'],0,1,'R');
			$this->leyendaPiePagina = $myrowlegal['pofootertext'];
			$emailconfig = "";
			if(isset($_SESSION['POPDFMail'])) {
				$emailconfig = $_SESSION['POPDFMail'];
			} else {
				$emailconfig =$myrowlegal['email'];
			}
			$this->cell('',4,$emailconfig,0,1,'R');
			$this->Ln();
			
			$this->SetFillColor(0,0,0);
			$this->cell('',2,'','B',1,'L',1);
			$this->Ln();
			
			$this->cell(50,4,nombremeslargo(substr($fecha,5,2))." ".substr($fecha,8,2).", ".substr($fecha,0,4),0,0,'L');
			$this->SetFont('helvetica','B',12);
			$this->cell('',4,'Orden Compra',0,1,'R');
			$this->SetFont('helvetica','B',10);
			$this->cell(20,5,'Pagina '.$this->PageNo(),0,0,'L');
			$this->cell('',4,$_GET['supplierorderno'],0,1,'R');
			
			$this->SetFont('helvetica','B',8);
			$this->Ln();
			$sqles = "SELECT purchorders.status
					FROM purchorders
					WHERE purchorders.orderno = '".$_GET['OrderNo']."'";
			$resultsales = DB_query($sqles, $db);
			$rowestatus = DB_fetch_array($resultsales);
			if($rowestatus['status'] == "Pending"){
				$this->SetFont('helvetica','B',8);
				$this->cell('',4,'ESTA ORDEN NO ESTA AUTORIZADA, FAVOR DE NO SURTIRLA',0,1,'R');
			}
			// mensaje de que no se encuentra autorizada
			$this->Ln();
			$this->SetFillColor(205,205,205);
			$this->cell(97,5,'Proveedor',1,0,'L',1);
			$this->cell(1,5,'',0,0,'C');
			$this->cell(97,5,'Domicilio Entrega',1,1,'L',1);
			$actY = $this->GetY();
			$actX = $this->GetX();
			$this->SetFont('helvetica','',7);
			$this->MultiCell(97,4,htmlspecialchars_decode(str_replace("&AMP;QUOT;&q","\"",$supplierdata)),1,'L');
			$finalY = $this->GetY();
			$this->SetXY($actX+97,$actY);
			$this->cell(1,5,'',0,0,'C');
			$this->MultiCell(97,4,htmlspecialchars_decode(str_replace("&AMP;QUOT;&q","\"",$deliverydata)),1,'L');
			if ($this->GetY() > $finalY)
				$finalY = $this->GetY();
			
			$this->SetY($finalY);
			$this->Ln(4);
			$this->SetFillColor(254,154,46);
			$this->SetX(30);
			$this->SetFont('helvetica','B',8);
			$this->cell(60,5,'Metodo entrega',1,0,'C',1);
			$this->SetX(130);
			$this->cell(60,5,'Termino pago',1,1,'C',1);
			
			$this->SetX(30);
			$this->SetFont('helvetica','B',7);
			$this->cell(60,5,$shipname,1,0,'C');
			$this->SetX(130);
			$this->cell(60,5,$paymentterm,1,1,'C');
			
			$this->Ln(3);
			$this->SetFillColor(0,0,0);
			$this->cell('',1,'','B',1,'L',1);
			$this->Ln(2);
			
			$this->SetFillColor(205,205,205);
			$this->cell(15,4,'Cantidad',1,0,'C',1);
			$this->cell(40,4,'Producto',1,0,'C',1);
			$this->cell(100,4,'Descripcion',1,0,'C',1);
			$this->cell(20,4,'Precio',1,0,'C',1);
			$this->cell(20,4,'Importe',1,	1,'C',1);
		}
	}

	function WriteHTML($html){
		// HTML parser
			
		$html = str_replace("\n",' ',$html);
		$a = preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
		foreach($a as $i=>$e)
		{
			if($i%2==0)
			{
				// Text
				if($this->HREF)
					$this->PutLink($this->HREF,$e);
				else
					$this->Write(4,$e);
			}
			else
			{
				// Tag
				if($e[0]=='/')
					$this->CloseTag(strtoupper(substr($e,1)));
				else
				{
					// Extract attributes
					$a2 = explode(' ',$e);
					$tag = strtoupper(array_shift($a2));
					$attr = array();
					foreach($a2 as $v)
					{
						if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
							$attr[strtoupper($a3[1])] = $a3[2];
					}
					$this->OpenTag($tag,$attr);
				}
			}
		}
	}
?>