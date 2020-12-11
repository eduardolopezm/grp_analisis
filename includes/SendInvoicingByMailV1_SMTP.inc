<?php
/*
typearchivos:
    1 solo pdf y xml
    2 solo xml

*/

include ('printNCR.php');

function SendByMailDoctoxType($DebtorTransID, $typearchivos, $emailsext, &$db) {

	$ncr = new ncrPDF;

	$SQL = "
		SELECT *,
		legalbusinessunit.taxid AS taxidrfc,
		custbranch.taxid as rfccliente,year(debtortrans.origtrandate) AS anio,
		month(debtortrans.origtrandate) AS mesfact,
		custbranch.brname as nombrecliente,
		debtortrans.emails AS correos
        FROM debtortrans INNER JOIN tags ON tags.tagref=debtortrans.tagref
        INNER JOIN legalbusinessunit ON  legalbusinessunit.legalid = tags.legalid
	    INNER JOIN custbranch on custbranch.branchcode=debtortrans.branchcode
		WHERE id = ".$DebtorTransID;

	$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('No se obtuvieron los datos del email');
	$DbgMsg = _('El siguiente SQL se utilizo');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	$myrowmails = DB_fetch_array($Result);

	$emails			= $myrowmails['emails'];
	$tipofac		= $myrowmails['type'];
	$legalname		= $myrowmails['legalname'];
	$InvoiceNoTAG	= $myrowmails['folio'];
	$separa 		= explode('|',$InvoiceNoTAG);

	if($tipofac == 12) {
		$serie = $separa[1];
		$folio = $separa[0];
	} else {
		$serie = $separa[0];
		$folio = $separa[1];
	}

	if ($tipofac=='12'){
	    $folder="Recibo";
	}elseif($tipofac=='10' or $tipofac=='110'){
	    $folder="Facturas";
	}elseif($tipofac=='13'){
	    $folder="NCreditoDirect";
	}elseif($tipofac=='21'){
	    $folder="NCargo";
	}else{
	    $folder="NCredito";
	}

	$_GET['OrderNo']	= $myrowmails['order_'];
	$_GET['Type']		= $myrowmails['type'];
	$_GET['TransNo']	= $myrowmails['transno'];

	if($myrowmails['typeinvoice'] == 2 or $myrowmails['typeinvoice'] == 4) {



			$direcciondos="./companies/".$_SESSION['DatabaseName']."/SAT/".str_replace(',','',str_replace('.','',str_replace(' ','',$legalname)))."/";
			$direcciondos=$direcciondos.'XML/'.$folder.'/'.$serie.$folio.'.pdf';
			if($tipofac != '10' AND $tipofac != '110' AND $tipofac != '66') {
				include ('includes/pdfOtherDocumentsClass.inc');
				$pdf = new pdfFactura;
				$pdf->printPDF($direcciondos);
			} else {

				$sqlxml = "Select Xmls.xmlImpresion
							FROM Xmls
							WHERE type = '".$_GET['Type']."'
								AND transNo = '".$_GET['TransNo']."'";
				$resultxml = DB_query($sqlxml, $db);
				if (DB_num_rows($resultxml)>0) {
					$existe=1;
				}else{
					$existe=0;
				}

				if ($existe==0){

					include ('includes/pdfFacturaClass.inc');
					$pdf = new pdfFactura;
					$pdf->printPDF($direcciondos);
				}else{

					require_once('jasper/ReportsWithXML.php');
					// $funcion=191;
					global $db;

					$sql="SELECT Xmls.rfcEmisor,Xmls.xmlImpresion,Xmls.fiscal,Xmls.type,legalbusinessunit.logo from Xmls
						LEFT JOIN legalbusinessunit ON legalbusinessunit.taxid=Xmls.rfcEmisor
						where Xmls.transNo=".$_GET['TransNo']." and Xmls.type=".$_GET['Type'];
					$ErrMsg=_('El Sql que fallo fue');
					$DbgMsg=_('No se pudo obtener los datos de la unidad de negocio');

					$sqlLogo="SELECT confvalue from config where confname='rootPath'";
					$resultLogo=DB_query($sqlLogo,$db,$ErrMsg,$DbgMsg,true);
					$result=DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

					$queryNombre="select typename from systypescat where typeid=".$_GET['Type'];
					$ResultNombre=DB_query($queryNombre,$db,$ErrMsg,$DbgMsg,true);
					$nombre='';
					if($mynombre=DB_fetch_array($ResultNombre)){
						$nombre=$mynombre['typename'];
					}

					if(DB_num_rows($result)>0){
						if($row=DB_fetch_array($result)){
							$row2=DB_fetch_array($resultLogo);
							$logo=$row2["confvalue"].$row["logo"];//

							if(!file_exists($logo)){
								$logo=$row2["confvalue"]."logo.jpg";
							}
							$cancelado=0;
							$copias=1;
							$pdfBytes=reportXML($row["xmlImpresion"],$row["fiscal"],$logo,$nombre,$row["type"],$cancelado,$copias);
							//echo $pdfBytes;
							$gestor = fopen($direcciondos, "w");
							fwrite($gestor, $pdfBytes);
							fclose($gestor);
						}
					}
				}// fin de version 6
			}
			$archivoPDF=$direcciondos;
			$archivoPDFNombre = $serie.$folio.'.pdf';

			if (!file_exists($archivoPDF)){
				if($tipofac != 10 AND $tipofac != 110) {
					$ncr->exportPDF(1);
				}
			}


		// obtenemos el archivo XML
		$direccion="./companies/".$_SESSION['DatabaseName']."/SAT/".str_replace(',','',str_replace('.','',str_replace(' ','',$legalname)))."/";
		$pagina=$direccion.'XML/'.$folder.'/'.$serie.$folio.'.xml';

		$nombre_archivo = $pagina;
		$archivoXML = $nombre_archivo;
		$archivoXMLNombre = $serie.$folio.'.xml';
		//Leemos el archivo xml para incluirlo en email como adjunto
		$archivo= fopen("$nombre_archivo", "r");
		$contenido = fread($archivo, filesize("$nombre_archivo"));
		$encoded_attach = chunk_split(base64_encode($contenido));
		fclose($archivo);
		//aqui asunto del email
		if($myrowmails['rfccliente'] == $_SESSION['SendInvoicingRFC']) {
			$asunto = "ATM940922626";
		}else{
			$asunto = "Notificacion de Comprobante Fiscal Digital ".$serie.$folio;
		}

		// los emails de recepcion
		$email=str_replace(';',',',$emails);
		//aqui es donde va el email del usuario que envia
		//$mmail="soporte@grp.com";
		$mmail="soporte@grp.com";
		 //aqui el mensaje que contendra el correo

		//$textomail=$textomail."\n\n Este es un mensaje automatico.Las respuestas a este mensaje no estan monitorizadas ni seran respondidas. \n\n ";
		$textomail=$textomail.$legalname;
		if($typearchivos==1){
			$textomail=$textomail."
						\nEnvia a usted adjunto a este correo su Comprobante Fiscal Digital (CFD) en formato PDF con los siguientes datos:";
		}else{
			$textomail=$textomail."
						\nEnvia a usted adjunto a este correo su Comprobante Fiscal Digital (CFD) en formato XML con los siguientes datos:";
		}
		$textomail=$textomail."\nFecha: ".$myrowmails['origtrandate'];
		$textomail=$textomail."\nSerie: ".$serie;
		$textomail=$textomail."\nFolio: ".$folio;
		//$textomail=$textomail."\nEmails Envio: ".$emails;

		$textomail=$textomail."\n\n Le agradecemos nuevamente por su preferencia. \n\n ";
		//emails de envio para descarga de documentos
		$linkPDF='http://' . $_SERVER['SERVER_NAME'] . '/erpdistribucion/ArchivosDescarga.php?type='.$tipofac.'&folio='.$folio.'&serie='.$serie.'&legalname='.str_replace(',','',str_replace('.','',str_replace(' ','',$legalname))).'&typearch=PDF&Datos=' . $_SESSION['DatabaseName'];
		$ligaPDF='<a  target="_blank" href="' .$linkPDF . '">Descargar Factura en Formato PDF</a>';
		$linkXML='http://' . $_SERVER['SERVER_NAME'] . '/erpdistribucion/ArchivosDescarga.php?type='.$tipofac.'&folio='.$folio.'&serie='.$serie.'&legalname='.str_replace(',','',str_replace('.','',str_replace(' ','',$legalname))).'&typearch=XML&Datos=' . $_SESSION['DatabaseName'];
		$ligaXML='<a  target="_blank" href="' .$linkXML . '"> Descargar Factura en Formato XML</a>';
		$textomail=$textomail."\n\n ".$ligaPDF;
		$textomail=$textomail."\n\n ".$ligaXML;

		if($myrowmails['rfccliente'] == $_SESSION['SendInvoicingRFC']) {
			$textomail='';
		}

		//$textomail=$textomail."\n\n Este es un mensaje automatico.Las respuestas a este mensaje no estan monitorizadas ni seran respondidas. \n\n ";
		$mensaje .= $textomail;

		// las cabeceras del correo que incluye quien envia el correo que en este caso es el que lleno el formulario
		$cabeceras = "From: $nombre <$mmail>\n";
		$cabeceras .= "Reply-To: $mmail\n";
		//el tipo de correo a enviar que es de texto y datos
		$cabeceras .= "MIME-version: 1.0\n";
		$cabeceras .= "Content-type: multipart/mixed; ";
		$cabeceras .= "boundary=\"Message-Boundary\"\n";
		$cabeceras .= "Content-transfer-encoding: 7BIT\n";
		// aqui el archivo adjunto que contendra
		$nombre_archivo=$serie.$folio.".xml";
		$cabeceras .= "X-attachments: $nombre_archivo";
		$body_top = "--Message-Boundary\n";
		$body_top .= "Content-type: text/plain; charset=US-ASCII\n";
		$body_top .= "Content-transfer-encoding: 7BIT\n";
		$body_top .= "Content-description: Mail message body\n\n";
		 // aqui unimos las varibles $mensaje y $body_top en una sola


		$cuerpo = $body_top.$mensaje;
		$nombref= $nombre_archivo;
		$direcciondosPFD=$archivodos;
		if($typearchivos==1){
			set_time_limit(600);
			$archivo= $archivoPDF;
			$buf_type= 'application/pdf';//obtener_extencion_stream_archivo($archivoPDF); //obtenemos tipo archivo
			$fp= fopen( $archivoPDF, "r" ); //abrimos archivo
			$buf= fread( $fp, filesize($archivo) ); //leemos archivo completamente
			fclose($fp); //cerramos apuntador;
			//Archivo PDF
			$archivoPDFEnvio=$serie.$folio.".pdf";
			//$archivoPDFEnvio="ArchivoPrueba.pdf";
			$cuerpo .= "\n\n--Message-Boundary\n";
			$cuerpo .= "Content-Type: ". $buf_type. "; name=\"". $archivoPDFEnvio. "\"\r\n"; //envio directo de datos
			$cuerpo .= "Content-Transfer-Encoding: base64\r\n";
			$cuerpo .= "Content-Disposition: attachment; filename=\"". $archivoPDFEnvio. "\"\r\n\r\n";
			$cuerpo .= base64_encode($buf). "\r\n\r\n";
		}
		//Archivo XML
		$cuerpo .= "\n\n--Message-Boundary\n";
		$cuerpo .= "Content-type: Binary; name=\"$nombref\"\n";
		$cuerpo .= "Content-Transfer-Encoding: BASE64\n";
		$cuerpo .= "Content-disposition: attachment; filename=\"$nombref\"\n\n";
		$cuerpo .= "$encoded_attach\n";
		$cuerpo .= "--Message-Boundary--\n";


	} elseif($myrowmails['typeinvoice'] == 1) {

		$legalname 		= $myrowmails['legalname'];
		$email 			= str_replace(';', ',', $emails);
		$email 			= "";
		$tipofac 		= $myrowmails['type'];
		$InvoiceNoTAG	= $myrowmails['folio'];
		$rfc			= trim($myrowmails['taxidrfc']);
		$keyfact		= $myrowmails['keysend'];
		$separa 		= explode('|',$InvoiceNoTAG);

		if($tipofac == 12) {
			$serie = $separa[1];
			$folio = $separa[0];
		}else{
			$serie = $separa[0];
			$folio = $separa[1];
		}

		//bajar archivo XML
		$ligaXML				= $_SESSION['XSA']."xsamanager/downloadCfdWebView?serie=".$serie."&folio=".$folio."&tipo=XML&rfc=".$rfc."&key=".$keyfact;
		$ligaPDF				= $_SESSION['XSA']."xsamanager/downloadCfdWebView?serie=".$serie."&folio=".$folio."&tipo=PDF&rfc=".$rfc."&key=".$keyfact;
		$id 					= $serie.$folio;
		$ID_Audience			= 8799;
		$Nombre					= $myrowmails['nombrecliente'];
		$rfccliente				= $myrowmails['rfccliente'];
		$Mes					= $myrowmails['mesfact'];
		$Anio					= $myrowmails['anio'];
		$Factura				= $id;
		$URL_PDF				= $ligaPDF;
		$URL_XML				= $ligaXML;
		$Referencia_Dolares		= 1/$myrowmails['rate'];
		$Referencia_Numerica	= $myrowmails['ovamount']+$myrowmails['ovgst'];


		$SQLCurrency="SELECT *
		       FROM configTEA c
		       WHERE c.typetea='".$myrowmails['typesendemail']."'";
		$ErrMsg =  _('No se pudo recuperar el valor de la moneda ');
		//$GetCurrency = DB_query($SQLCurrency,$db,$ErrMsg);

		$GetCurrency = DB_query($SQLCurrency, $db, $ErrMsg, $DbgMsg, true);
		if (DB_num_rows($GetCurrency)==1) {
			$myrowTEA = DB_fetch_array($GetCurrency);
			$keyTEA=$myrowTEA['keytea'];
			$wsdlTEA=$myrowTEA['wsdl'];
			$proxiTEA=$myrowTEA['proxy'];
			$ID_Audience=$myrowTEA['idaudience'];
		}

		if($myrowmails['typesendemail']==2){
			$enviofactura=$ID_Audience.'|'.$email.'|'.$rfccliente.'|'.$Nombre.'|'.$Mes.'|'.$Anio.'|'.$Factura.'|'.$URL_PDF.'|'.$URL_XML.'|'.$Referencia_Numerica.chr(13).chr(10);
			//'124917014be244a6972581f80286b8ab';
		}else{
			//factura general
			$enviofactura=$ID_Audience.'|'.$email.'|'.$rfccliente.'|'.$Nombre.'|'.$Mes.'|'.$Anio.'|'.$Factura.'|'.$URL_PDF.'|'.$URL_XML.'|'.$Referencia_Numerica.'|'.$Referencia_Dolares.chr(13).chr(10);
			//$keyTEA='876740832f7042508cfc861e0476eaa0';
		}

		$sError = "";

		$oClient = new nusoap_client($wsdlTEA,true);//URL del webservice

		$oClient->setHTTPProxy($proxiTEA,'80');
		$param=array('importKey'=>$keyTEA,'importContent'=>$enviofactura);
		echo "<font size=2 color=Darkblue><b>"._('SE REALIZO EL ENVIO DE DATOS DE MANERA SATISFACTORIA')."</b></font><br><br>";
		//$param=array('importKey'=>'124917014be244a6972581f80286b8ab','importContent'=>$file);
		$oResult = $oClient->call('newImportJob',$param);//Selección del método y pase de parámetros

		if($oClient->fault){
			$sError = 'Ha ocurrido un error inesperado fault, lamentamos las molestias que esto te ocasiona.'.$oClient->fault;
		} else {
			$err = $oClient->getError();
			//echo '<br><br>Datos enviados:<br><br>'.$enviofactura.'<br><br><br>key utilizado:'.$keyTEA;
			if ($err) {
				$sError = ($err);
				//echo $sError;
				echo "<font size=2 color=Darkred><b>"._('NO SE REALIZO EL ENVIO DE DATOS, LA DESCRIPCION DEL ERROR ES :<br><br>').$sError."</b></font><br><br>";
			} else {
				 echo "<font size=2 color=Darkblue><b>"._('SE REALIZO EL ENVIO DE DATOS DE MANERA SATISFACTORIA')."</b></font><br><br>";
			}
		}
	}

	if(strpos($emailsext, ';') == TRUE) {

		$separamail = explode(';',$emailsext);
		$cuenta=count($separamail);

		for($xmail=0;$xmail<$cuenta;$xmail++){
		    //extraigo el email
		    $emaily=$separamail[$xmail];
		    //valido que sea un email valido
		    if (IsEmailAddress($emaily)){
				if ($xmail==0){
				    $emailsx=$emaily;
				}else{
				    $emailsx=$emailsx.';'.$emaily;
				}
		    }
		}
	} elseif(strlen(trim($emailsext)) > 0) {// si la longitud es mayor a cero
		//valido que el email proporcionado sea valido
		if (!IsEmailAddress($emailsext)){
		    $emailsx="";
		}else{
			$emailsx= $emailsext;
		}
	}

	if(strlen(trim($emailsx)) > 0){// si la longitud es mayor a cero
		$email = $email . ';' . $emailsx;
	}


	$email = str_replace(';',',',$email);

	require_once('includes/mail.php');

	$email     .= "," . $_SESSION['SendInvoicingEmail'];
	$from 		= $_SESSION['SendInvoicingFrom'];
	$senderName = $_SESSION['SendInvoicingSubject'];
	$to 		= array_unique(explode(',', $email));
	$to_temp	= array();
	$mensaje 	= str_replace("\n", "<br/>", $mensaje);
	$asunto = "Notificacion de Comprobante Fiscal Digital ".$serie.$folio;
	if(empty($mensaje)) {
		$mensaje = " ";
	}

	foreach($to as $mail) {
		if(IsEmailAddress($mail)) {
			$mailxy=explode('@',$mail);

			if ($mailxy[1]=='gmail.com.mx'){
				echo $mailxy[1];
			}else{
				$to_temp[] = filter_var($mail, FILTER_SANITIZE_EMAIL);
			}
		}
	}

	$mail 			= new Mail();
	$mail->protocol = 'smtp';
	$mail->hostname = 'localhost';
	$mail->port 	= 25;
	$mail->timeout  = 25;
	$to 			= $to_temp;

	$mail->setTo($to);
	$mail->setFrom($from);
	$mail->setSender($senderName);
	$mail->setSubject($asunto);
	$mail->setHtml($mensaje);

	if($typearchivos==1){
		$mail->addAttachment($archivoPDF, $archivoPDFNombre);
		$mail->addAttachment($archivoXML, $archivoXMLNombre);
	}else{
		$mail->addAttachment($archivoXML, $archivoXMLNombre);
	}

	if($mail->send()) {
		return '1|'.$serie.$folio;
	} else {
		return '0|'.$serie.$folio;
	}
}

?>
