<?php
/**
 * Envió de correo, pase de cobro
 *
 * @category Proceso
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 21/11/2019
 * Fecha Modificación: 21/11/2019
 * Proceso para el envió de correo de los pases de cobro a los contribuyentes
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
//
$PageSecurity = 5;
$funcion = 602;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db, 'Enviar Correo');

include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';
include('includes/Functions.inc');
include('includes/MiscFunctions.inc');
include ('Numbers/Words.php');

ini_set('display_errors', 1);
ini_set('log_errors', 1);

if(isset($_GET["tagref"])) {
	$tagref = $_GET["tagref"]; 
} else if(isset($_POST["tagref"])) {
	$tagref = $_POST["tagref"];
} else {
	$tagref = 0;
}

if(isset($_GET["transno"])) {
	$transno = $_GET["transno"];
} else if(isset($_POST["transno"])) {
	$transno = $_POST["transno"];
} else {
	$transno = 0;
}

if(isset($_GET["legalid"])) {
	$legalid = $_GET["legalid"];
} else if(isset($_POST["legalid"])) {
	$legalid = $_POST["legalid"];
} else {
	$legalid = 0;
}

if(isset($_GET["debtorno"])) {
	$debtorno = $_GET["debtorno"];
} else if(isset($_POST["debtorno"])) {
	$debtorno = $_POST["debtorno"];
} else {
	$debtorno = 0;
}

if(isset($_GET["email"])) {
	$email = $_GET["email"];
} else if(isset($_POST["email"])) {
	$email = $_POST["email"];
} else {
	$email = "";
}

$Ocultar = 0;
if (isset($_GET['PV'])) {
	//No mostrar formulario cuando venga del punto de venta
	$Ocultar = 1;
}

if (isset($_GET['Enviar'])) {
	$_POST['Enviar'] = $_GET['Enviar'];
}

if (isset($_GET['emails'])) {
	$_POST['emails'] = explode(";",$_GET['emails']);
}

if (isset($_GET['tipocotizacion'])) {
	$_POST['tipocotizacion'] = $_GET['tipocotizacion'];
}
if (!isset($_POST['tipocotizacion'])) {
	// Simple por default
	$_POST['tipocotizacion'] = 2;
}

$BDS = array (
		"gruposervillantas_DES" => "gruposervillantas_DES",
		"gruposervillantas_CAPA" => "gruposervillantas_CAPA",
		"gruposervillantas" => "gruposervillantas"
);

if (in_array ( $_SESSION ['DatabaseName'], $BDS ) == true) {
	$_POST['tipocotizacion'] = 3;
}

if ($_POST['tipocotizacion'])
	$tipoCotiz = $_POST['tipocotizacion'];

	
if(isset($_POST['Agregar'])) {
	if(IsEmailAddress($email)) {
		$rs = DB_query("SELECT * FROM custmails WHERE email = '$email' AND debtorno = '$debtorno'", $db);
		if(DB_num_rows($rs)) {
			prnMsg(_('El correo ya existe!'), 'error');
		} else {
			$errMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El correo no pudo ser agregado');
			$dbgMsg = _('El siguiente SQL se utilizo para agregar un correo');
			DB_query("INSERT INTO custmails (email, debtorno, active) VALUES('$email', '$debtorno', 1)", $db, $errMsg, $dbgMsg, TRUE);
			$email = "";
		}
	} else {
		prnMsg(_('El correo no es v�lido!'), 'error');
	}
}

if (empty($emails)) {
	$emails = DB_query("SELECT * FROM custmails WHERE active = 1 AND debtorno = '$debtorno' ORDER BY idemail DESC", $db);
}

function nombremeslargo($idmes){
	$nombremeslargo = "";
	switch ($idmes) {
		case 1:
			$nombremeslargo = "Enero";
			break;
		case 2:
			$nombremeslargo = "Febrero";
			break;
		case 3:
			$nombremeslargo = "Marzo";
			break;
		case 4:
			$nombremeslargo = "Abril";
			break;
		case 5:
			$nombremeslargo = "Mayo";
			break;
		case 6:
			$nombremeslargo = "Junio";
			break;
		case 7:
			$nombremeslargo = "Julio";
			break;
		case 8:
			$nombremeslargo = "Agosto";
			break;
		case 9:
			$nombremeslargo = "Septiembre";
			break;
		case 10:
			$nombremeslargo = "Octubre";
			break;
		case 11:
			$nombremeslargo = "Noviembre";
			break;
		case 12:
			$nombremeslargo = "Diciembre";
			break;
		
	}
	return $nombremeslargo;
	
}

function mail_attachment($content, $mailto, $from_mail, $from_name, $replyto, $subject, $message, $filename) {

	/*error_reporting(E_ALL);
	ini_set('display_errors', '1');
	ini_set('log_errors', 1);
	ini_set('error_log', dirname(__FILE__) . '/error_log.txt');//*/
	
	
  //  echo '<pre>'.$content.'<br>->'.$mailto.'<br>->'. $from_mail.'<br>->'. $from_name.'<br>->'. $replyto.'<br>->'. $subject.'<br>->'. $message.'<br>->'. $filename;
	//$CC_mail='jahepi@gmail.com';
	$content = chunk_split(base64_encode($content));
	$uid = md5(uniqid(time()));
	$header = "From: ".$from_name."\r\n";
	$header .= "Reply-To: ".$replyto."\r\n";
	$header .= "MIME-Version: 1.0\r\n";
	$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
	//$message  = "This is a multi-part message in MIME format.\r\n";
	$message2  = "--".$uid."\r\n";
	$message2  .= "Content-type:text/plain; charset=iso-8859-1\r\n";
	$message2  .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	$message2  .= $message."\r\n\r\n";
	$message2  .= "--".$uid."\r\n";
	$message2  .= "Content-Type: application/pdf; name=\"$filename\"\r\n";
	$message2  .= "Content-Transfer-Encoding: base64\r\n";
	$message2  .= "Content-Disposition: attachment; filename=\"$filename\"\r\n\r\n";
	$message2   .= $content."\r\n\r\n";
	$message2  .= "--".$uid."--";
        
	
	
	if (mail($mailto, $subject, $message2, $header)) {
		echo "<br>1: " . "VERDADERO"; 
		return TRUE;
	} else {
		print_r(error_get_last());
		echo "<br>2: " . "FALSO: " . $mail->ErrorInfo;;
		return FALSE;
	}
}

