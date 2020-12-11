<?php
	define('FPDF_FONTPATH','../fonts/');
	include_once('fpdf.php');
	include_once('fpdi.php');
	include_once('../Numbers/Words.php');
	 
	class pdfCotizacionTemplate extends FPDI {		
	
		function TextWithDirection($x, $y, $txt, $direction='R'){
			if ($direction=='R')
				$s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',1,0,0,1,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
			elseif ($direction=='L')
			$s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',-1,0,0,-1,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
			elseif ($direction=='U')
			$s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',0,1,-1,0,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
			elseif ($direction=='D')
			$s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',0,-1,1,0,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
			else
				$s=sprintf('BT %.2F %.2F Td (%s) Tj ET',$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
			if ($this->ColorFlag)
				$s='q '.$this->TextColor.' '.$s.' Q';
			$this->_out($s);
		}
		
		function TextWithRotation($x, $y, $txt, $txt_angle, $font_angle=0){
			$font_angle+=90+$txt_angle;
			$txt_angle*=M_PI/180;
			$font_angle*=M_PI/180;
		
			$txt_dx=cos($txt_angle);
			$txt_dy=sin($txt_angle);
			$font_dx=cos($font_angle);
			$font_dy=sin($font_angle);
		
			$s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',$txt_dx,$txt_dy,$font_dx,$font_dy,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
			if ($this->ColorFlag)
				$s='q '.$this->TextColor.' '.$s.' Q';
			$this->_out($s);
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
		
		function OpenTag($tag, $attr){
			// Opening tag
			if($tag=='B' || $tag=='I' || $tag=='U')
				$this->SetStyle($tag,true);
			if($tag=='A')
				$this->HREF = $attr['HREF'];
			if($tag=='BR')
				$this->Ln(5);
		}
		
		function CloseTag($tag){
			// Closing tag
			if($tag=='B' || $tag=='I' || $tag=='U')
				$this->SetStyle($tag,false);
			if($tag=='A')
				$this->HREF = '';
		}
		
		function SetStyle($tag, $enable){
			// Modify style and select corresponding font
			$this->$tag += ($enable ? 1 : -1);
			$style = '';
			foreach(array('B', 'I', 'U') as $s)
			{
				if($this->$s>0)
					$style .= $s;
			}
			$this->SetFont('',$style);
		}
		
		function PutLink($URL, $txt){
			// Put a hyperlink
			$this->SetTextColor(0,0,255);
			$this->SetStyle('U',true);
			$this->Write(4,$txt,$URL);
			$this->SetStyle('U',false);
			$this->SetTextColor(0);
		}
		
		function Header(){
			global $db,$legalname;
		}
		
		function exportPDF($send=0){

			//echo "<br> PRUEBAAAAAA";
			global $db,$legalname ;
			
			$idoc = 0;
			$i=1;			
	
			$this->fpdf('P','mm','Letter');
			$this->AliasNbPages();
			//if ($_GET['tipodocto']==1)
				$this->SetAutoPageBreak(true,20);
			//else
				//$this->SetAutoPageBreak(true,11);
	
			$this->AddPage();
			
			//inicio header
			$logofield = "tags.logotag";
			if ($_GET['tipodocto']==3)
				$logofield = "legalbusinessunit.logo";
			
			
			$sql="select legalbusinessunit.address1 as calle,legalbusinessunit.address2 as colonia,
					legalbusinessunit.address3 as region,legalbusinessunit.address4 as estado,
					legalbusinessunit.address5 as cp , tags.logotag as logo, legalbusinessunit.legalname,
					legalbusinessunit.telephone, legalbusinessunit.fax, concat(tags.address1, ' ', tags.address2, ', ', tags.address3, ', ', tags.address4, ',', tags.address5, ', ', tags.address6) AS sucursal
					from tags inner join legalbusinessunit on legalbusinessunit.legalid=tags.legalid and tagref='".$_GET['Tagref']."'";
			
			if ($_SESSION['UserID']=='admin')
				//echo "<pre>$sql";
			
			$ErrMsg = "Error";
			$resultlegal=DB_query($sql,$db, $ErrMsg);
			$myrowlegal=DB_fetch_array($resultlegal);
			
			// $this->Image("".$myrowlegal['logo'],10,3,55,25);
				
			$this->SetY(2);
			$legalname = $myrowlegal['legalname'];
			
			$this->SetFont('helvetica','B',9);
			$this->cell('',4,$legalname,0,1,'R');
			//$this->cell('',4,'control.sdindustrial.com.mx',0,1,'R');
			$this->SetFont('helvetica','',8);
			$this->cell('',4,$myrowlegal['calle'].' '.$myrowlegal['colonia'],0,1,'R');
			$this->cell('',4,$myrowlegal['region'].', '.$myrowlegal['estado'].', CP. '.$myrowlegal['cp'],0,1,'R');
			$this->cell('',4,'Tel.: '.$myrowlegal['telephone'].' Fax: '.$myrowlegal['fax'],0,1,'R');
			$this->cell('',4,'',0,1,'R');
			$this->cell('',4,'',0,1,'R');
			$this->Ln();
			
			$SQL="select * from salesorders
					inner join custbranch on custbranch.branchcode=salesorders.branchcode
					left join salesman ON salesorders.salesman = salesman.salesmancode
					where orderno=".$_GET['TransNo'];
			$ErrMsg = "Error";
			$resultcliente=DB_query($SQL,$db, $ErrMsg);
			$myrowcliente=DB_fetch_array($resultcliente);
			
			if ($_GET['tipodocto']==2 or $_GET['tipodocto']==4){
				
				$this->SetFont('helvetica','B',10);				
				$this->SetFillColor(205,205,205);
				$this->SetTextColor(255,255,255);
				$this->cell(150,7,utf8_decode('Atención a:'),0,0,'L',1);
				$this->SetFillColor(254,154,46);
				
				if($myrowcliente['quotation'] == 1) { // Si es cotizacion
					$this->cell(45,7,utf8_decode('Atención a:'),0,1,'L',1);
					$this->SetFillColor(254,154,46);
					//$this->cell(45,7,utf8_decode('Cotización: ').$_GET['TransNo'],0,1,'R',1);
				} else {
					$this->cell(122,7,utf8_decode('Atención a:'),0,0,'L',1);
					$this->SetFillColor(254,154,46);
					$this->cell(73,7,utf8_decode('Confirmación de Compra: ').$_GET['TransNo'].' ',0,1,'R',1);
				}
				
				$this->SetTextColor(0,0,0);
			}
			if ($_GET['tipodocto']==3){
				$this->SetFont('helvetica','B',11);
				
				$this->cell(97,7,'',0,0,'R');
				$this->SetFillColor(205,205,205);
				$this->SetTextColor(255,255,255);
				$this->cell(73,7,utf8_decode('Confirmación de Compra: ').$_GET['TransNo'].' ',0,0,'R',1);
				$this->SetFillColor(254,154,46);
				$this->cell(25,7,date("d/m/Y"),0,1,'R',1);
				//$this->cell(25,7,$myrowcliente['orddate'],0,1,'R',1);
				
				$this->SetFillColor(150,150,150);
				$this->SetTextColor(255,255,255);
				$this->cell(97,7,'Vendido a:',0,0,'L',1);
				$this->SetFillColor(150,150,150);
				$this->cell(98,7,'Enviado a:',0,1,'L',1);
				
				$this->SetTextColor(0,0,0);
			}
			
			$sqlstate= "select prospect_status.nombre 
						from prospect_movimientos 
						join prospect_status on prospect_movimientos.idstatus = prospect_status.idstatus 
						where prospect_movimientos.u_movimiento= '".$myrowcliente['idprospect']."'";

			$resultSetstate = DB_query($sqlstate,$db);
			$rowstate = DB_fetch_array($resultSetstate);
				
			$posY = $this->GetY();
			if ($_GET['tipodocto']==2 or $_GET['tipodocto']==4){
				$this->SetFont('helvetica','',10);
				
				$SQL = "Select contactname as contactname 
						from custcontacts where debtorno = '".$myrowcliente['debtorno']."' and contid='".$myrowcliente['contid']."'";
				$rs = DB_query($SQL,$db);
				if (DB_num_rows($rs) > 0){
					$reg = DB_fetch_array($rs);
					$this->cell(120,4,$reg[0],0,1,'L');
					$this->cell(120,4,$myrowcliente['brname'],0,0,'L');
				}
			}
			//$this->MultiCell(98,4,$resultsta['nombre'],0,'L');// //
			if ($_GET['tipodocto']==3){
				$this->SetFont('helvetica','',11);
			
				$dato = $myrowcliente['brname'].chr(13).chr(10);
				$dato.=$myrowcliente['taxid'].chr(13).chr(10);
				$dato.=$myrowcliente['braddress1'].chr(13).chr(10);
				$dato.=$myrowcliente['braddress2'].chr(13).chr(10);
				$dato.=$myrowcliente['braddress3'].chr(13).chr(10);
				$dato.=$myrowcliente['braddress5'].chr(13).chr(10);
				$dato.=$myrowcliente['braddress7'].chr(13).chr(10);
				$dato.= $myrowcliente['braddress4'];
				
				$this->MultiCell(97,4,$dato,0,'L');
				
			}
			//$this->SetXY(75,1);
			//$this->cell(70,6,$rowstate['nombre'],0,0,'C',false);
			
			if ($_GET['tipodocto']==2 or $_GET['tipodocto']==4){
			
				$this->SetXY(120,$posY);
				$vigencia = 30;
				
				$qry = "Select currcode,date_format(date_add(orddate, INTERVAL $vigencia DAY),'%d/%m/%Y') as vigencia,date_format(orddate,'%d/%m/%Y') as orddate,date_format(date_add(orddate,INTERVAL kilometraje DAY),'%d/%m/%Y') as fvalida, salesstatus.statusname 
						FROM salesorders 
						INNER JOIN salesstatus ON salesorders.quotation = salesstatus.statusid
						WHERE orderno = '".$_GET['TransNo']."'";

				$res = DB_query($qry,$db);
				$regs = DB_fetch_array($res);
				
				$this->SetFont('helvetica','',11);
				$this->cell('',4,utf8_decode('Fecha de Expedición: ').$regs['orddate'],0,1,'R');
				$this->SetX(120);
				/*
				$qry = "Select salesmancode,www_users.email
						FROM www_users inner join salesman
								ON www_users.userid=salesman.usersales
							join salesorders on salesorders.salesman=salesman.salesmancode
						WHERE orderno = '".$_GET['TransNo']."'";
				$res = DB_query($qry,$db);
				$regsmail = DB_fetch_array($res);
				
				$vendedormail = 'email '.$regsmail['email'];
				*/
				
				$vendedor = $myrowcliente['salesmanname'];
				$this->SetFont('helvetica','',11);
				$this->cell('',4,'Vendedor: '.$vendedor,0,1,'R');
				
				$this->SetFont('helvetica','',11);

				if (empty($rowstate['nombre'])){
					$this->cell('',4,'Status: '.$regs['statusname'],0,1,'R');	
				} else {
					$this->cell('',4,'Status: '.$rowstate['nombre'],0,1,'R');
				}
				
				//$this->SetFont('helvetica','',11);
				//$this->cell('',4,$vendedormail,0,1,'R');
				
				$this->SetX(120);
					
				$this->SetFont('helvetica','',11);
				$this->cell('',4,'Valida hasta: '.$regs['vigencia'],0,1,'R');
				$this->cell('',4,'Expedido en: '. $myrowlegal['sucursal'], 0, 1, 'R');
			}
			
			if ($_GET['tipodocto']==3){
				
				$this->SetXY(106,$posY);
				
				$this->SetFont('helvetica','',11);
					
				$dato = $myrowcliente['brname'].chr(13).chr(10);
				$dato.=$myrowcliente['brpostaddr1'].chr(13).chr(10);
				$dato.=$myrowcliente['brpostaddr2'].chr(13).chr(10);
				$dato.=$myrowcliente['brpostaddr4'].chr(13).chr(10);
				$dato.=$myrowcliente['brpostaddr3'].chr(13).chr(10);
				$dato.=$myrowcliente['brpostaddr6'].chr(13).chr(10);
				
				$this->MultiCell(90,4,$dato,0,'L');
				$this->Ln(6);
				
				
				$sql="select * from  PDFTemplates where tipodocto=".$_GET['tipodocto']." and Ubicacion='HeaderR' and Orden > 0 Order by Orden";
				$ErrMsg = "Error";
				$resulttext=DB_query($sql,$db, $ErrMsg);
				$this->SetFillColor(220,220,220);
				while ($myrowfooter=DB_fetch_array($resulttext)){
                                    $fill = $myrowfooter['filled'];

                                    $this->SetX($myrowfooter['posX']);

                                    if (strlen($myrowfooter['Titulo'])>0){
                                        $this->SetFont('helvetica','B',9);
                                        $posY = $this->GetY();
                                        $wx = $this->GetStringWidth($myrowfooter['Titulo']);
                                        $this->MultiCell('',4,$myrowfooter['Titulo'],0,'L',$fil);
                                        if ($myrowfooter['consulta']=='' && $myrowfooter['Texto']=='')
                                            ;//$this->Ln();
                                        else{
                                            $this->SetXY($this->GetX() + $wx + 2,$posY);
                                        }
                                    }

                                    $txt = $myrowfooter['Texto'];
                                    if ($myrowfooter['bold']==1 )
                                            $this->SetFont('helvetica','B',8);
                                    else
                                            $this->SetFont('helvetica','',8);

                                    if (strlen($myrowfooter['consulta'])>0){
                                            $SQL=$myrowfooter['consulta'].$_GET['TransNo'];
                                            $ErrMsg = "Error";
                                            $resultcliente=DB_query($SQL,$db, $ErrMsg);
                                            $myrowcliente=DB_fetch_array($resultcliente);
                                            $txt.=" ".$myrowcliente[0];
                                    }
                                    if (strlen($txt)>0){

                                            $this->MultiCell('',4,$txt,0,'J',$fill);

                                    }
						
				}
				
			}
			
			$this->Ln();
			$this->SetFont('helvetica','',8);
				
			$sql = "Select * from PDFTemplates
					where tipodocto = ".$_GET['tipodocto']."
					and Ubicacion = 'Header'
					Order By Orden";
			$rsH = DB_query($sql,$db);
			while ($rows = DB_fetch_array($rsH)){
			
				if (strlen($rows['Titulo'])>0){
					$this->SetFont('helvetica','B',8);
					$posY = $this->GetY();
					$wx = $this->GetStringWidth($rows['Titulo']);
					$this->MultiCell('',4,$rows['Titulo'],0,'L');
					if ($rows['consulta']=='' && $rows['Texto']=='')
						$this->Ln();
					else{
						$this->SetXY($this->GetX() + $wx + 2,$posY);
			
					}
				}
			
				$txt = $rows['Texto'];
			
			
				$this->SetFont('helvetica','',8);
				if (strlen($rows['consulta'])>0){
					$SQL=$rows['consulta'].$_GET['TransNo'];
					$ErrMsg = "Error";
					$resultcliente=DB_query($SQL,$db, $ErrMsg);
					$myrowcliente=DB_fetch_array($resultcliente);
					$txt=$myrowcliente[0];
				}
				if (strlen($txt)>0){
			
					$this->MultiCell('',4,$txt,0,'J');
					$this->Ln();
				}
			
			}
			
			if ($_GET['tipodocto']==3){
				
				//tabla de datos de embarques
				$this->SetFillColor(150,150,150);
				$this->SetTextColor(255,255,255);
				$this->SetFont('helvetica','B',8);
				
				$this->SetX(5);
				$this->cell(20,5,'Estatus',1,0,'C',1);
				$this->cell(55,5,'Vendedor',1,0,'C',1);
				$this->cell(35,5,'OC Clte',1,0,'L',1);
				$this->cell(35,5,'Cond. Pago',1,0,'C',1);
				$this->cell(25,5,'Forma. Entrega',1,0,'C',1);
				$this->cell(20,5,'Guia',1,0,'C',1);
				$this->cell(15,5,'F. Envio',1,1,'C',1);
				$this->SetTextColor(0,0,0);
				$this->SetFont('helvetica','',8);
				
				$sql = "Select paymentterms.terms, realname, nopedido ,shippername
						FROM salesorders 
						left join paymentterms ON salesorders.paytermsindicator = paymentterms.termsindicator
						left join salesman ON salesorders.salesman = salesman.salesmancode
						left join www_users ON salesman.usersales = www_users.userid
						left join shippers ON salesorders.shipvia = shippers.shipper_id
						WHERE salesorders.orderno = ".$_GET['TransNo'];
				$resultSet = DB_query($sql,$db);
				$row = DB_fetch_array($resultSet); 
				$condPago = $row['terms'];
				$vendedor = $row['realname'];
				$formaEntrega = $row['shippername'];
				$OCClte = $row['nopedido'];
				
				$sql = "Select shippingstatusname,trackingnumber, if(shippingdate<>'' and shippingdate is not null,date_format(shippingdate,'%d/m/%Y'),shippingdate) as shipdate 
						FROM shippingorders
						left join shippingstatus ON shippingorders.shippingstatusid = shippingstatus.shippingstatusid
						where shippingorders.orderno = ".$_GET['TransNo'];
				$resSet = DB_query($sql,$db);
				if (DB_num_rows($resSet) > 0){
					while ($row = DB_fetch_array($resSet)){
						$estatus = $row['shippingstatusname'];
						$guia = $row['trackingnumber'];
						$fechaEmb = $row['shipdate'];
						
						$this->SetX(5);
						$this->cell(20,5,$estatus,0,0,'C');
						$this->cell(55,5,$vendedor,0,0,'L');
						$this->cell(35,5,$OCClte,0,0,'L');
						$this->cell(35,5,$condPago,0,0,'L');
						$this->cell(25,5,$formaEntrega,0,0,'L');
						$this->cell(20,5,$guia,0,0,'L');
						$this->cell(15,5,$fechaEmb,0,1,'L');
						
						
					}
				}
				else{
					$this->SetX(5);
					$this->cell(20,5,'',0,0,'C');
					$this->cell(55,5,$vendedor,0,0,'L');
					$this->cell(35,5,$OCClte,0,0,'L');
					$this->cell(35,5,$condPago,0,0,'L');
					$this->cell(25,5,$formaEntrega,0,0,'L');
					$this->cell(20,5,'',0,0,'L');
					$this->cell(15,5,'',0,1,'L');
				}
				
				
			}
			
			$this->Ln(6);
			
			//fin header
			$this->SetFont('helvetica','',10);
			$this->SetFillColor(195,195,195);
			$this->cell(15,5,'Cantidad',1,0,'L',1);
			$this->cell(35,5,'No.Parte',1,0,'L',1);
			$this->cell(95,5,'Producto',1,0,'L',1);
			$this->cell(25,5,'Precio Unitario',1,0,'C',1);
			$this->cell(25,5,'Total',1,0,'C',1);
			$this->SetFont('helvetica','',9);
			
			$sql = "SELECT 	salesorderdetails.unitprice as precio,
							salesorderdetails.stkcode as codigo,
							salesorderdetails.quantity as cantidad,
							-- salesorderdetails.quantitydispatched as cantidad,
							salesorderdetails.discountpercent as descu,
							salesorderdetails.discountpercent1 as desc1,
							salesorderdetails.discountpercent2 as desc2,
							salesorderdetails.narrative,
							'' as infext,			
							if (stockmaster.categoryid in ('CMH','CONT','CVY','CYSP','FBSP','FOODBE','FUR','HSP','LP','MHSP'),stockmaster.longdescription,stockmaster.description) as descripcion,
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
							(salesorderdetails.quantity*salesorderdetails.unitprice)-(((salesorderdetails.quantity*salesorderdetails.unitprice)*(1-salesorderdetails.discountpercent))*(1-salesorderdetails.discountpercent1))*(1-salesorderdetails.discountpercent2) as totaldesc,
							salesorderdetailsgroups.groupname,
							salesorderdetailsgroups.description as groupdescription,
							salesorderdetailsgroups.showcontent,
							salesorderdetailsgroups.required,
							salesorderdetailsgroups.hideprice,
							salesorderdetailsgroups.hidepart,
							salesorderdetails.showdescrip,
							custbranch.taxgroupid,
							custbranch.braddress7,
							stockcategory.cattipodescripcion,
							stockmaster.longdescription,
							stockmaster.mbflag,
							taxauthrates.taxrate*100 as percentiva
						FROM salesorderdetails 
							INNER JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							INNER JOIN salesorders ON salesorders.orderno = salesorderdetails.orderno 
							LEFT JOIN salesman ON salesman.salesmancode = salesorders.salesman
							INNER JOIN debtorsmaster ON debtorsmaster.debtorno = salesorders.debtorno
							INNER JOIN custbranch ON custbranch.branchcode = salesorders.branchcode
												  and custbranch.debtorno = salesorders.debtorno
							INNER JOIN salestypes ON salestypes.typeabbrev = salesorders.ordertype
							LEFT JOIN salesorderdetailsgroups ON salesorderdetails.orderno = salesorderdetailsgroups.orderno 
																 and salesorderdetails.orderlineno = salesorderdetailsgroups.orderlineno
							INNER JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							INNER JOIN taxauthrates ON stockmaster.taxcatid=taxauthrates.taxcatid 
						WHERE salesorderdetails.orderno =".$_GET['TransNo']."
						GROUP BY salesorderdetails.stkcode			
						ORDER BY salesorderdetails.orderlineno";
			/*if($_SESSION['UserID'] == "admin"){
				echo '<pre>'.$sql;
			}*/		
			$ErrMsg = "Error";
			$result=DB_query($sql,$db, $ErrMsg);
				
				if (DB_num_rows($result)>0){
					$myrow=DB_fetch_array($result);
					$result=DB_query($sql,$db);
					$this->Ln();
					
						$totdesc = 0;
						$tot = 0;
						$group = "";
						$groupdesc="";
						$showitems=1;
						$hidepart=0;
						$hideprice=0;
						$totgr = 0;
						$grreq = 1;
						$this->SetFillColor(215,215,215);
						while ($myrow2=DB_fetch_array($result)){

							if ($myrow2['groupname']!="" and $myrow2['groupname']!=$group){
								
								if ($group!="" and $showitems == 1 and $hideprice == 0){
									//poner totales del grupo si se muestran los items
									$this->cell(170,4,'Sub Total ','L',0,'R',1);
									$this->cell(25,4,number_format($grreq*$totgr,2),'R',1,'R',1);
									
								}
								else
									if ($showitems == 1 and $hideprice==1)
										$this->cell(195,0,'','T',1,'C');
								
								$group = $myrow2['groupname'];
								$groupdesc = $myrow2['groupdescription'];  
								$showitems = $myrow2['showcontent'];
								$hideprice = $myrow2['hideprice'];
								$hidepart = $myrow2['hidepart'];
								
								$grreq = $myrow2['required'];
								$totgr = 0;
								
								if ($showitems == 0 || ($showitems == 1 and $hideprice == 1)){
									//calcular total del grupo
									$qry = "SELECT 	SUM(salesorderdetails.unitprice * salesorderdetails.quantity) as totalgr
											FROM salesorderdetails
												INNER JOIN salesorderdetailsgroups ON salesorderdetails.orderno = salesorderdetailsgroups.orderno 
									 					and salesorderdetails.orderlineno = salesorderdetailsgroups.orderlineno
														and salesorderdetails.orderno = ".$_GET['TransNo']."
														and salesorderdetailsgroups.groupname = '$group'			
											";
									$r = DB_query($qry,$db);
									$reg = DB_fetch_array($r);
									$montogroup = $reg[0]/$grreq;						
									
									$this->cell(15,4,$grreq,'L',0,'C');
									$this->cell(35,4,$group,0,0,'L');
									$x=$this->GetX();
									$y=$this->GetY();
									
									$this->MultiCell(95,4,htmlspecialchars_decode(str_replace("&AMP;QUOT;&q","\"",$groupdesc)),0,'L');
									$posYMultiCell = $this->GetY();
									if ($y < $posYMultiCell){
										$yaux = $y+4;
										while ($yaux < $posYMultiCell){
											$this->SetY($yaux);
											$this->cell(195,4,'','LR',1,'L');
											$yaux+=4;
										}
									}
									$this->SetXY($x+95,$y);// para regresar a la posicion sigteh
									
									$this->cell(25,4,number_format($montogroup,2),0,0,'R');
									$this->cell(25,4,number_format($grreq*$montogroup,2),'R',1,'R');
									$this->SetY($posYMultiCell);
									if ($showitems == 1 and $hideprice == 1)
										$this->cell(195,0,'',0,1,'C');
									else 
										$this->cell(195,0,'','T',1,'C');
									
									//$this->Ln(1);
									
								}
								else 
									$this->cell(195,4,$group." - ".$groupdesc ."  (requeridas ".$grreq." pzas)",'LR',1,'L',1);
								
							}
							else 
								if ($myrow2['groupname']=="" and $group!="" ){
								
									if ($showitems == 1 and $hideprice == 0){
										//poner totales del grupo si se muestran los items
										$this->cell(170,4,'Sub Total ','L',0,'R',1);
										$this->cell(25,4,number_format($grreq*$totgr,2),'R',1,'R',1);
									}
									else 
										if ($showitems == 1 and $hideprice==1)
											$this->cell(195,0,'','T',1,'C');
									
									$group = "";
									$showitems = 1;
									$groupdesc ="";
									$hidepart=0;
									$hideprice=0;
									$grreq = 1;
									$totgr = 0;
								}
							
							if ($showitems == 1){
							
								$datos=trim($myrow2['comentarios']);
								$x=$this->GetX();
								$y=$this->GetY();
								//$this->SetFont('helvetica','',8);
							
								if ($hidepart==1){
									$this->cell(15,4,'','L',0,'C');
									$this->cell(35,4,'',0,0,'L');
								}
								else{
									if(($_GET['tipodocto']==4) and ($myrow2['mbflag'] == 'D')){
										$this->cell(15,4,'','L',0,'C');
									}else{
										$this->cell(15,4,abs($myrow2['cantidad']),'L',0,'C');
									}
									
									$this->cell(35,4,$myrow2['codigo'],0,0,'L');
								}
								$x=$this->GetX();
								$y=$this->GetY();
								
								if ($myrow2['showdescrip'] == 1){
									$inftext = $myrow2['narrative'];
									if($myrow2['cattipodescripcion'] == 0){
										$this->MultiCell(95,4,htmlspecialchars_decode(trim($myrow2['descripcion'])).chr(13).chr(10).htmlspecialchars_decode(str_replace("&AMP;QUOT;&q","\"",$inftext)),0,'L');
									}elseif($myrow2['cattipodescripcion'] == 1){
										$this->MultiCell(95,4,htmlspecialchars_decode(trim($myrow2['longdescription'])).chr(13).chr(10).htmlspecialchars_decode(str_replace("&AMP;QUOT;&q","\"",$inftext)),0,'L');
									}
									
									$posYMultiCell = $this->GetY();
									if ($y < $posYMultiCell){								
										$yaux = $y+4;	
										while ($yaux < $posYMultiCell){
											$this->SetY($yaux);
											$this->cell(195,4,'','LR',1,'L');
											$yaux+=4;
										}
									}	
								} else {
									$inftext = $myrow2['narrative'];
									
									$this->MultiCell(95,4,htmlspecialchars_decode(str_replace("&AMP;QUOT;&q","\"",$inftext)),0,'L');
									$posYMultiCell = $this->GetY();
									if ($y < $posYMultiCell){
										$yaux = $y+4;
										while ($yaux < $posYMultiCell){
											$this->SetY($yaux);
											$this->cell(195,4,'','LR',1,'L');
											$yaux+=4;
										}
									}
								}						
								$this->SetXY($x+95,$y);// para regresar a la posicion sigteh
								if ($hideprice==1){
									$this->cell(25,4,'',0,0,'R');
									$this->cell(25,4,'','R',1,'R');
								}
								else{
									$this->cell(25,4,number_format((($myrow2['cantidad']*$myrow2['precio'])-$myrow2['totaldesc'])/$myrow2['cantidad'],2),0,0,'R');
									$this->cell(25,4,number_format(abs(($myrow2['cantidad']*$myrow2['precio'])-$myrow2['totaldesc']),2),'R',1,'R');
								}
								
								$this->SetY($posYMultiCell);
								if ($hidepart==1 || $hideprice==1)
									$this->cell(195,0,'',0,1,'C');
								else	
									$this->cell(195,0,'','T',1,'C');
															
								/*$this->SetLineWidth(.1);
								$this->Cell(150,0,'','T');
								$this->Ln();
								*/
							}
								$itemsCount++;
								
								$tot += abs($myrow2['cantidad']*$myrow2['precio']);
								$totdesc += $myrow2['totaldesc'];
								$totgr += abs($myrow2['cantidad']*$myrow2['precio']/$grreq);
								$iva=$myrow2['iva'];
						
						}		
						$taxgroupid=$myrow['taxgroupid'];
						if ($group!="" and $showitems == 1 and $hideprice==0){
							//poner totales del grupo si se muestran los items
							$this->cell(170,4,'Sub Total ','L',0,'R',1);
							$this->cell(25,4,number_format($totgr,2),'R',1,'R',1);
							
						}
						else
							if ($showitems == 1 and $hideprice==1)
								$this->cell(195,0,'','T',1,'C');
						
						
						$this->Ln(4);
						
						$qry="select taxrate from taxauthrates where taxcatid = $taxgroupid";
						$rstax = DB_query($qry,$db);
						$rowtax = DB_fetch_array($rstax);
						$taxrate = $rowtax[0];
						
						//$iva=$tot-$totdesc;
						//$iva = $iva*$taxrate;
						
						$this->SetFont('helvetica','',10);
						$this->SetFillColor(215,215,215);
						$this->SetX(155);
						$this->cell(25,5,'Sub Total',0,0,'R',1);
						$this->cell(25,5,number_format($tot-$totdesc,2),0,1,'R',1) ;
						$this->SetX(155);
						if ($taxrate > 0){
							$this->cell(25,5,'I.V.A.',0,0,'R',1);
							$this->cell(25,5,number_format($iva,2),0,1,'R',1);
							
						}else{
							$iva=0;
						}
						
						$gtot=$tot-$totdesc+$iva;
						$this->SetX(155);
						$this->SetFont('helvetica','B',10);
						$this->cell(25,5,'Total ('.$myrow["moneda"].')','T',0,'R',1);
						$this->cell(25,5,number_format($gtot,2),'T',1,'R',1);
						$this->SetFont('helvetica','',8);
				}
			
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
                
               /* $this->SetFont('helvetica','B',8);
				$this->cell(30,4,'Cantidad en letras: ',0,0,'L');
				$this->SetFont('helvetica','',8);
				$this->cell('',4,$montoletra,0,1,'L');
				*/

			$this->Ln();
			
            $this->SetFillColor(254,154,46);
			$arrubicacion = array("Footer","Footer1","Footer2","Footer3","Footer4");
			$tope=245;
			foreach ($arrubicacion as $ubicacion){	
				$sqlfooter="select * from  PDFTemplates where tipodocto=".$_GET['tipodocto']." and Ubicacion='$ubicacion' and Orden > 0 Order by Orden";
				$ErrMsg = "Error";
				$resulttextf=DB_query($sqlfooter,$db, $ErrMsg);
				$hayregistros = false;
				if (DB_num_rows($resulttextf) > 0){
					$hayregistros = true;
				}
				
				while ($myrowfooter=DB_fetch_array($resulttextf)){
					$fill = $myrowfooter['filled'];
					if ($this->GetY() >= $tope)
						$this->AddPage();
						
					if (strlen($myrowfooter['Titulo'])>0){
						$this->SetFont('helvetica','B',9);
						$posY = $this->GetY();
						$wx = $this->GetStringWidth($myrowfooter['Titulo']);
						$this->MultiCell('',4,$myrowfooter['Titulo'],0,'L',$fil);
						if ($myrowfooter['consulta']=='' && $myrowfooter['Texto']=='')
							;//$this->Ln();
						else{
							$this->SetXY($this->GetX() + $wx + 2,$posY);	
							
						}
					}
					
					$txt = $myrowfooter['Texto'];
					if ($myrowfooter['bold']==1)
						$this->SetFont('helvetica','B',8);
					else 
						$this->SetFont('helvetica','',8);
						
					if (strlen($myrowfooter['consulta'])>0){
							$SQL=$myrowfooter['consulta'].$_GET['TransNo'];
							$ErrMsg = "Error";
							$resultcliente=DB_query($SQL,$db, $ErrMsg);
							$myrowcliente=DB_fetch_array($resultcliente);	
							$txt.=" ".$myrowcliente[0];
					}
					if (strlen($txt)>0){
						if ($fill==1)
							$this->SetTextColor(255,255,255);
							
						$this->MultiCell('',4,$txt,0,'J',$fill);
						$this->SetTextColor(0,0,0);
						//$this->Ln();
					}
					
				}
				if ($hayregistros)
					$this->Ln(6);
			}
			
			if ($_GET['tipodocto']==2 or $_GET['tipodocto']==4){
				$this->SetFont('helvetica','',9);
				$this->cell(40,6,'No. Orden de Compra','T',0,'C');
				$this->cell(30,6,'',0,0,'C');
				$this->cell(40,6,'Fecha','T',0,'C');
				$this->cell(30,6,'',0,0,'C');
				$this->cell(40,6,'Firma','T',0,'C');
			}	
			
	
			if ($send==1){
				return $this->Output('','S');
			}
			else
				$this->Output('','I');//F
		}
		
		function Footer(){
			global $db;
			
			$this->SetY(-20);
			$posY = $this->GetY();
			$this->SetFont('helvetica','',9);
			
			//$this->Image('images/piedepagina.jpg',10,$posY,195,13);
				
			$this->SetY($this->GetY()+15);
				
			$this->SetTextColor(0,0,0);
			$this->SetFont('helvetica','B',6);
			$this->cell('',5,'Pagina '.$this->PageNo().'/{nb}',0,0,'R');
		}

	}
?>
