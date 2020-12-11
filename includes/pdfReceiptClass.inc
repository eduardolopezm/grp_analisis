<?php



	define('FPDF_FONTPATH','./fonts/');
	include ('fpdf.php');

	class pdfRecibo extends FPDF {
		
		//para numero de paginas por grupos
		
		var $NewPageGroup;   // variable indicating whether a new group was requested
		var $PageGroups;     // variable containing the number of pages of the groups
		var $CurrPageGroup;  // variable containing the alias of the current page group
	
		// create a new page group; call this before calling AddPage()
		function StartPageGroup(){
			$this->NewPageGroup = true;
		}
	
		// current page in the group
		function GroupPageNo(){
			return $this->PageGroups[$this->CurrPageGroup];
		}
	
		// alias of the current page group -- will be replaced by the total number of pages in this group
		function PageGroupAlias(){
			return $this->CurrPageGroup;
		}
	
		function _beginpage($orientation, $format){
			parent::_beginpage($orientation, $format);
			if($this->NewPageGroup)
			{
				// start a new group
				$n = sizeof($this->PageGroups)+1;
				$alias = "{nb$n}";
				$this->PageGroups[$alias] = 1;
				$this->CurrPageGroup = $alias;
				$this->NewPageGroup = false;
			}
			elseif($this->CurrPageGroup)
				$this->PageGroups[$this->CurrPageGroup]++;
		}
	
		function _putpages()
		{
			$nb = $this->page;
			if (!empty($this->PageGroups))
			{
				// do page number replacement
				foreach ($this->PageGroups as $k => $v)
				{
					for ($n = 1; $n <= $nb; $n++)
					{
						$this->pages[$n] = str_replace($k, $v, $this->pages[$n]);
					}
				}
			}
			parent::_putpages();
		}
	
		
		// fin numero de paginas por grupo
		function printPDF($fullpath=""){
			global $db,$myrow,$myrowAddenda,$esGlobal,$cfd;
			$ivaret=0;
			$sql="SELECT SUM(abs(debtortrans.ovamount)) AS importe,
						SUM(abs(debtortrans.ovamount)) AS precio,
						SUM(abs(debtortrans.ovgst)) AS iva,
						debtortrans.currcode as moneda,
						debtortrans.rate as tipocambio,
						debtortrans.order_ as orden,
						date(debtortrans.trandate) as fecha,
						debtortrans.debtorno as cliente,
						debtortrans.ref1 as ref1,
						custbranch.taxid as rfc,
						1 as cantidad,
						debtortrans.transno as codigo,
						debtortrans.reference as descripcion,
						debtortrans.folio as folio,
						debtorsmaster.name as nombre,
						debtorsmaster.address1 as dirfrom1,
						debtorsmaster.address2 as dirfrom2,
						debtorsmaster.address3 as dirfrom3,
						debtorsmaster.address4 as dirfrom4,
						debtorsmaster.address5 as dirfrom5,
						custbranch.braddress1 as dir1,
						custbranch.braddress2 as dir2,
						custbranch.braddress3 as dir3,
						custbranch.braddress4 as dir4,
						custbranch.braddress5 as dir5,
						custbranch.brpostaddr1,
						custbranch.brpostaddr2,
						custbranch.brpostaddr3,
						custbranch.brpostaddr4,
						custbranch.brpostaddr5,
						custbranch.brpostaddr6,
						debtortrans.transno as Trans,
						debtortrans.reference as comentarios,
						ifnull(www_users.userid,'') as userr,
						debtortrans.sello as sello,debtortrans.cadena as cadena ,
						debtortrans.origtrandate as fechafact,
						debtortrans.uuid,
						debtortrans.timbre,
						debtortrans.fechatimbrado,
						debtortrans.cadenatimbre,
						debtortrans.ovamount as montofactura,
				 		debtortrans.ovgst as montoivafact,
						debtortrans.id as iddocto,
						areas.NoCertificado as certificado,
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
						legalbusinessunit.pdffootertext,
						tags.logotag as logo,
						tags.tagref as tagbank,
						legalbusinessunit.cedula,
						legalbusinessunit.comments as referenciaempre,
						tags.address1 AS calleexpe,
						tags.address2 as Noextexpe,
						tags.address3 as coloniaexpe,
						tags.address4 as cdexpe,
						tags.address5 as edoexpe,
						tags.typeinvoice,
						'' as tagemail,
						tags.cp as cpexpe,
						tags.address6 as edo,
						tags.phone as telexpe,
						tags.logotag,
						tags.areacode,
						tags.u_department,
					    upper(systypescat.typename) as typename ,
					custbranch.taxgroupid,
					custbranch.specialinstructions,
					 case when debtortrans.paymentname is null or debtortrans.paymentname='' then 'No Identificado' else debtortrans.paymentname end as paymentname,
					
				 case when debtortrans.nocuenta is null or  debtortrans.nocuenta='' then 'No Identificado' else debtortrans.nocuenta end as nocuenta,
					case when debtortrans.type=105 then 'Anticipo' else 'No Identificado' end as codigo,
					'No Aplica' as um ,
					case when debtortrans.invtext =''  then reference else invtext end as descripcion,
					
				 debtortrans.nopedidof,
				 debtortrans.order_,
					custbranch.phoneno as telcliente,
					 salesman.salesmanname as vendedor,
					www_users.email as mailvendedor,
					paymentterms.terms
				 FROM debtorsmaster
					 LEFT JOIN paymentterms ON paymentterms.termsindicator=debtorsmaster.paymentterms
					,custbranch,
					  debtortrans INNER JOIN tags ON debtortrans.tagref=tags.tagref
					 LEFT JOIN salesman ON salesman.salesmancode = debtortrans.cobrador
					  Left join www_users on www_users.userid=salesman.usersales
						INNER JOIN areas ON tags.areacode=areas.areacode
						INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
				     		
					inner join systypescat on systypescat.typeid=debtortrans.type
					
				     WHERE debtortrans.type=".$_GET['Type']." AND debtortrans.transno=".$_GET['TransNo']."
						AND debtortrans.debtorno=debtorsmaster.debtorno
						AND debtortrans.debtorno=custbranch.debtorno
						AND debtortrans.branchcode=custbranch.branchcode
						AND debtortrans.transno=debtortrans.transno
						AND debtortrans.type=".$_GET['Type']."
				    GROUP BY debtortrans.currcode, debtortrans.rate, debtortrans.order_,
						debtortrans.debtorno, custbranch.taxid, debtorsmaster.name,
						debtorsmaster.address1, debtorsmaster.address2, debtorsmaster.address3,
						debtorsmaster.address4, debtorsmaster.address5, debtortrans.branchcode,
						custbranch.braddress1, custbranch.braddress2, custbranch.braddress3,
						custbranch.braddress4,custbranch.braddress5 ";
			$result = DB_query($sql,$db);
			if (DB_num_rows($result)>0){			
				$myrow=DB_fetch_array($result);
				$this->fpdf('P','mm','Letter');			
				$this->SetAutoPageBreak(true,10);
				$this->StartPageGroup();
				$this->AddPage();		
				$original = "ORIGINAL";
				$legalname = $myrow['legalname'];
				
				for($i=1;$i<=2;$i++){				
					if ($i==2){
						$this->StartPageGroup();			
						$this->AddPage();
					}
					//valores del detalle
					$res = DB_query($sql,$db);
					$totitems = DB_num_rows($res);
					$itemsCount = 0;
					$newPage=false;
					$this->SetFont('helvetica','',6);
					$linenum=0;
					
					$group = "";
					$groupdesc="";
					$showitems=1;
					$totgr = 0;
					$this->SetFillColor(215,215,215);
					$ind=0;
					while ($myrow2=DB_fetch_array($res)){	
						$taxgroupid=$myrow['taxgroupid'];
						$stockcantidad=1;
						
						$sqlfactura="select  case when instr(d2.folio,'|')>0 then d2.folio else concat(d2.type,'-',d2.transno) end as folio,
		                 concat(d2.invtext, ' (Complementario al Folio: ', replace(IFNULL(d2.folio,''),'|','-'), ' con fecha: ') as descripcion,
		                 Date_format(d2.origtrandate, '%d-%c,%Y') as fecha,
		                 custallocns.amt as importe1,
		                 d2.transno as codigo,
		                 d2.type as tipo,
		                 d2.order_,
						 d2.id,
		                 custallocns.amt / (1+ ((d2.ovgst*1)/d2.ovamount)) as importe ,
		                 custallocns.amt - (custallocns.amt / (1+ ((d2.ovgst*1)/d2.ovamount))) as iva,
						 d2.invtext as invtextfact,
						 replace(IFNULL(d2.folio,''),'|','-') as foliofact,
						 d2.ovamount as totalfact,
						 d2.ovgst as ivafact
						 from debtortrans inner join custallocns on custallocns.transid_allocfrom=debtortrans.id
		                 inner join debtortrans d2 ON custallocns.transid_allocto  = d2.id
						 where   custallocns.transid_allocfrom=".$myrow2['iddocto'];
						
						$rsfact = DB_query($sqlfactura, $db);
						
						while($myrowfact = DB_fetch_array($rsfact)) { 
							
							$npagosSql = "select count(*) as npagos from custallocns where transid_allocto = '{$myrowfact['id']}'";
							$npagosRs = DB_query($npagosSql, $db);
							$npagos = DB_fetch_array($npagosRs);
							
							$cod = $myrowfact['codigo'];
							$pagotxt = "Pago numero: " . $npagos['npagos'];
							$this->cell(35,3,$cod,'L',0,'C');
							$um = $myrow2['um'];
							$descripcion = "";
							if($myrowfact['invtextfact'] <> ""){
								$descripcion = $myrowfact['invtextfact'];
							}
							if($myrowfact['foliofact'] <> ""){
								$descripcion = $descripcion.' Factura: '.$myrowfact['foliofact'];
							}
							$TotalFactura = $myrowfact['totalfact'] + $myrowfact['ivafact'];
							$descripcion = $descripcion.' con fecha: '.$myrowfact['fecha'].' Importe '.$TotalFactura.' ';
							
							$this->cell(75,3,$descripcion. $pagotxt,0,0,'L');
							$this->cell(50,3,$um,0,0,'C');
							
							$this->cell(20,3,'$'.number_format($myrowfact['importe'],2),0,0,'L');
							$this->cell(16,3,'$'.number_format($myrowfact['importe'],2),'R',1,'R');
							//$this->cell(30.8,4,'Total','TBR',1,'R',1);
							$ind++;
						}
						
					}
					
					
					$this->cell('',0,'',1,1,'L');
					
					if($_GET['Type'] == 12){
						$sql3 = "SELECT abs(ovamount) as subtotal,
										abs(ovamount)+abs(ovgst) as total,
										abs(ovgst) as iva,
										currcode 
								FROM debtortransmovs 
								where type=".$_GET['Type']." and transno=" .$_GET['TransNo'];
						$result3 = DB_query($sql3, $db);
						$myrow3 = DB_fetch_array($result3);
						$iva = $myrow3['iva'];
						$subt = $myrow3['subtotal'];
					}
					
					//footer de la pagina que solo se pone en la ultima pagina por eso no se crea el metodo Footer.
					$sql2="SELECT abs(ovamount) as subtotal,
								abs(ovamount)+abs(ovgst) as total,
								abs(ovgst) as iva,
								currcode ,cadena
							FROM debtortrans where type=".$_GET['Type']." and transno=" .$_GET['TransNo'];		
					$rs = DB_query($sql2,$db);
					$myrow2 = DB_fetch_array($rs);
					
					
					if($_GET['Type'] <> 12){	
						$iva = $myrow2['iva'];
						$subt = $myrow2['subtotal'];
						
					}
					
					if (strpos($myrow2['cadena'],'XAXX010101000')==true){
						//$stockprecio = number_format($myrow2['priceunit']+($myrow2['priceunit']*$myrow2['poriva']),2);
						if ($taxgroupid<>1){
							if($_GET['Type'] == 12){
								$iva = 0;
							}else{
								$myrow2['iva']=0;
							}
							
							
						}
						
						if($_GET['Type'] == 12){
							$subt = $subt+$iva;
						}else{
							$subt = $myrow2['subtotal']+$myrow2['iva'];
						}
						
						$iva = 0;
						
					
					}else{
						
						
						
						if($_GET['Type'] <> 12){
							$iva = $myrow2['iva'];
							$subt = $myrow2['subtotal'];
						}		
						//echo '<br> tax'.$taxgroupid;
						if ($taxgroupid<>1){
							if($_GET['Type'] == 12){
								$iva = 0;
								
							}else{
								$myrow2['iva']=0;
							}
							
						}
						
					}
					$tot = $myrow2['total'];
					//echo 'iva'.$iva;
					$porciva=round($iva*100)/$subt;
					//echo '<br> poriva'.$porciva;
					if ($ivaret > 0)
						$tot -= $iva; 
					else{
						//retenciones de % al millar
						$SQL = "select amount,nametax
								from debtortranstaxesclient 
									inner join sec_taxes ON debtortranstaxesclient.idtax = sec_taxes.idtax 
								where iddoc = '".$myrow['iddocto']."'"; 
						$rret = DB_query($SQL,$db);
						$impret = 0;
						$porcimpret = 0;
						if (DB_num_rows($rret)>0){
							$reg = DB_fetch_array($rret);
							$impret = $reg['amount']*(-1);
							$tot -= $impret;
							$porcimpret = $reg['nametax'];
						}		
					}
					
					$totaletras = $tot;
					$separa=explode(".",$totaletras);
					$montoctvs2 = $separa[1];
					$montoctvs1 = $separa[0];
					//$montoctvs1x= $separa[1];
					if (left($montoctvs2,3)>=995){
							$montoctvs1=$montoctvs1+1;
							//$montoctvs1x='entra';
					}
					
					$montoletra=Numbers_Words::toWords($montoctvs1,'es');
					
					
	
	
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
								
								$montoletra=ucwords($montoletra) . " Dolares ". $montoctvs2 ."/100 USD";
							}
					}
	
					//cantidad en letras
					$this->Ln(2);
					$posY = $this->GetY();
					$this->SetFont('helvetica','B',7);
					$this->cell(170,4,'Cantidad en letras','LTR',1,'L');
					$this->SetFont('helvetica','',7);
					$this->cell(170,3,'('.$montoletra.')','LBR',1,'L');
					$posY2 = $this->GetY();
					//subtotal,iva y total
					$this->SetXY(180,$posY);
					$this->SetFont('helvetica','B',8);
					$this->SetFillColor(205,205,205);
					$this->cell(26,4,'SubTotal','LTR',1,'L',1);
					$this->SetFont('helvetica','',7);
					$this->SetX(180);
					$this->cell(26,4,number_format($subt,2),'LR',1,'R',1);
					
					
					if ($porciva > 0){
					
						//if($myrowAddenda['add_TAXTaxPercentage']==0){
							$porciva = number_format($porciva)."%";
						//}else{
							//$porciva = number_format($myrowAddenda['add_TAXTaxPercentage'],2)."%";
						//}	
					}

					if ($ivaret > 0 || $impret > 0){
						$this->SetX(180);					
						$this->cell(26,2,'','LR',1,'L',1);
					}
					else{
						$this->SetX(180);					
						$this->cell(26,4,'','LR',1,'L',1);
					}
					
					if ($porciva > 0){
						$this->SetX(180);
						$this->SetFont('helvetica','B',8);
						if ($taxgroupid==1){
							$this->cell(26,3,'Impuesto IVA ','LR',1,'L',1);
							$this->SetFont('helvetica','',6);
							$this->SetX(180);
							$this->cell(26,3,$porciva,'LR',1,'L',1);
							$this->SetX(180);
							$this->SetFont('helvetica','',7);
							$this->cell(26,3,number_format($iva,2),'LR',1,'R',1);
						}
					}
					else{
						$this->SetX(180);
						$this->cell(26,11,'','LR',1,'L',1);
					}

					if ($ivaret > 0 || $impret > 0){
						$tipo = utf8_decode('Retención IVA');
						$valor = number_format($iva,2);
						if ($impret > 0){
							$tipo = utf8_decode('Retención ');
							$valor = number_format($impret,2);
						}
						$this->SetX(180);
						$this->SetFont('helvetica','B',8);
						$this->cell(26,3,$tipo,'LR',1,'L',1);
						$this->SetFont('helvetica','',7);
						$this->SetX(180);
						$this->cell(26,4,$porcimpret."    ".$valor,'LR',1,'R',1);
					}
					else{
						$this->SetX(180);
						$this->cell(26,4,'','LR',1,'L',1);
					}
					$anchoy=2;
					if ($ivaret > 0)
						$anchoy=3;
					
					$this->SetX(180);
					$this->SetFont('helvetica','B',8);
					$this->cell(26,$anchoy,'TOTAL    '. number_format($tot,2) ,'LBR',1,'L',1);
					$this->SetFont('helvetica','',7);
					//$this->SetX(180);						
					//$this->cell(26,0,number_format($tot,2),'LBR',1,'R');
					//$this->Ln(3);
					
					//informacion comercial
					$this->SetY($posY2);
					$this->SetFont('helvetica','B',7);
					
					
					if($myrow['rfccliente']=='CLD0507145H6' or $myrow['rfccliente']=='LAC811016CFA' ){
						$this->cell(85,4,utf8_decode('Información Comercial'),'L',0,'L');
						$this->cell(40,3,utf8_decode('Banco o Institución Financiera:'),0,0,'L');
						$this->SetFont('helvetica','',6);
						$this->cell(25,3,('BBVA BANCOMER S.A.'),0,1,'L');
						
					}else{
						$this->cell(170,4,utf8_decode('Información Comercial'),'LR',1,'L');
						
					}
					
					$this->SetFont('helvetica','',5);
					$posY = $this->GetY();
					//$this->cell(170,3,utf8_decode('La tenencia de esta factura no acredita su pago si no se justifica con el comprobante respectivo'),'LR',1,'L');
					$this->SetFont('helvetica','B',7);
					
					$this->cell(20,3,'Metodo de Pago:','L',0,'L');
					$this->SetFont('helvetica','',6);
					$this->cell(45,3,utf8_decode($myrow['paymentname']),0,0,'L');
					$this->SetFont('helvetica','B',7);
					$this->cell(11,3,utf8_decode('Moneda: '),0,0,'L');
					$this->SetFont('helvetica','',6);
					$this->cell(6,3,$myrow['moneda'],0,1,'L');
					
					
					
					//$this->SetFont('helvetica','B',7);
					//$this->cell(20,3,utf8_decode('Método de Pago:'),'L',0,'L');
					//$this->SetFont('helvetica','',6);
					//$this->cell(51,3,utf8_decode($myrowAddenda['com_metodoDePago']),0,1,'L');
					$this->SetFont('helvetica','B',7);
					$this->cell(20,3,utf8_decode('No Cuenta Pago:'),'L',0,'L');
					$this->SetFont('helvetica','',6);
					$this->cell(45,3,utf8_decode($myrow['nocuenta']),0,0,'L');

					$this->SetFont('helvetica','B',7);
					$this->cell(5,3,utf8_decode('TC: '),0,0,'L');
					$this->SetFont('helvetica','',6);
					$this->cell(10,3,number_format((1/$myrow['tipocambio']),4),0,1,'L');


					$this->SetFont('helvetica','B',7);
					$this->cell(25,3,utf8_decode('Condiciones Pago:'),'L',0,'L');
					$this->SetFont('helvetica','',6);
					$this->cell(40,3,$myrow['terms'],0,1,'L');
					$this->SetFont('helvetica','B',7);
					
					
					$Comprobante=TraeDatosCFD($cfd,'Comprobante');
					$FormaPago = $Comprobante['formaDePago'];
					$this->SetFont('helvetica','B',7);
					$this->cell(25,3,utf8_decode('Forma de pago:'),'L',0,'L');
					$this->SetFont('helvetica','',6);
					$this->cell(40,3,$FormaPago,0,1,'L');
					$this->SetFont('helvetica','B',7);
					
					$posY2 = $this->GetY();
					
					$this->cell(16,3,' ','L',1,'L');
					if ($myrow['tagbank']!=13){
						$this->SetFont('helvetica','',6);
						$textonapa=" ";//_('* Esta Condición aplica solo para productos NAPA remanufacturados que sean devueltos en la caja NAPA al producto en cuestión y que no esten fisurados, rotos o incompletos.');
						$this->MultiCell(100,6,utf8_decode($textonapa),'L','L');
					}
						
					$fechapagoinicial=ConvertSQLDate($myrow['trandatefact']);
					list($dia,$mes,$anio) = explode('/', $fechapagoinicial);
					if($_GET['Type']!=119){
						$textofactura=utf8_decode('Debo(emos) y pagaré(emos) incondicionalmente en la Ciudad de ').$myrow['cdexpe'] . ', ' . $myrow['edoexpe'] .' en la fecha '."$dia/$mes/$anio" .
						' a la orden de '.$myrow['legalname'] .
						'por la cantidad de '. $totaletras .' '.$myrow['currcode'].' ('.$montoletra. utf8_decode('), valor de la mercancía que recibi(mos) a mi(nuestra) entera satisfacción.').
						utf8_decode('Pagaré sustentado por Cap. III de la Ley General de Títulos y Operaciones de Crédito.');
						$firma='Firma de Conformidad del Cliente';
					}else{
						$textofactura='';
						$firma='Firma de Recibido Cliente';
					
					}
						
						
						
					$this->SetFont('helvetica','',5);
					$posX = $this->GetX()+85;
					//$posY2 = $this->GetY()-3;
					$this->SetXY($posX,($posY-3));
					$this->MultiCell(75,3,$textofactura,0,'L');
						
					$this->SetXY($posX,$posY2);
					$this->Ln(6);
						
					$this->SetX($posX+20);
					$this->Cell(40,3,$firma,'T',1,'C');
						
					$this->Cell(0,3,'','T',1,'TBR');
						
					//$posY2 = $this->GetY();
					$comentarios=str_replace('\R\N', chr(13).chr(10), $myrow['invtext']);
					
					/*$this->SetFont('helvetica','',5);
					 $posX = $this->GetX();
					$posY2 = $this->GetY();
					$this->SetXY($posX,$posY2);
					$this->MultiCell(0,3,$comentarios,0,'LBR');
					$posY2 = $this->GetY();
					$posX = $this->GetX();*/
					$posX = $this->GetX();
					$posY = $this->GetY();
					
					$posY2 = $this->GetY()+3;
					$this->SetXY($posX,$posY2);
						
					$this->SetFont('helvetica','B',7);
					$this->cell('',3,'Comentarios','LTR',1,'L');
					$this->SetFont('helvetica','',6);
					$this->MultiCell('',3,$comentarios,'LR','L');
					
					$posX = $this->GetX();
					$posY = $this->GetY();
					$this->SetXY($posX,$posY);
											
				
					//sello
					if($_GET['Type']==119){
						$this->cell('',2,'','BR',1,'L');
					
					}else{
						if ($myrow['typeinvoice']==4){
							$this->SetFont('helvetica','B',7);
							$this->cell('',3,'Sello Digital del emisor','LTR',1,'L');
							$this->SetFont('helvetica','',6);
							$this->MultiCell('',3,$myrow['sello'],'LBR','L');
							// sello del sat
							$this->SetFont('helvetica','B',7);
							$this->cell('',3,'Sello Digital del SAT','LTR',1,'L');
							$this->SetFont('helvetica','',6);
							$this->MultiCell('',3,$myrow['timbre'],'LBR','L');
							//cadena original
								
								
							$this->SetFont('helvetica','B',7);
							$this->cell('',3,utf8_decode('Cadena Original del Complemento de Certificación SAT'),'LTR',1,'L');
							$this->SetFont('helvetica','',5);
							$this->MultiCell('',3,htmlspecialchars_decode(utf8_decode($myrow['cadenatimbre'])),'LBR','L');
							//original o copia
							$this->SetFont('helvetica','B',8);
							$this->cell(140,4,'ESTE DOCUMENTO ES UNA REPRESENTACION IMPRESA DE UN CFDI', 0, 0, 'C');
						} else {
							$this->SetFont('helvetica','B',7);
							$this->cell('',3,'Sello Digital','LTR',1,'L');
							$this->SetFont('helvetica','',6);
							$this->MultiCell('',3,$myrow['sello'],'LBR','L');
							//cadena original
							$this->SetFont('helvetica','B',7);
							$this->cell('',3,'Cadena Original','LTR',1,'L');
							$this->SetFont('helvetica','',5);
							$this->MultiCell('',3,htmlspecialchars_decode(utf8_decode($myrow['cadena'])),'LBR','L');
							//original o copia
							$this->SetFont('helvetica','B',8);	
							$this->cell(140,4,'ESTE DOCUMENTO ES UNA REPRESENTACION IMPRESA DE UN CFD', 0, 0, 'C');
						}
					}
					$this->SetFont('helvetica','',8);					
					$this->cell('',4,'Pagina '.$this->GroupPageNo().'/'.$this->PageGroupAlias().'   '.$original, 0, 1, 'R');

					$this->SetFont('helvetica','B',7);					
					$this->cell(140,3,$myrow['pdffootertext'], 0, 0, 'C');
					
					$original = "COPIA";	
					
				}//for
				
				if (trim($fullpath)!=""){
					$this->Output($fullpath, 'F');//F
				}
				else{
					$this->OutPut('','I');		
					die();
				}

			}//if si hay registros
		}
		

		function Header(){
			  global $db,$myrow,$myrowAddenda,$esGlobal,$cfd;
			  
			  $legalname = $myrow['legalname'];
			  $esGlobal=true;
			  if (strpos($legalname,"Global")===false)
					$esGlobal=false;
			  
			  $posY = $this->GetY()-3;			  
			  $this->Image( './'.$myrow['logo'] ,5,$posY,45,18);

			  //emisor				
			  $xIni = 70;
			  $this->SetFont('helvetica','B',8);				
			  $this->SetXY($xIni,$posY);
			  $this->cell($xIni,3,$myrow['legalname'],0,1,'L');
			  $this->SetFont('helvetica','',7);				
			  $this->SetX($xIni);
			  $this->cell(15,3,'RFC: '.$myrow['rfcempre'],0,1,'L');
			  $this->SetX($xIni);
			  $this->cell($xIni,3,$myrow['calleempre'],0,1,'L');
			  $this->SetX($xIni);
			  $this->cell($xIni,3, $myrow['coloniaempre'],0,1,'L');
			  $this->SetX($xIni);
			  $this->cell($xIni,3, $myrow['Noextempre']. ' ' . $myrow['cdempre'],0,1,'L');
			  $this->SetX($xIni);
			  $this->cell(25,3,'Tel: ' . $myrow['phoneempre'] . ' ' . 'Fax: ' . $myrow['faxempre'],0,1,'L');
			  $this->SetX($xIni);				
			  //$this->cell(25,3, 'Email: ' . $myrow['emailempre'],0,1,'L');
			  $this->SetX($xIni);				
			  $this->cell(25,3, utf8_decode('Régimen: ' . utf8_encode($myrow['regimenfiscal'])),0,1,'L');

			
			  //datos documento
			  //echo $myrow['folio'];
			  //var_dump($myrow['folio']);
			  //echo '<br>get'.$_GET['Type'];
			  
			  $foliox=explode('|',$myrow['folio']);
			  
			  if($_GET['Type'] == 12){
			  	$serie=$foliox[1];
			  	$folio=$foliox[0];//
			  }else{
			  	$serie=$foliox[0];
			  	$folio=$foliox[1];
			  }
			 //echo '<br>serie'.$serie;
			 //echo '<br>folio'.$folio;
			 //exit;
			  $folioX=$foliox[1];
			  $sqlfact = "Select debtortrans.uuid
							from debtortrans
							where type = '".$_GET['Type']."'
							and transno = '".$_GET['TransNo']."'";
			  
			 // echo $sqlfact;
			  $resultfact = DB_query($sqlfact, $db);
			  $row = DB_fetch_array($resultfact);
			  if($row['uuid'] <> ""){
			  	$tipodoc = "Recibo Parcial";
			  }else{
			  	$tipodoc = $myrow['typename'];
			  }
			  
			  
			  $Certificado='';
			  $Noaprobacion='';
			  $anioAprobacion='';
			  if(empty($folio) == false) {
			  	$aprobaxfolio=TraeAprobacionxFolio($myrow['rfcempre'],$serie,$folio,$db,$myrow['fechafact']);
			  	$aprobacionfolios=explode('|',$aprobaxfolio);
			  	$Certificado=$aprobacionfolios[0];
			  	$Noaprobacion=$aprobacionfolios[1];
			  	$anioAprobacion=$aprobacionfolios[2];
			  }
			 
			  $condicion = $myrowConVen['type'];
			  $this->SetXY(155,$posY);
			  $this->SetFont('helvetica','B',8);
			  $this->SetFillColor(254,154,46);
			  $this->cell(50,4,'Tipo: '.$tipodoc,'LTR',1,'C');
			  $this->SetX(155);
			  if(strlen($serie)==0){
			  	$serfol=$folio;
			  }else{
			  	$serfol=$serie.'-'.$folio;
			  	$serfolarchivo = $serie.$folio;
			  }
			  if ($myrow['typeinvoice']==4){
			  	$this->cell(50,4,_('Folio Fiscal (UUID): '),'LTR',1,'C',1);
			  	$this->SetX(155);
			  	$this->SetFont('helvetica','B',6);
			  	$this->cell(50,4,$myrow['uuid'],'LTR',1,'C',1);
			  
			  	$this->SetX(155);
			  	$this->SetFont('helvetica','',7);
			  	$this->cell(50,4,'SERIE/FOLIO Interno: '.$serfol,'LTR',1,'L');
			  	$this->SetX(155);
			  	$this->SetFont('helvetica','B',7);
			  	$this->cell(50,4,utf8_decode('Fecha Certificación: ').str_replace(' ','T',$myrow['fechatimbrado']),'LTR',1,'L');
			  			  		
			  }else{
			  	$this->cell(50,4,'SERIE/FOLIO: '.$serfol,'LTR',1,'C',1);
			  }

			  $this->SetFont('helvetica','',7); 
			  $this->SetX(155);
			  $this->cell(50,4,utf8_decode('Fecha Emisión: ').str_replace(' ','T',$myrow['fechafact']),'LTR',1,'L');
				
			  $this->SetX(155);
			  $this->SetX(155);
			  
			  
			  $posYfolio = $this->GetY();
			  $this->Ln(6);
			  //datos del cliente
			  $posY = $this->GetY();
			  $this->SetFont('helvetica','B',7);
			  $this->cell(50,4,'Datos del Cliente',0,1,'L');
			  
			  $tipofac = $_GET['Type'];
			  if ($tipofac=='12'){
			  	$folder="Recibo";
			  }elseif($tipofac=='10' or $tipofac=='110'){
			  	$folder="Facturas";
			  }elseif($tipofac=='13'){
			  	$folder="NCreditoDirect";
			  }elseif($tipofac=='21' or $tipofac=='105'){
			  	$folder="NCargo";
			  }else{
			  	$folder="NCredito";
			  }
			  	
			  $carpeta = $folder;
			 
			  $dir="/var/www/html/".dirname($_SERVER['PHP_SELF'])."/companies/".$_SESSION['DatabaseName']."/SAT/".str_replace(',','',str_replace('.','',str_replace(' ','',$legalname)))."/XML/".$carpeta."/";
			  $nufa = $serfolarchivo;
			  $mitxt=$dir.$nufa.".xml";
			  $cfd=file_get_contents($mitxt);
			  
			  if (strpos($myrow['cadena'],'XAXX010101000')==true){
			  	  $this->SetFont('helvetica','',7);
				  $this->cell(71,4,$myrow['cliente'].' - VENTAS MOSTRADOR','LTR',1,'L');
				  $this->cell(71,4,'RFC: XAXX010101000','LR',1,'L');
				 
				  if($myrow['preferential']==1){
					$DomicilioCFD=TraeDatosCFD($cfd,'Domicilio');
					$domiciliofiscal=$myrow['cliente'].' '.$myrowAddenda['re_nombre'];//$myrow['brname'];
					$actY = $this->GetY();
					$this->MultiCell(71,4,$domiciliofiscal,'LR','L');
					$espacio = 4;
				  	if ($actY < ($this->GetY()-$espacio))
				  		$espacio = 0;
					
					$domiciliofiscal1=$myrowAddenda['re_calle'].' '.$myrowAddenda['re_noExterior'];
					$domiciliofiscal2=$myrowAddenda['re_municipio'].' , '.$myrowAddenda['re_estado'];
					$this->cell(71,4,$domiciliofiscal1,'LR',1,'L');
					$this->cell(71,4,$domiciliofiscal2,'LR',1,'L');
					$this->cell(71,$espacio,'','LBR',1,'L');
				  }
				  else
				  	$this->cell(71,16,'','LBR',1,'L');//equivale a 4 lineas de 4 de ancho
			  }
			  else{
			  	  $DatosCFD = TraeDatosCFD($cfd,'Receptor');
				 //var_dump(htmlentities($cfd));//
				 //exit; 
				  $this->SetFont('helvetica','',7);
				  $actY = $this->GetY();
				  $this->MultiCell(71,4,utf8_decode($myrow['cliente'].' - '.htmlspecialchars_decode($DatosCFD['nombre'])),'LTR','L');
				  $espacio = 4;
				  if ($actY < ($this->GetY()-$espacio))
				  	$espacio = 0;
				  
				  
				  $this->cell(71,4,'RFC: '.$DatosCFD['rfc'],'LR',1,'L');
				  
				  $DomicilioCFD=TraeDatosCFD($cfd,'Domicilio');
				  if(count($DomicilioCFD)==0){
				  	$DomicilioCFD['calle']=$myrow['calle'];
				  	$DomicilioCFD['codigoPostal']=$myrow['colonia'];
				  	$DomicilioCFD['colonia']=$myrow['municipio'];
				  	$DomicilioCFD['estado']=$myrow['estado'];
				  }
				  
				  $this->cell(71,4,utf8_decode($DomicilioCFD['calle'].''.$DomicilioCFD['noExterior'].' '.$DomicilioCFD['noInterior']),'LR',1,'L');
				  $actY = $this->GetY();
				  $this->MultiCell(71,4,utf8_decode($DomicilioCFD['colonia'].', '.$DomicilioCFD['localidad'].', '.$DomicilioCFD['estado'] .' CP ' .$DomicilioCFD['codigoPostal'] ),'LR','L');
				  $this->cell(71,4,_('Tel ').$myrow['telcliente'],'LR',1,'L');
				  
				  $espacio = 4;
				  if ($actY < ($this->GetY()-$espacio))
				  	$espacio = 0;
					
				  $this->cell(71,4,utf8_decode($DomicilioCFD['pais']),'LR',1,'L');
				  $this->cell(71,$espacio,'','LBR',1,'L');
				  $posYHeader = $this->GetY();
			  }
			  
			  
			  $posX = $this->GetX()+72;
			  $this->SetXY($posX,$posY);	
			  //datos de entrega
			  $this->SetFont('helvetica','B',7);
			  $this->MultiCell(72,4,'Datos de Entrega',0,'L');
			  //$this->Cell(8,4,$myrow['dirfrom1'],'LR',1,'L');
			  $this->SetX($posX);	
			  $this->SetFont('helvetica','',7);
			  $actY = $this->GetY();
			  $this->MultiCell(72,4,$myrow['specialinstructions'],'LTR','L');
			  $espacio=4;
			  if ($actY < ($this->GetY()-$espacio))
			  	$espacio=0;
				
			  $this->SetX($posX);	
			  $actY = $this->GetY();
			 // $this->MultiCell(72,4,$myrowAddenda['ent_calle'],'LR','L');
			  $espacio=4;
			  if ($actY < ($this->GetY()-$espacio))
			  	$espacio=0;
			 
			  $this->SetX($posX);	
			  $this->MultiCell(72,4,$myrow['brpostaddr1'].' '.$myrow['brpostaddr2'].' '.$myrow['brpostaddr3'],'LR',1,'L');
			  
			  
			  $this->SetX($posX);	
			  if ($espacio==4)
			  	$this->MultiCell(72,4,$myrow['brpostaddr4'].' '.$myrow['brpostaddr5'].' '.$myrow['brpostaddr6'],'LR',1,'L');
			  else
			  	$this->MultiCell(72,0,'','LR',0,'L');
			  
			  
			  $this->SetX($posX);	
			  $datosVendedor = 'Cobrador: '.$myrow['vendedor'];
			  //agregar datos de vehiculo
			 
			
			  $datosVendedor =$datosVendedor.chr(13).chr(10). ' Email: '.$myrow['mailvendedor'];
			  $this->MultiCell(72,4,$datosVendedor,'LR','L');
			  $this->SetX($posX);
			  $this->cell(72,0,'','B',1,'L');	
			  
			  $this->Ln(4);
			  
			  if ($this->GetY() > $posYHeader)
			  	$posYHeader = $this->GetY();
			  
			  $posYTitulos = $posYHeader+7;//$this->GetY()+2;			  	
			  //datos de factura
			  $posX += 73;			
			  $this->SetXY($posX,$posYfolio);
  			  $this->Ln();	
			  $this->SetX($posX);	
			  if($_GET['Type']==119){
			  	
			  }else{
			  	
			  	if ($myrow['typeinvoice']==4){
			  			
			  		$certificadox=explode('|',$myrow['cadenatimbre']);
			  		$this->SetX(155);
			  		$this->SetFont('helvetica','B',7);
			  		$this->cell(50,4,utf8_decode('Certificado del SAT '),'LTR',1,'C');
			  		$this->SetX(155);
			  		$this->SetFont('helvetica','B',7);
			  		$this->cell(50,4,$certificadox[6],'LTR',1,'C',1);
			  		$qr = new QRcode;
			  		if (strpos($myrow['cadena'],'XAXX010101000')==true){
			  			$rfccliente='XAXX010101000';
			  		}else{
			  			$rfccliente=$myrow['rfc'];
			  		}
			  			
			  		$totaletrasbarra=abs($myrow['montofactura'])+abs($myrow['montoivafact']);
			  		$separa=explode(".",$totaletrasbarra);
			  		$montoctvsbarra2 = $separa[1];
			  		$montoctvsbarra1 = $separa[0];
			  		//$montoctvs1x= $separa[1];
			  		if (left($montoctvsbarra2,3)>=995){
			  			$montoctvsbarra1=$montoctvsbarra1+1;
			  		}
			  		$montoctvsbarra2 = $separa[1];
			  		if (left($montoctvsbarra2,3)>956){
			  			$montoctvsbarra2=1;
			  		}
			  		$montoctvsbarra2=str_replace('+','0',add_spacesstring(left($montoctvsbarra2,6),6));
			  			
			  		$cadbarra="?re=".$myrow['rfcempre']."&rr=".$rfccliente."&tt=".add_cerosstring($montoctvsbarra1,10).".".$montoctvsbarra2."&id=".$myrow['uuid'];
			  			
			  		//$cad = "http://www.arandalab.com.mx";
			  		$imagencbb='companies/'.$_SESSION['DatabaseName'].'/micbb.png';
			  		unlink($imagencbb);
			  		$qr->png($cadbarra,$imagencbb,'L',2.1,1);
			  		//$this->addJpegFromFile($imagencbb,155,$this->GetY(),0,0);
			  		$this->Image( $imagencbb,170,$this->GetY()+10,0,0);
			  			
			  			
			  	
			  	} else {
					$this->SetFont('helvetica','B',7);
					$this->cell(50,4,utf8_decode('Número/Año de Aprobación'),'LTR',1,'C');
					$this->SetFont('helvetica','',7);
					$this->SetX($posX);	
					$this->cell(50,4,$Noaprobacion.'/'.$anioAprobacion,'LTR',1,'C',1);
			  	}
			  	
				$this->SetFont('helvetica','B',7);
				$this->SetX($posX);	
				$this->cell(50,4,utf8_decode('No. Certificado Digital'),'LTR',1,'C');
				$this->SetFont('helvetica','',7);
				$this->SetX($posX);	
			  }
			  $this->cell(50,4,$Certificado,'LBR',1,'C',1);
			
			  //titulos encabezado
			  $this->SetFillColor(205,205,205);
			  $this->SetY($posYTitulos+10);
			  $this->SetFont('helvetica','B',7);
			  $this->cell(35,4,'Codigo','LTB',0,'C',1);
			  $this->cell(90,4,'Descripcion','TB',0,'C',1);
			  $this->cell(10,4,'UM','TB',0,'C',1);
			  $this->cell(30,4,'Precio Unitario','TB',0,'R',1);
			  $this->cell(30.8,4,'Total','TBR',1,'R',1);
			  	
		}
		
	}
?>