function generarPDF($transno, $db) {
	include('includes/PDFStarter.php');
	$PaperSize = 'letter';
	for ($i=1;$i<=1;$i++) {
		if ($i==2){
			$pdf->newPage();
		}
		$line_height=18;
		$PageNumber = 1;
		$sql = "SELECT 	salesorderdetails.unitprice as precio,
				salesorderdetails.stkcode as codigo,
				salesorderdetails.quantity as cantidad,
				salesorderdetails.discountpercent as descu,
				salesorderdetails.discountpercent1 as desc1,
				salesorderdetails.discountpercent2 as desc2,
				salesorderdetails.narrative as infext,
				stockmaster.description as descripcion,
				salesorders.printedpackingslip as printcomments,
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
				salesorders.comments,
				custbranch.brname,
				custbranch.phoneno,
				custbranch.faxno,
				vehiclesbycostumer.plate as placa,
				vehiclesbycostumer.serie as serie,
				vehiclesbycostumer.numeco as numeconomico,
				vehiclesbycostumer.color as color,
				vehiclesbycostumer.yearvehicle as anio,
				vehiclesbycostumer.lastmilage as kilom,
				vehiclemarks.mark as marca,
				vehiclemodels.model as modelo,
				salestypes.sales_type as lista,
				(salesorderdetails.quantity*salesorderdetails.unitprice)-(((salesorderdetails.quantity*salesorderdetails.unitprice)*(1-salesorderdetails.discountpercent))*(1-salesorderdetails.discountpercent1))*(1-salesorderdetails.discountpercent2) as totaldesc
			FROM salesorderdetails INNER JOIN stockmaster
				ON salesorderdetails.stkcode = stockmaster.stockid
				INNER JOIN salesorders ON salesorders.orderno = salesorderdetails.orderno 
				INNER JOIN salesman ON salesman.salesmancode = salesorders.salesman
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = salesorders.debtorno
				INNER JOIN custbranch ON custbranch.branchcode = salesorders.branchcode and debtorsmaster.debtorno=custbranch.debtorno
				INNER JOIN salestypes ON salestypes.typeabbrev = salesorders.ordertype
				LEFT JOIN vehiclesbycostumer ON salesorders.vehicleno = vehiclesbycostumer.vehicleno
				LEFT JOIN vehiclemarks ON  vehiclemarks.idmark=vehiclesbycostumer.idmark 
				LEFT JOIN vehiclemodels ON vehiclemodels.idmodel=vehiclesbycostumer.idmodel 
	
			WHERE  salesorderdetails.stkcode = stockmaster.stockid
				AND salesorders.orderno = salesorderdetails.orderno
				AND salesman.salesmancode = salesorders.salesman
				AND debtorsmaster.debtorno = salesorders.debtorno
				AND custbranch.branchcode = salesorders.branchcode
				AND salestypes.typeabbrev = salesorders.ordertype
				AND salesorderdetails.orderno =".$transno."
			ORDER BY salesorderdetails.orderlineno";
		
		$result = DB_query($sql, $db);
		if (DB_num_rows($result) > 0) {
			$FontSize=11;
			$pdf->selectFont('./fonts/Helvetica.afm');
			$pdf->addinfo('Title', _('Impresion de Orden') );
			$pdf->addinfo('Subject', _('Lista de orden') . ' ' . $transno);
	
			$ErrMsg = _('Ha habido un problema al intentar recuperar los detalles de la orden') . ' ' .
			$transno . ' ' . _('de la base de datos');
			$x=0;
			if (DB_num_rows($result)>0){
				include('includes/PDFSalesOrderQuotePageHeader.inc');
				while ($myrow=DB_fetch_array($result)){
					$FontSize=11;
					$title=intval($myrow['title']);
					if ($title==0){
						$title='Pedido Cerrado';
					} elseif ($title==1){
						$title='Cotizacion';
					} elseif ($title==2){
						$title='Pedido Abierto';
					} elseif ($title==3){
						$title='Pedido Cancelado';
					}
	
					if ($Copy=='Customer'){
						$pdf->addText(490,760,$FontSize,$title );
					} else {
						$pdf->addText(490, 760,$FontSize,$title );
					}
					$x=$x+1;
						
					$pdf->addTextWrap(70,645,527,10,$myrow['nombre']);
					$pdf->addTextWrap(70,633,180,10,$myrow['dir1'] .',');
					$pdf->addTextWrap(70,620,152,10,$myrow['dir2'].',');
					$pdf->addTextWrap(70,605,100,10,$myrow['dir3']);
					$pdf->addTextWrap(180,605,149,10,$myrow['dir4']);
					if(strlen($myrow['phoneno'])>0){
						$pdf->addText($XPos+15, 592,10, _('Telefono:'));
						$pdf->addTextWrap(70,590,159,10,$myrow['phoneno']);
					}else
					{
						$pdf->addText($XPos, 592,$FontSize, _(' '));
					}
					if(strlen($myrow['faxno'])>0){
						$pdf->addText($XPos+205, 592,$FontSize=10, _('Fax:'));
						$pdf->addTextWrap(234,590,100,$FontSize=12,$myrow['faxno']);
					}else
					{
						$pdf->addText($XPos+167, 592,$FontSize, _(' '));
					}
	
					$FontSize=10;
					$pdf->addTextWrap(450,633,127,$FontSize,$myrow['termino']);
					$pdf->addTextWrap(450,621,127,$FontSize,$myrow['lista']);
					$posyven=330;
						
	
					if ($verplaca==1){
						$pdf->addTextWrap($Left_Margin+10,$posyven,150, $FontSize, 'Placa: '. $myrow['placas']);
					}
					if ($verserie==1){
						$posyven=$posyven-15;
						$pdf->addTextWrap($Left_Margin+10, $posyven,150, $FontSize,'Serie: '. $myrow['serie']);
					}
					if ($verkilometraje==1){
						$posyven=$posyven-15;
						$pdf->addTextWrap($Left_Margin+10, $posyven,150, $FontSize, 'Kilometraje: '. $myrow['kilometraje']);
					}
	
						
					$pdf->addText(90,725-160,$FontSize-2,$myrow['placas']);
					$pdf->addText(90,725-172,$FontSize-2,$myrow['serie']);
					$pdf->addText(90,725-182,$FontSize-2,$myrow['numeconomico']);
					$pdf->addText(190,725-160,$FontSize-2,$myrow['anio']);
					$pdf->addText(190,725-172,$FontSize-2,$myrow['modelo']);
					$pdf->addText(190,725-182,$FontSize-2,$myrow['marca']);
					$pdf->addText(300,725-160,$FontSize-2,$myrow['color']);
					$pdf->addText(300,725-172,$FontSize-2,$myrow['kilom']);
						
					$pdf->addText(450,725-130,$FontSize,$myrow['fecha']);
					$pdf->addText(450,725-143,$FontSize,$myrow['vendedor']);
					 
					/*if ($vercomentarios==1){
						$pdf->addTextWrap(285,565,286,$FontSize,'Comentarios'.$myrow['comments']);
					}*/
					
					$precio = number_format($myrow['precio'],2);
					$iva = number_format($myrow['iva'],2);
					$descu = number_format($myrow['descu']*100);
					$desc1 = number_format($myrow['desc1']*100);
					$desc2 = number_format($myrow['desc2']*100);
					$importe=number_format(($myrow['cantidad']*$myrow['precio'])-$myrow['totaldesc'],2);
					$FontSize=8;
						
					$daco=$myrow['codigo'];
					$LeftOversdc = $pdf->addTextWrap(30,$YPos-80,60,$FontSize,$daco);
					while (strlen($LeftOversdc) > 1 ) {
						$YPos -= 1*$line_height*0.40;
						$LeftOversdc = $pdf->addTextWrap(30,$YPos-80,40,$FontSize,$LeftOversdc);
					}
						
					$datos=$myrow['descripcion'];
					$LeftOvers = $pdf->addTextWrap(90,$YPos-80,130,$FontSize,$datos);
					while (strlen($LeftOvers) > 1 ) {
						$YPos -= 1*$line_height*0.40;
						$LeftOvers = $pdf->addTextWrap(90,$YPos-80,130,$FontSize,$LeftOvers);
					}
						
					$daextra=$myrow['infext'];
					$LeftOversde = $pdf->addTextWrap(187,$YPos-80,80,$FontSize,$daextra);
					while (strlen($LeftOversde) > 1 ) {
						$YPos -= 1*$line_height*0.40;
						$LeftOversde = $pdf->addTextWrap(187,$YPos-80,80,$FontSize,$LeftOversde);
					}
						
					$pdf->addTextWrap(280,$YPos-80,20,$FontSize,$myrow['cantidad']);
					$pdf->addTextWrap(280,$YPos-80,90,$FontSize,'$ '.$precio,'right');
					$pdf->addTextWrap(360,$YPos-80,45,$FontSize,$descu.'%','right');
					$pdf->addTextWrap(400,$YPos-80,45,$FontSize,$desc1.'%','right');
					$pdf->addTextWrap(440,$YPos-80,45,$FontSize,$desc2.'%','right');
					$pdf->addTextWrap(460,$YPos-80,90,$FontSize,'$ '.$importe,'right');
	
					$FontSize=10;
					$pdf->addTextWrap($xpos+500,120,90,$FontSize,'$ '.$iva,'right');
	
					if ($YPos-$line_height <= 100){
						$PageNumber++;
						include ('includes/PDFSalesOrderQuotePageHeader.inc');
					} else {
						$YPos -= ($line_height);
					}
					$tempcoments=$myrow;
				}
				if ($tempcoments['printcomments']==1){
						$comments=$tempcoments['comments'];
						$Y=80;
						$LeftOverscom =$pdf->addTextWrap(20,$Y,550,$FontSize=7,'Comentarios:'.' '.$comments);
						while (strlen($LeftOverscom) > 1 ) {
						//
						//$YPos -= 1*$line_height*0.20;
						//
						$Y=$Y-10;
						$LeftOverscom = $pdf->addTextWrap(20,$Y,550,$FontSize=7,$LeftOverscom);
						}
					} 
				$pdf->addTextWrap(170,530,225,$FontSize,_('Descripcion de ').$title,'right');
				$FontSize=10;
				$YPos=90;
				$pdf->addTextWrap(20,$YPos,900,$FontSize, _('Las Condiciones de Precio Pueden Variar Sin Previo Aviso...'));
			}
			$sql2="SELECT salesorders.currcode,
					       (sum((salesorderdetails.quantity*salesorderdetails.unitprice)-((salesorderdetails.quantity*salesorderdetails.unitprice)-(((salesorderdetails.quantity*salesorderdetails.unitprice)*(1-salesorderdetails.discountpercent))*(1-salesorderdetails.discountpercent1))*(1-salesorderdetails.discountpercent2))))AS subtotal,
					       (sum(((salesorderdetails.unitprice*salesorderdetails.quantity)*(1-salesorderdetails.discountpercent)* (1-salesorderdetails.discountpercent1)*(1-salesorderdetails.discountpercent2))) + salesorders.taxtotal) AS total
					FROM salesorderdetails,
					     salesorders,
					     debtorsmaster
					WHERE salesorders.orderno = salesorderdetails.orderno
					  AND debtorsmaster.debtorno = salesorders.debtorno
					  AND salesorders.orderno=" . $transno ."
					GROUP BY currcode";
			$result2=DB_query($sql2,$db, $ErrMsg);
			if (DB_num_rows($result2)>0){
				while ($myrow2=DB_fetch_array($result2)){
					$pdf->addTextWrap($xpos+500,140,90,$FontSize,'$ '.number_format($myrow2['subtotal'],2),'right');
					$total=number_format($myrow2['total'],2);
					$pdf->addTextWrap($xpos+500,100,90,$FontSize,'$ '.$total,'right');
					$YPos=365;
	
					$totaletras=abs($myrow2['total']);
					$separa=explode(".",$totaletras);
					$montoctvs2 = $separa[1];
					$montoctvs1 = $separa[0];
					if (left($montoctvs2,3)>956){
						$montoctvs1=$montoctvs1+1;
					}
					$montoletra=Numbers_Words::toWords($montoctvs1,'es');
					$totaletras=number_format($totaletras,2);
					$separa=explode(".",$totaletras);
					$montoctvs2 = $separa[1];
					if ($montoctvs2>995){
						$montoctvs2=0;
					}
					$montocentavos=Numbers_Words::toWords($montoctvs2,'es');
						
						
					if ($myrow2['currcode']=='MXN'){
						$pdf->addText($Page_Width-$Right_Margin-550,$YPos-250,$FontSize, " ( ".ucwords($montoletra) . " Pesos ". $montoctvs2 ."/100 M.N. ) " ,'right');
					}
					else
					{
						$pdf->addText($Page_Width-$Right_Margin-500, $YPos-250,$FontSize, " ( ".ucwords($montoletra) . " Dolares ". $montoctvs2 ."/100 USD ) " ,'right');
					}
				}
				
				$sqli="SELECT stockcatproperties.label,
						       salesstockproperties.valor
						FROM salesstockproperties,
						     stockcatproperties
						WHERE salesstockproperties.stkcatpropid=stockcatproperties.stkcatpropid
						  AND salesstockproperties.orderno=".$transno;
				$resulti=DB_query($sqli,$db, $ErrMsg);
				$x=0;
				if (DB_num_rows($resulti)>0) {
					$posi=150;
					$FontSize=8;
					$pdf->addText($Left_Margin, $posi , $FontSize, 'INFORMACION COMPLEMENTARIA ');
					$posi=$posi-10;
					$pdf->addText($Left_Margin, $posi , $FontSize, 'Dato: ');
					$pdf->addText($Left_Margin+120, $posi , $FontSize, 'Valor: ');
					while ($myrowi=DB_fetch_array($resulti)){
						$x=$x+1;
						$posi=$posi-10;
						$posi=$posi;
						$PageNumber++;
					}	 
				}	 
			}
		}
		$Copy='Customer';
	}
	return $pdf->output();
}

if(isset($_POST['Enviar'])) {
	$filename = "cotizacion.pdf";
	
	$subject = "Documento PDF";
	if($_SESSION['PDFCotizacionBD']==1){
		//include_once ('includes/PDFCotizacionTemplateV2.inc');
		//if ($_SESSION['DatabaseName']=='erpsdi'){
		//	include_once ('includes/PDFCotizacionTemplateV2.inc');
		//}else{

		define('FPDF_FONTPATH','./fonts/');
		include_once('includes/fpdf.php');
		include_once('includes/fpdi.php');
			
		include_once('companies/'.$_SESSION['DatabaseName'].'/PDFCotizacionTemplateV2.inc');
		//}
		$_GET['Tagref'] = $tagref;
		$_GET['TransNo'] = $transno;
                
		$sqlcot = "SELECT fromcr
			FROM salesorders
			WHERE orderno = '".$transno."'";
		$rescot = DB_query($sqlcot, $db);
		$rowcot = DB_fetch_array($rescot);
		$fromcr = $rowcot['fromcr'];
		if($fromcr == 1){
			$_GET['tipodocto'] = 3;//
		}else{
			$_GET['tipodocto'] = $tipoCotiz;
		}
                
		$_GET['legalid'] = $legalid;
		
		$doc = new pdfCotizacionTemplate();


		$arraydbs = array("erpgosea" => "erpgosea",
						"erpgosea_CAPA" => "erpgosea_CAPA",
						"erpgosea_DES" => "erpgosea_DES");

		if(in_array($_SESSION['DatabaseName'], $arraydbs) == true) {
			if(isset($_POST['hidepricexdocto']) and $_POST['hidepricexdocto'] == "1") {
				$hidepricexdocto = 1;	
			}else{
				$hidepricexdocto = 0;	
			}
			$pdfcode = $doc->exportPDF(1, $hidepricexdocto); 
		} else {
			$pdfcode = $doc->exportPDF(1); 
		}

		//$pdfcode = $doc->exportPDF(1); 
		//echo $pdfcode;
		
	} else {
		$pdfcode = generarPDF($transno, $db);
	}

	if(empty($_POST['emails']) == FALSE) {
		$arraydatabes = array("gruposervillantas" => "gruposervillantas",
							"gruposervillantas_CAPA" => "gruposervillantas_CAPA",
							"gruposervillantas_DES" => "gruposervillantas_DES");
        if(in_array($_SESSION['DatabaseName'], $arraydatabes) == true){
			$subject = "Cotización Grupo Servillantas";
		}
            
		$emailFrom = $_SESSION['FactoryManagerEmail'];
		$fromName = ucwords($_SESSION['DatabaseName']);
		
		//obtener mail del usuario logeado
		$realname="";
		$department="";
        if ($_SESSION['SendSalesOrderFromEmailUser']==1){
			$qry = "SELECT * FROM www_users WHERE userid = '".$_SESSION['UserID']."'";
			$rsm = DB_query($qry,$db);
			$reg = DB_fetch_array($rsm);
			if(IsEmailAddress($reg['email']))
				$emailFrom = $reg['email'];
			
			$realname = $reg['realname'];
			$department = $reg['department'];
			$fromName = $realname;
			if ($_SESSION['SMTP_isTRUE']) {
                $mmail = $_SESSION['SMTP_emailSENDER'];
                //Se utiliza el correo electr�nico en config = SMTP_emailSENDER como DE:weberp@portalito.com
            } else {
                $mmail = "weberp@portalito.com";
            }
        }
		foreach($_POST['emails'] as $correo) {
			//echo "<pre> for emails";
			$txt = "Documento Adjunto";
			if ($_POST['txtmail']!="")
				$txt = $_POST['txtmail'];
			
			$txt = DB_escape_string($txt);
			// chr(13).chr(10)
			$diagonal = "\\\\";
			$diagonal = substr($diagonal, 0,2);
			$diagonal = $diagonal."r";
			$diagonal2 = "\\\\";
			$diagonal2 = substr($diagonal2, 0,2);
			$diagonal = $diagonal.$diagonal2."n";
			$txt = str_replace($diagonal, chr(13).chr(10), $txt);

			//agregar pie de firma
			if ($_SESSION['PersonalSignatureInEmail']==1){
				$subject = " Cotizacion :$transno.";
				
				$qry = "SELECT name FROM debtorsmaster WHERE debtorno = '$debtorno'";
				$res = DB_query($qry,$db);
				$rows = DB_fetch_array($res);
				$cliente = str_replace(" ","_",$rows[0]);				
				$filename = "Cotizacion_".$transno."_".$cliente;
				
				//datos para la firma
				$qry = "SELECT legalname,
						       telephone,
						       comments,
						       legalbusinessunit.address6,
						       tags.legalid
						FROM legalbusinessunit
						INNER JOIN tags ON tags.legalid = legalbusinessunit.legalid
						AND tags.tagref = $tagref";
				$res = DB_query($qry,$db);
				$rows = DB_fetch_array($res);
				$phone = $rows['telephone'];
				$legalname = $rows['legalname'];
				$comments= $rows['comments'];
				$paginav=$rows['address6'];
				$legalid=$rows['legalid'];
				
				$txt.=chr(13).chr(10).chr(13).chr(10);
				
				$txt.= $realname." | ". $department.chr(13).chr(10).chr(13).chr(10);
				$txt.= $legalname.chr(13).chr(10).chr(13).chr(10);
				$txt.= $emailFrom.chr(13).chr(10).chr(13).chr(10);
				$txt.= "Oficina: ".$phone.chr(13).chr(10).chr(13).chr(10);
				$txt.= $comments.chr(13).chr(10);
				$txt.=$paginav;
			}
			

			/*
			$emailFrom = $correo;
			$mail_attch = mail_attachment($pdfcode, $correo, $emailFrom, $fromName, $emailFrom, $subject, $txt,$filename);
			if($mail_attch==true){
				prnMsg(_('No se encontraron errores'), 'success');
				prnMsg(_('El correo ha sido enviado a los siguientes destinatarios: ' . implode($_POST['emails'], ',')), 'success');
			}else{
				prnMsg(_('Hay errores'), 'Error');
				prnMsg(_('No se envio el correo a los siguientes destinatarios: ' . implode($_POST['emails'], ',')), 'err');
			}
			*/

			// las cabeceras del correo que incluye quien envia el correo que en este caso es el que lleno el formulario

			$direcciondos="./companies/".$_SESSION['DatabaseName']."/SAT/".str_replace(',','',str_replace('.','',str_replace(' ','',$legalname)))."/";
			$direcciondos=$direcciondos.'XML/' . $filename . '';

			if($_SESSION['UserID']=="desarrollo"){
				echo '<pre><br>direcciondos:'.$direcciondos;
				echo '<br>mmail:'.$mmail;
				echo '<br>emailFrom:'.$emailFrom;
				echo '<br>txt:'.$txt;
				echo '<br>filename:'.$filename;
			}

			$gestor = fopen($direcciondos, "w");
						fwrite($gestor, $pdfcode);
						fclose($gestor);

			$cabeceras = "From:  <$mmail>\n";
			$cabeceras .= "Reply-To: " . $emailFrom . "\n";
			//el tipo de correo a enviar que es de texto y datos 
			$cabeceras .= "MIME-version: 1.0\n";
			$cabeceras .= "Content-type: multipart/mixed; ";
			$cabeceras .= "boundary=\"Message-Boundary\"\n";
			$cabeceras .= "Content-transfer-encoding: 7BIT\n";
			// aqui el archivo adjunto que contendra
			
			$cabeceras .= "X-attachments: $filename";
			$body_top = "--Message-Boundary\n";
			$body_top .= "Content-type: text/plain; charset=iso-8859-1\n";
			$body_top .= "Content-transfer-encoding: 7BIT\n";
			$body_top .= "Content-description: Mail message body\n\n";
			 // aqui unimos las varibles $mensaje y $body_top en una sola 
			//$cuerpo = $txt;
			$cuerpo = $cabeceras . $body_top . $txt;
			$nombref= $filename;
			
			/*
			$direcciondosPFD=$archivodos;
			set_time_limit(600);
			$archivo= $archivoPDF;
			$buf_type= 'application/pdf';//obtener_extencion_stream_archivo($archivoPDF); //obtenemos tipo archivo
			$fp= fopen( $archivoPDF, "r" ); //abrimos archivo
			$buf= fread( $fp, filesize($archivo) ); //leemos archivo completamente
			fclose($fp); //cerramos apuntador;
			//Archivo PDF
			$archivoPDFEnvio=$serie.$folio.".pdf";
		
			prnMsg(_('Construyendo correo... ' ), 'info');
		
			$attachments[] = array(
									'archivo' => $direcciondos,
									'nombre'  => $nombre,
									'encoding'=> 'base64',
									'type'    => 'application/pdf'
							);
			*/
			
			//echo "<br>" . $pdfcode;
			//echo "<br>" . $nombre_archivo;
			//exit;

			$attachments[] = array(
									'archivo' => $direcciondos,
									'nombre'  => $nombre_archivo,
									'encoding'=> 'base64',
									'type'    => 'application/pdf'
							);
			//*/


			//$emailFrom = $correo;
			//$mail_attch = mail_attachment($pdfcode, $correo, $emailFrom, $fromName, $emailFrom, $subject, $txt,$filename);
			$subject = " Cotizacion :$transno.";
			
			$from 		= $emailFrom;
			$to 		= $correo;
			$serdername = $fromName;
			$subject	= $subject;
			$message 	= $txt;
			$reply_to 	= $emailFrom;
			
			/*
			prnMsg(_('To:' . $to), 'info');
			prnMsg(_('From:' . $from), 'info');
			prnMsg(_('ReplayTo:' . $rowmail['email']), 'info');
			prnMsg(_('Name:' . $senderName), 'info');
			prnMsg(_('Subject:' . $subject), 'info');
			prnMsg(_('MSG:' . $mensaje), 'info');
			prnMsg(_('Attach:' . $direcciondos), 'info');
			prnMsg(_('Attach:' . $pagina), 'info');
			*/
			if(!isset($funcion)){
				$funcion=152;
			}
			
			$qry = "SELECT tags.legalid
					FROM legalbusinessunit
						INNER JOIN tags ON tags.legalid = legalbusinessunit.legalid AND tags.tagref = '".$tagref."'";
				$res = DB_query($qry,$db);
				$rows = DB_fetch_array($res);
				$legalid=$rows['legalid'];
			$debug = 0;

		    if($_SESSION['UserID'] == 'desarrollo' or $_SESSION['UserID'] == 'admin') {
		        $debug = 1;
		    }
		    if($_SESSION['UserID']=="desarrollo"){
		    	
				echo '<pre><br>from:'.$from;
				echo '<br>to:'.$to;
				echo '<br>message: '.$message;
				echo '<br>legalid: '.$legalid;
				echo '<br>serdername: '.$serdername;
				echo '<br>subject: '.$subject;
				echo '<br>funcion: '.$funcion;
				echo '<br>reply_to: '.$reply_to;
			}
			if(!send_email($legalid,$db,$to,$from,$serdername,$subject,$message,$funcion,1,$reply_to,$attachments)){
				if ($Ocultar == 1){
					echo "<h4>Ocurrio un error</h4>";
					echo "<h4>Al enviar correo</h4>";
					echo "<h4>".implode($to, ',')."</h4>";
				}else{
					prnMsg(_('El correo No fue enviado ' . implode($to, ',')), 'error');
				}
			}
		}
		
	} else {
		prnMsg(_('Seleccione por lo menos un correo a los siguientes destinatarios: ' . implode($_POST['emails'])), 'err');
	}
}
?>

<style type="text/css">
form {
	margin: .5em;
	margin: 0 auto;
	text-align: center;
	padding: 2em;
}
#emails_div {
	overflow: auto; 
	width: 300px; 
	height: 100px; 
	background-color: #999;
	margin: 1em auto;
	text-align: left;
}
.email_div {
	background-color: #666; 
	margin: .3em; 
	padding: .3em; 
	color: #fff;
}
</style>

<script type="text/javascript">
function checkAll(field) {
	for(var i = 0; i < field.length; i++) {
		field[i].checked = true;
	}
}

function uncheckAll(field) {
	for (var i = 0; i < field.length; i++) {
		field[i].checked = false;
	}
}
</script>

<?php if ($Ocultar == 0): ?>
	
	<form action="<?php echo $_SERVER["PHP_SELF"] . "?" . SID; ?>" method="post">

		 <br><br>
			<div class="col-md-3">
			
			</div>

			<div class="col-md-3">
						<div class="form-inline row">
							<div class="col-md-3">
								<span><label for="tipocotizacion">Tipo Cotizacion:</label></span>
							</div>
							<div class="col-md-9">
							<select name="tipocotizacion"  onchange="javascript:document.forms[0].submit();"/ class="form-control selectGeneral">
								<option <?php if($_POST['tipocotizacion']==2) echo "selected";?> value="2">Simple</option>
	    						<option <?php if($_POST['tipocotizacion']==3) echo "selected";?> value="3">Compleja</option>
							</select>
							</div>
						</div>
			</div>
			<div class="col-md-3">
			<component-text-label for="email" label="Email: "  type="text" name="email" value="<?php echo $email; ?>" placeholder="Email"></component-text-label>      
			</div>
			
			<div class="col-md-3">
			
			</div>

			<div class="row"></div>
			<br><br>
        	<div align="center">
			<component-button type="submit" value="Agregar" name="Agregar"></component-button>
			</div>
		<!--<input type="text" name="email" value="<?php //echo $email; ?>" />
		<input type="submit" value="Agregar" name="Agregar" />!-->
		<br><br>
		<div id="emails_div">
		<?php while($row = DB_fetch_array($emails)) { ?>
			<div class="col-md-3">
				<input type="checkbox" value="<?php echo $row['email']; ?>" name="emails[]" /> <?php echo $row['email']; ?>
			</div>
		<?php } ?>
		</div>
		
		<div class="col-md-4" style="margin-left: 30%; margin-top: 3%;">
		<component-textarea-label label="Texto del Mensaje: " rows="5" cols="60" name="txtmail"></component-textarea-label><br><br>
		</div>
		
		<?php
		$arraydbs = array("erpgosea" => "erpgosea",
									"erpgosea_CAPA" => "erpgosea_CAPA",
									"erpgosea_DES" => "erpgosea_DES");
		if(in_array($_SESSION['DatabaseName'], $arraydbs) == true){
		?>
			<div>
				<label >Ocultar Precios:&nbsp;</label>
				<input type="checkbox" name="hidepricexdocto" value="1"><br><br>
			</div>
		<?php
		}
		?>

		<input type="hidden" value="<?php echo $tagref; ?>" name="tagref" />
		<input type="hidden" value="<?php echo $transno; ?>" name="transno" />
		<input type="hidden" value="<?php echo $debtorno; ?>" name="debtorno" />
		<input type="hidden" value="<?php echo $legalid; ?>" name="legalid" />
		
		
		<div class="row"></div>
        <div align="center">
		<component-button type="button" value="Seleccionar Todos" name="Seleccionar" onclick="checkAll(document.getElementsByName('emails[]'))"/></component-button>
		<component-button type="button" value="Deseleccionar Todos" name="Deseleccionar" onclick="uncheckAll(document.getElementsByName('emails[]'))"/></component-button>
		<component-button type="submit" value="Enviar" name="Enviar" /></component-button>
		</div>
		
		
	</form>
<?php endif ;

function send_email($legalid,$db1,$to,$from,$serdername,$subject,$message,$funcion_id=152,$debug=0,$reply_to="",$attachments = "",$message_erp="",$message_erp_error=""){
	/* ini_set('display_errors', 1);
	error_reporting(E_ALL); */
	/* 
	 * send_email($legalid,$db,$to,$from,$serdername,$subject,$message[,$funcion_id,$debug,$reply_to,$attachments,$message_erp,$message_erp_error]);
	 * donde:
	 * $legalid  	- id de legalbusiness
	 * $db 			- conexion de base de datos degault
	 * $to 			- remitente a quien va dirigido el email, separado por comas
	 * $from		- email del que envia , se utilizara para responder en caso que no se especifique un $reply_to
	 * $serdername 	- nombre del emisor
	 * $subject		- Titulo del email
	 * $message		- Mensaje
	 * $funcion_id	- id de la pagina utilizada, se enviará conforme a la configuracion de la empresa y modufo
	 * [opcionales]
	 * $debug		- So se manda = 1 se mostrará el detalle de la conexion (habilitar solamente para rastrear errores)
	 * $reply_to	- email al que se espera la respuesta
	 * $attachments - Array de adjuntos y tipos de datos
	 * $message_erp	- Mensaje a mostrar al enviar el email , como complemento cuando se envia de forma correcta
	 * $message_erp_error	- Mensaje a mostrar al enviar el email , como complemento cuando hay error en el envio
	 
	 $attachments[] = array(
	 'archivo' => $archivoPDF,
	 'nombre'  => $nombre,
	 'encoding'=> 'base64',
	 'type'    => 'application/pdf'
	 );

	 $attachments[] = array(
	 'archivo' => $archivoXML,
	 'nombre'  => $nombre,
	 'encoding'=> 'base64',
	 'type'    => 'application/xml'
	 );
	 
	 Para configuración en aplicación 
	 	Servidor SMTP:
		Puerto:
		Tipo de cifrado: (ssl / tls )
		Requiere autenticación ?: (si/no)
		Usuario: 
		Contraseña :
	Ingresar registro en tabla:
		sec_submodules_email_methods
	
	 */

		global $db;
		/* echo "<pre><br/><br/>Misc_functions.php";
		echo "<br/>Dspues de:";
		echo "<br/><br/>legal: "; var_dump($legalid);
		echo "<br>db: "; var_dump($db);
		echo "<br>to: "; var_dump($to);
		echo "<br>from: "; var_dump($from);
		echo "<br>serdername: "; var_dump($serdername);
		echo "<br>subject: "; var_dump($subject);
		echo "<br>message: "; var_dump($message);
		echo "<br>functionid: "; var_dump($funcion_id);
		echo "<br>debug; "; var_dump($debug);
		echo "<br>reply_to: "; var_dump($reply_to);
		echo "<br>attachments: "; var_dump($attachments);
		echo "<br>message_erp: "; var_dump($message_erp);
		echo "<br>message_erp_error: "; var_dump($message_erp_error); */
	
	$to_original=$to;
	$requisitos_error=false;
	$errors=array();
	{  // --- Verificación de requisitos --- //
		$tipo=gettype($to);
		$c_emails_orig=0;
		$c_emails_validos=0;
		$c_emails_invalidos=0;
		//verificación de tipo de campo de to
		switch($tipo){
			case "array":
				{
					$to_tmp="";
					$c_emails_orig2=0;
					foreach ($to_original as $email) {
						$c_emails_orig2++;
						if($c_emails_orig2!=1)
						{
							$to_tmp.=",";
						}
						$to_tmp.=$email;
					}
					$to_original=$to_tmp;
					
					$to_tmp="";
					foreach ($to as $email) {
						$c_emails_orig++;
						
						if(isValidEmail($email)){
							$c_emails_validos++;
							if($c_emails_validos!=1)
							{
								$to_tmp.=",";
							}
							$to_tmp.=$email;
						}else{
							$errors[]="Email incorrecto: '".$email."'";
							$c_emails_invalidos++;
						}
					}
					$to=$to_tmp;
				}
				break;
			case "string":
				{
					$to=$to_original;
				}
				break;
		}
			
		if($c_emails_orig==$c_emails_invalidos && $c_emails_invalidos!=0){
			$errors[]="Todos Los email enviados son invalidos";
			$requisitos_error=true;
		}elseif($c_emails_orig!=$c_emails_validos){
			$errors[]="Uno o varios de email enviados son Invalidos '".$to_original."'";
			prnMsg("Uno o varios de email enviados son Invalidos '".$to_original."'","warn");
		}
	}
	
	if(!$requisitos_error){
		try{
			//require_once("PHPMailer/class.phpmailer.php");
			//require_once('PHPMailer/class.smtp.php');	
			include_once("includes/class.phpmailer.php");
            
			$mail = new PHPMailer();

			$secFunctionTable = "sec_functions";
			if (!empty($_SESSION['SecFunctionTable'])) {
				$secFunctionTable = $_SESSION['SecFunctionTable'];
			}
		
			//$SQL="SELECT submoduleid FROM ".$secFunctionTable." WHERE functionid='".$funcion_id."' ";
			$SQL="SELECT * FROM ".$secFunctionTable." WHERE functionid='".$funcion_id."' ";

			//echo "<br/> sql funcion $SQL";



			$result = DB_query($SQL,$db);
			//echo 
			$rows = mysqli_num_rows($result);
			 
			$funcion_encontrada=DB_fetch_array($result);
			if(DB_num_rows($result) > 0 || $funcion_id == 9999.99 || $funcion_id == "9999.99"){ 

		    
				$SQL="SELECT
                        metodo,cifrado,desde,requiere_autenticacion,servidor,puerto,usuario,contrasena ,
                        sem.id_metodo,sem.submoduleid,
                        e.id_smtp
                        FROM legalbusiness_email_methods e
                        JOIN sec_submodules_email_methods sem ON e.id_smtp=sem.id_smtp
                        WHERE legalid='".$legalid."' 
                                and (submoduleid='".$funcion_encontrada['submoduleid']."' or submoduleid = -1)";
               //echo "<br>SQL001; ".$SQL;
				//prnMsg($SQL,"info");;
				$rs = DB_query($SQL,$db);
				$metodos=DB_fetch_array($rs);
		
				//Se establece el metodo.
				//Si existe metodo por mmódulo lo utiliza
				//Si no busca metodo global por empresa
				//Si no utiliza metodo base de portalito 
				if(DB_num_rows($rs)>0){
					//Faltan por especificar: desde,requiere_autenticacion
					$mail->setmetod($metodos['metodo'],$metodos['servidor'],$metodos['puerto'],$metodos['usuario'],$metodos['contrasena'],$metodos['cifrado'],$metodos['desde'],$metodos['requiere_autenticacion'],30);
			
				}else{
					$SQL="SELECT metodo,cifrado,desde,requiere_autenticacion,servidor,puerto,usuario,contrasena
						FROM legalbusiness_email_methods e
						WHERE legalid='".$legalid."' and metodo='smtp_all'";
					$rs = DB_query($SQL,$db);
					$metodos=DB_fetch_array($rs);
					if(DB_num_rows($rs)>0){
                        //Faltan por especificar: desde,requiere_autenticacion
                        $mail->setmetod($metodos['metodo'],$metodos['servidor'],$metodos['puerto'],$metodos['usuario'],$metodos['contrasena'],$metodos['cifrado'],$metodos['desde'],$metodos['requiere_autenticacion'],30);
					}else{
						$mail->setmetod("smtp_base", "",25,"","","","","",30);
					}
				}
				
				//array_unique(explode ( ',', $to ));
				$emails_ = explode ( ',', $to );
				foreach($emails_ as $email_address){
                    if($email_address!=""){
                        if(isValidEmail($email_address))
                        {
                          //  prnMsg("email ".$email_address,"info");
                            $mail->AddAddress($email_address); // Dirección a la que llegaran los mensajes.

                        }else{
                            prnMsg("Error de email ".$email_address,"error");
                        }	
                    }
				}
		
				// Aquí van los datos que apareceran en el correo que reciba ,,,
				$mail->CharSet     	= 'UTF-8';
				$mail->WordWrap 	= 50;
				$mail->IsHTML(true);
				//$mail->From     	= $from; //Dirección desde la que se enviarán los mensajes. Debe ser la misma de los datos de el servidor SMTP.
				$mail->FromName 	= $serdername;
				$mail->Subject  	= $subject;
				$mail->Body     	= $message;
				$mail->SMTPDebug 	= $debug;           
		
				if(isValidEmail($reply_to)){
					$mail->AddReplyTo($reply_to, $serdername);
				}else {
					if(isValidEmail($from)){
						$mail->AddReplyTo($from, $serdername);
					}
				}
				
				if(is_array($attachments))
				{
					
					foreach($attachments as $adjunto)
					{
						if(file_exists($adjunto['archivo']))
						{
							if(!$mail->AddAttachment($adjunto['archivo'], $adjunto['nombre'])) {
								prnMsg("No se pudo adjuntar el adjuntar el archivo: '".$adjunto['archivo']."'","error");	
							}

						}else{
							prnMsg("No se puede adjuntar archivo al email, el archivo no existe '".$adjunto['archivo']."'","error");
						}
					}
				}
		
				if(count($errors)>0){
					$errores_desc="";
					foreach($errors as $error){
						$errores_desc.="<br/>* - ".$error;
					}
					prnMsg("-- Errores en envio de email --".$errores_desc,"warn");
				}
				
				if ($mail->Send())
				{
					prnMsg("Email enviado: '".implode($emails_, ',')."' ".$message_erp,"success");
				}else{
					prnMsg("Error en envio de email a: '".implode($emails_, ',')."' ".$message_erp_error,"error");
					prnMsg("ErrorInfo <br/>".$mail->ErrorInfo);
					if("SMTP Error: Could not connect to SMTP host."==$mail->ErrorInfo){
						prnMsg("Timeout:".$mail->Timeout);
					}
					unset($mail);
					if(isset($_SESSION['SMTP_LOG']) && $_SESSION['SMTP_LOG']=="true"){
						$SQL="INSERT into legalbusiness_email_log 
								(id_smtp,desde,para,cc,cco,estado,smtp_server,smtp_msg,functionid,userid,fecha_registro) 
								values('".$metodos['id_smtp']."',".
										"'".$metodos['usuario']."',".
										"'".$emails_."','','',".
										"'ERROR',".
										"'".$metodos['servidor']."',".
										"'".$mail->ErrorInfo."',".
										"'".$funcion_id."',".
										"'".$_SESSION['UserID']."',".
										"now() )";
						$rs_email_log = DB_query($SQL,$db);
					}
					//$metodos['id_smtp'],$metodos['servidor'],$metodos['puerto'],$metodos['usuario'],$metodos['contrasena'],$metodos['cifrado'],$metodos['desde'],$metodos['requiere_autenticacion']
					return false;
				}
			}else{
				prnMsg("Esta Funcion (".$funcion_id.") no tiene privilegios de envio, el email no se ha enviado","error");
				unset($mail);
				return false;
			}
			unset($mail);
			return true;
		} catch (phpmailerException $e) {
		    $errors[] = $e->errorMessage(); //Pretty error messages from PHPMailer
		    foreach($errors as $error){
		    	prnMsg("Fallo en envio: '".$error."'","warn");
		    }
		} catch (Exception $e) {
		    $errors[] = $e->getMessage(); //Boring error messages from anything else!
			foreach($errors as $error){
		    	prnMsg("Fallo en envio: '".$error."'","warn");
		    }
		}
	}else{
		$errores_desc="";
		foreach($errors as $error){
			$errores_desc.="<br/>* - ".$error;
		}
		prnMsg("-- Errores en envio de email --".$errores_desc,"error");
		return false;
	}
}

function isValidEmail($email){
	$pattern = "/^([a-zA-Z0-9])+([a-zA-Z0-9\._\+-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/";

	if (preg_match($pattern, $email)){
		return true;
	}
	else {
		return false;
	}
}



?>



<?php
include 'includes/footer_Index.inc';
?>