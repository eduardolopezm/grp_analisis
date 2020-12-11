<?php
// //
// //
// /*
//  * desarrollo - 23/AGOSTO/2011 - Modifique formato para impresion de cheques
//  */
// /*error_reporting(E_ALL);
// ini_set('display_errors', 1);*/

// $PageSecurity = 1;
// $RightMargin =0;
// require "includes/SecurityUrl.php";
// include('includes/session.inc');
// $funcion = 210;
// include('includes/SecurityFunctions.inc');
// include('includes/SQL_CommonFunctions.inc');
// include('Numbers/Words.php');

// if (isset($_GET ['TransNo'])) {
//     $TransNo = $_GET ['TransNo'];
//     $TransNo = str_replace("_", ",", $TransNo);
// }
//     $Tno = explode(",", $TransNo);
//     $newTrans = "";
// for ($i=0; $i <count($Tno); $i++) {
//     if ($i == 0) {
//         $newTrans .= "gltrans.typeno = '".$Tno[$i]."'";
//     } else {
//         $newTrans .= " OR gltrans.typeno = '".$Tno[$i]."'";
//     }
// }

// if (isset($_GET ['type'])) {
//     $type = $_GET ['type'];
// }

// $folioempresa="";
// if (isset($_GET ['folio'])) {
//     if ($_GET['folio']) {
//         $folioempresa = $_GET ['folio'];
//     }
// }

// $sql = "SELECT typeid,
// 		       typename
// 		FROM systypescat
// 		WHERE typeid = '" . $type . "'";

// $SResults = DB_query($sql, $db, "");
// if ($myrow = DB_fetch_array($SResults)) {
//     $typename = $myrow ['typename'];
// }

// if (!isset($_GET['periodno'])) {
//     $_GET['periodno'] = "";
// }

// $periodo = $_GET ['periodno'];
// //consulta  vieja que venia por default
// /*$sql = "SELECT bankact,
// 		       ref,
// 		       exrate,
// 		       transdate,
// 		       banktranstype,
// 		       banktrans.amount AS amount,
// 		       banktrans.currcode,
// 		       banktrans.tagref,
// 		       beneficiary,
// 		       banktrans.chequeno,
// 		       periodno,
// 		       legalname,
// 		       tagdescription AS tagname,
// 		       Day(trandate) AS dia,
// 		       Month(trandate) AS mes,
// 		       Year(trandate) AS year,
// 		       trandate,
// 		       bankaccountnumber, bankaccountname,
// 		       narrative
// 		FROM banktrans
// 		LEFT JOIN gltrans ON banktrans.bankact = gltrans.typeno
// 		AND banktrans.type = gltrans.type
// 		AND banktrans.transno = gltrans.typeno
// 		LEFT JOIN tags ON gltrans.tag = tags.tagref
// 		LEFT JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
// 		LEFT JOIN bankaccounts ON banktrans.bankact = bankaccounts.accountcode
// 		WHERE " . $newTrans . "
// 		  AND gltrans.type = '" . $type . "'"; */

// //tipo  20
// $sql = "SELECT bankact,
//                ref,
//                exrate,
//                transdate,
//                banktranstype,
//                banktrans.amount AS amount,
//                banktrans.currcode,
//                banktrans.tagref,
//                beneficiary,
//                banktrans.chequeno,
//                periodno,
//                legalname,
//                tagdescription AS tagname,
//                Day(trandate) AS dia,
//                Month(trandate) AS mes,
//                Year(trandate) AS year,
//                trandate,
//                bankaccountnumber, bankaccountname,
//                narrative
//         FROM banktrans
//        /* LEFT JOIN gltrans ON banktrans.bankact = gltrans.account */

//         LEFT JOIN gltrans ON banktrans.transno = gltrans.typeno
//         AND banktrans.type = gltrans.type
//         AND banktrans.transno = gltrans.typeno
//         LEFT JOIN tags ON gltrans.tag = tags.tagref
//         LEFT JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
//         LEFT JOIN bankaccounts ON banktrans.bankact = bankaccounts.accountcode
//         WHERE " . $newTrans . "
//           AND gltrans.type = '" . $type . "'
//             GROUP BY gltrans.typeno ";

// $arraybase = array (
//         "erpmg" => "erpmg",
//         "erpmg_DES" => "erpmg_DES",
//         "erpmg_CAPA" => "erpmg_CAPA"
// );
// if (in_array($_SESSION ['DatabaseName'], $arraybase) == true) {
//     $sql = $sql . " and (transno in (" . $TransNo . ") or INSTR(ref,'" . $TransNo . " ') > 0 or INSTR(beneficiary,'" . $TransNo . " ') > 0)";
// } else {
//     $sql = $sql . " and transno in(" . $TransNo . ")";
// }
// //
// if ($_SESSION ['UserID'] == "saplicaciones") {
//     //echo '<pre>'.$sql;
// }

// $flagcheque = 0; //
// $mes = array (
//         1 => 'Enero',
//         2 => 'Febrero',
//         3 => 'Marzo',
//         4 => 'Abril',
//         5 => 'Mayo',
//         6 => 'Junio',
//         7 => 'Julio',
//         8 => 'Agosto',
//         9 => 'Septiembre',
//         10 => 'Octubre',
//         11 => 'Noviembre',
//         12 => 'Diciembre'
// );
// //echo '<pre>'.$sql."<br>".;
// $SResults = DB_query($sql, $db, "");
// if ($myrow = DB_fetch_array($SResults)) {
//     $bankaccount = $myrow ['bankact'];
//     $reference = $myrow ['ref']; //
//     $exchrate = $myrow ['exrate'];
//     $transdate = $myrow ['transdate'];
//     $tipomovimiento = $myrow ['banktranstype'];

//     $monto = $myrow ['amount'];
//     $currcode = $myrow ['currcode'];
//     $tagref = $myrow ['tagref'];
//     $beneficiary = $myrow ['beneficiary'];
//     $chequeno = $myrow ['chequeno'];
//     if (! isset($_GET ['periodno'])) {
//         $periodo = $myrow ['periodno'];
//     }
//     // //
//     $rsocial = $myrow ['legalname'];
//     $unnegocio = $myrow ['tagname'];
//     $trandate = $myrow ['trandate'];
//     $fecha = $myrow ['dia'] . ' de ' . $mes [$myrow ['mes']] . ' del ' . $myrow ['year'];
//     $numerocuenta = $myrow ['bankaccountnumber'];
//     $nombrebanco = $myrow ['bankaccountname'];
//     $narrative = $myrow ['narrative'];
//     $flagcheque = 1;
// }

// include('includes/PDFStarter.php');

// // echo $_POST['FromCust'];

// $pdf->addinfo('Title', _('Impresion de Poliza Cheque'));
// $pdf->addinfo('Subject', _('Poliza Cheque Tipo:') . ' ' . $typename . ' ' . _(' Folio:') . ' ' . $TransNo);
// $PageNumber = 1;

// $line_height = 16;

// $FirstStatement = true; //
// $sql = "SELECT t.tagdescription,
// 		       c.accountname AS account,
// 		       c.accountcode,
// 		       sum(o.amount) AS amount,
// 		       o.narrative,
// 		       l.legalname,
// 		       l.logo,
// 		       o.periodno
// 		FROM gltrans o,
// 		     tags t,
// 		     chartmaster c,
// 		     legalbusinessunit l
// 		WHERE t.tagref=o.tag
// 		  AND c.accountcode=o.account
// 		  AND t.legalid=l.legalid
// 		  AND o.type = " . $type . "
// 		  AND o.typeno IN (" . $TransNo . ")
// 		GROUP BY c.accountcode";

// $StatementResults = DB_query($sql, $db, "");
// $NumberOfRecordsReturned = DB_num_rows($StatementResults);

// //
// if (DB_Num_Rows($StatementResults) == 0) {
//     $title = _('Imprime sentencias') . ' - ' . _('No polizas encontradas');

//     require('includes/header.inc'); //
//     echo '<div class="centre"><p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Print') . '" alt="">' . ' ' . _('Impresion de Poliza Cheque') . '';
//     prnMsg(_('No existen polizas que correspondan con la seleccion') . $type . '/' . $TransNo, 'error');
//     echo '</div>';
//     include('includes/footer.inc');
//     exit();
// }

// $PageNumber = 1;
// if ($FirstStatement == true) {
//     $FirstStatement = false;
// } else {
//     $pdf->newPage();
// }

// // *** HEADER INICIO ///
// if ($PageNumber > 1) {
//     $pdf->newPage();
// }
// $Perforation = $Page_Width - $RightMargin - 200;
// $Top_Margin = 200;
// $FontSize = 10;
// $YPosCHQ = $Page_Height - 50;
// if ($flagcheque == 1) {
//     // $pdf->addText($Left_Margin+60, $YPosCHQ-10,$FontSize, $chequeno);
//     $pdf->addText($Left_Margin + 385, $YPosCHQ - 1, $FontSize, $fecha);

//     $pdf->addText($Left_Margin - 5, $YPosCHQ - 45, $FontSize, $beneficiary);
//     $pdf->SetFont('helvetica', 'B', 10);
//     $pdf->addText($Left_Margin + 440, $YPosCHQ - 48, $FontSize, number_format(abs($monto), 2));
//     $pdf->SetFont('helvetica', '', 10);
//     // $pdf->addText($Left_Margin+60, $YPosCHQ - 55,$FontSize, 'CANTIDAD CON LETRA');//
//     $separa = explode(".", $monto);
//     $montoletra = $separa [0];
//     $separa2 = explode(".", number_format($monto, 2)); //
//     $montoctvs2 = $separa2 [1];

//     $objNumbers = new Numbers_Words(); // objeto de la Clase Numbers_Words

//     if ($currcode=='USD') {
//         //$montoletra=Numbers_Words::toWords($montoctvs1,'en_US');
//         $montoletra = $objNumbers->toWords(abs($montoletra), 'es');
//     } else {
//         $montoletra = $objNumbers->toWords(abs($montoletra), 'es');
//     }

//     if ($currcode=='USD') {
//         $pdf->addText($Left_Margin - 5, $YPosCHQ - 80, $FontSize, strtoupper(ucwords($montoletra)) . ' DOLARES ' . $montoctvs2 . '/100 USD');
//     } else {
//         $pdf->addText($Left_Margin - 5, $YPosCHQ - 80, $FontSize, strtoupper(ucwords($montoletra)) . ' PESOS ' . $montoctvs2 . '/100 M.N.');
//     }
//     // $pdf->Image( $_SESSION['LogoFile'] ,$Left_Margin,$YPos + 25,0,30);//

//     // Title
// }
// $YPos = $Page_Height - $Top_Margin;

// // $pdf->addJpegFromFile($_SESSION['LogoFile'],$Left_Margin,$YPos + 25,0,30);

// $FontSize = 15;
// $XPos = $Page_Width / 2 - 110;
// // $pdf->addText($Left_Margin+60, $YPos - 13,$FontSize, _('POLIZA CHEQUE') );//

// // $LeftOvers = $pdf->addTextWrap($Left_Margin+60,$YPos + 5,600,$FontSize,strtoupper($rsocial));

// $FontSize = 12;

// $YPosR = $YPos;
// $FontSize = 10;
// $LineHeight = 13;
// $LineCountR = 0;
// $Remit1 = $Perforation + 2;

// $XPos = $Left_Margin;

// $LineHeight = 13;
// $XPos = $Left_Margin;
// $YPos = $Page_Height - $Top_Margin - 40;

// $LineCount = 0;
// $FontSize = 7;

// $sqlperiods = "SELECT month(lastdate_in_period) AS mes
// 				FROM periods
// 				WHERE periodno='" . $periodo . "'";
// $Resultperiods = DB_query($sqlperiods, $db, "");
// if ($myrowP = DB_fetch_array($Resultperiods)) {
//     $mesperiodo = add_ceros($myrowP ['mes'], 2);
// }
// $rsocial = $myrow ['legalname'];
// $unnegocio = $myrow ['tagname'];
// $arrnarrative = explode('||', $narrative);
// $narrative = $arrnarrative[0];
// $pdf->addText(10, $YPos - 55, $FontSize, $narrative);
// $pdf->addText(10, $YPos - 65, $FontSize, _('CHEQUE') . ':' . $chequeno);
// $pdf->addText(10, $YPos - 75, $FontSize, 'Dependencia: ' . $rsocial);
// $pdf->addText(10, $YPos - 85, $FontSize, 'UR:' . $unnegocio);
// if ($_SESSION ['DatabaseName'] == "erpgosea" or $_SESSION ['DatabaseName'] == "erpgosea_CAPA" or $_SESSION ['DatabaseName'] == "erpgosea_DES") {
//     if ($folioempresa!="") {
//         $pdf->addText(10, $YPos - 95, $FontSize, 'Folio Empresa:' . $folioempresa);
//     }
// }
// /*
//  * $pdf->addText($XPos, $YPos-$LineCount*$LineHeight,$FontSize, _('Periodo').': ' . $periodo. _(' - Mes').': '.$mesperiodo); $LineCount += 1; $pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, _('Cuenta').': ' . $nombrebanco . '/'. $numerocuenta); $LineCount += 1; $FontSize = 8; $pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, _('Fecha').': ' . $trandate); $LineCountR += 3; $pdf->addText($Perforation+2, $YPosR-$LineCountR*$LineHeight,$FontSize, _('Poliza no').':' . $TransNo); $LineCountR += 1; $pdf->addText($Perforation+2, $YPosR-$LineCountR*$LineHeight,$FontSize, _('CHEQUE').':' . $chequeno); $pdf->addText($Perforation+2, $YPos-$LineCount*$LineHeight, $FontSize, _('Tipo Poliza').': ' . $typename);
//  */

// $YPos = $Page_Height - $Top_Margin - 70;
// $XPos = $Left_Margin;

// // $pdf->line($Left_Margin, $YPos-16,$Left_Margin, $Bottom_Margin+10);
// // $pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+10,$Page_Width-$Right_Margin, $YPos-16);

// $YPos -= $line_height;
// $FontSize = 10;

// $YPos -= $line_height;
// $FontSize = 8;

// $YPos -= $line_height;

// /* draw a line */
// // $pdf->line($Page_Width-$Right_Margin, $YPos,$XPos, $YPos);

// $YPos -= $line_height;
// $XPos = $Left_Margin;
// // *** HEADER FINAL ///
// $TAbono = 0;
// $TCargo = 0;

// if ($NumberOfRecordsReturned > 0) {
//     while ($myrow = DB_fetch_array($StatementResults)) {
//         $rsocial = $myrow ['legalname'];
//         $FontSize = 7;

//         $LeftOvers = $pdf->addTextWrap($Left_Margin + 10, $YPos - 30, 110, $FontSize, $myrow ['accountcode'], 'left');
//         $LeftOvers = $pdf->addTextWrap($Left_Margin + 110, $YPos - 30, 170, $FontSize, $myrow ['accountcode'] . '  ' . $myrow ['account']);

//         if ($myrow ['amount'] > 0) {
//             $LeftOvers = $pdf->addTextWrap($Left_Margin + 395, $YPos - 30, 50, $FontSize, '$' . number_format($myrow ['amount'], 2), 'right');
//             $TCargo = $TCargo + $myrow ['amount'];
//         } else {
//             $LeftOvers = $pdf->addTextWrap($Left_Margin + 475, $YPos - 30, 50, $FontSize, '$' . number_format(($myrow ['amount'] * (- 1)), 2), 'right');
//             $TAbono = $TAbono + $myrow ['amount'];
//         }

//         $FontSize = 6;
//         // $LeftOvers = $pdf->addTextWrap($Left_Margin+380,$YPos,140,$FontSize,$myrow['narrative'], 'left');

//         $YPos -= ($line_height);

//         if ($YPos - (2 * $line_height) < $Bottom_Margin) {
//             /* Then set up a new page */
//             $PageNumber ++;

//             // *** HEADER INICIO ///
//             if ($PageNumber > 1) {
//                 $pdf->newPage();
//             }

//             $Perforation = $Page_Width - $RightMargin - 200;
//             $Top_Margin = 200;

//             $YPos = $Page_Height - $Top_Margin; //

//             // $pdf->addJpegFromFile($_SESSION['LogoFile'],$Left_Margin,$YPos - 5,0,30);
//                                                 // $pdf->Image( $_SESSION['LogoFile'] ,$Left_Margin,$YPos - 5,0,30);

//             // Title
//             $FontSize = 15;
//             $XPos = $Page_Width / 2 - 110;
//             $pdf->addText($Left_Margin + 60, $YPos - 13, $FontSize, _('POLIZA CHEQUE'));

//             $LeftOvers = $pdf->addTextWrap($Left_Margin + 60, $YPos + 5, 600, $FontSize, strtoupper($rsocial));

//             $FontSize = 12;

//             $YPosR = $YPos;
//             $FontSize = 10;
//             $LineHeight = 13;
//             $LineCountR = 0;
//             $Remit1 = $Perforation + 2;

//             $XPos = $Left_Margin;

//             $LineHeight = 13;
//             $XPos = $Left_Margin;
//             $YPos = $Page_Height - $Top_Margin - 40;

//             $LineCount = 0;
//             $FontSize = 8;

//             $sqlperiods = "SELECT month(lastdate_in_period) AS mes
// 							FROM periods
// 							WHERE periodno='" . $periodo . "'";
//             $Resultperiods = DB_query($sqlperiods, $db, $ErrMsg);
//             if ($myrowP = DB_fetch_array($Resultperiods)) {
//                 $mesperiodo = add_ceros($myrowP ['mes'], 2);
//             }

//             $pdf->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, $narrative);
//             /*
// 			 * $pdf->addText($XPos, $YPos-$LineCount*$LineHeight,$FontSize, _('Periodo').': ' . $periodo. _(' - Mes').': '.$mesperiodo); $LineCount += 2; $FontSize = 8; $pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, _('Fecha').': ' . $trandate); $LineCountR += 3; $pdf->addText($Perforation+2, $YPosR-$LineCountR*$LineHeight,$FontSize, _('Poliza no').':' . $TransNo); $pdf->addText($Perforation+2, $YPos-$LineCount*$LineHeight, $FontSize, _('Tipo Poliza').': ' . $typename);
// 			 */

//             $YPos = $Page_Height - $Top_Margin - 70;
//             $XPos = $Left_Margin;

//             // $pdf->line($Left_Margin, $YPos-16,$Left_Margin, $Bottom_Margin+10);
//             // $pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+10,$Page_Width-$Right_Margin, $YPos-16);

//             $YPos -= $line_height;
//             $FontSize = 10;

//             /* Set up headings */
//             // $pdf->line($Page_Width-$Right_Margin, $YPos,$XPos, $YPos);
//             $YPos -= $line_height;

//             $FontSize = 8;

//             $YPos -= $line_height;

//             /* draw a line */
//             // $pdf->line($Page_Width-$Right_Margin, $YPos,$XPos, $YPos);

//             $YPos -= $line_height;
//             $XPos = $Left_Margin;
//             // *** HEADER FINAL ///
//         } /* end of new page header */
//     }
// }

// // ////////////imprime totales
// // $pdf->line($Page_Width-$Right_Margin, $Bottom_Margin,$Left_Margin,$Bottom_Margin);
// $FontSize = 10;

// $YPos -= $line_height;
// $TAbonom = $TAbono * (- 1);
// $TAbono = number_format($TAbonom, 2);
// $TCargo = number_format($TCargo, 2);
// // $LeftOvers = $pdf->addTextWrap($Left_Margin+230,$YPos+100,60,$FontSize,'Total', 'left');
// $LeftOvers = $pdf->addTextWrap($Left_Margin + 395, 125, 50, $FontSize, '$' . $TCargo, 'right');
// $LeftOvers = $pdf->addTextWrap($Left_Margin + 475, 125, 50, $FontSize, '$' . $TAbono, 'right');
// // $LeftOvers = $pdf->addTextWrap($Left_Margin+330,$YPos,50,$FontSize,'$'.$TAbono * (-1), 'left');//////

// $YPos -= $line_height; //
// $FontSize = 8;

// // $pdf->addText($Left_Margin+85, $Bottom_Margin+(3*$line_height)+4, $FontSize, _('___________________________________'). ' ');
// // $pdf->addText($Left_Margin+300, $Bottom_Margin+(3*$line_height)+4, $FontSize, _('___________________________________'). ' ');

// $YPos -= $line_height;

// $FontSize = 8;

// // $pdf->addText($Left_Margin+150, $Bottom_Margin+(3*$line_height)-5, $FontSize, _('FIRMA'). ' ');
// // $pdf->addText($Left_Margin+350, $Bottom_Margin+(3*$line_height)-5, $FontSize, _('AUTORIZO'). ' ');

// $pdf->addText(15, 75, $FontSize, $TransNo);

// if (isset($pdf)) {
//     // Here we output the actual PDF file, we have given the file a name (this could perhaps be a variable based on the Customer name), and outputted via the "I" Inline method
//     $pdfcode = $pdf->output("Cheque.pdf", "I");
//     $len = strlen($pdfcode);
//     header('Content-type: application/pdf');
//     header('Content-Length: ' . $len);
//     header('Content-Disposition: inline; filename=ChequePoliza.pdf');
//     header('Expires: 0');
//     header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
//     header('Pragma: public');

//     $pdf->Stream();
// } else {
//     $title = _('Print Statements') . ' - ' . _('No Statements Found');
//     include('includes/header.inc');
//     echo '<br><br><br>' . prnMsg(_('There were no statements to print'));
//     echo '<br><br><br>';
//     include('includes/footer.inc');
// }


/**
 * Impresion Suficiencia ManuaL y Automática
 *
 * @category Pdf
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/11/2017
 * Fecha Modificación: 02/11/2017
 * Impresion Suficiencia ManuaL y Automática
 */

$PageSecurity = 1;
include('config.php');
include('includes/session.inc');
$PrintPDF = $_GET ['PrintPDF'];
$_POST ['PrintPDF'] = $PrintPDF;
include('jasper/JasperReport.php');
include("includes/SecurityUrl.php");

include('Numbers/Words.php');
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
$currcode="MXN";
$monto= $_GET["monto"];
if($monto<0){
	$monto=$monto*(-1);
}
$separa = explode(".", $monto);
$montoletra = $separa [0];
$separa2 = explode(".", number_format($monto, 2)); //

$pos = strpos($monto,".");

if ($pos == true) {
	$monto =$monto; //.($separa2 [1]);
	$decimales=($separa2 [1]);
}else{
	//number_format($myrow['ovamount'], $_SESSION['DecimalPlaces'], '.', ',');
	//$monto=//number_format($monto, $_SESSION['DecimalPlaces'], '.', '');
	$decimales=number_format(0, $_SESSION['DecimalPlaces'], '.', '');
	$decimales=explode(".",$decimales);
	$decimales=$decimales[1];
}

$objNumbers = new Numbers_Words(); // objeto de la Clase Numbers_Words

if ($currcode=='USD') {
    //$montoletra=Numbers_Words::toWords($montoctvs1,'en_US');
    $montoletra = $objNumbers->toWords(($montoletra), 'es');
} else {
    $montoletra = $objNumbers->toWords(($montoletra), 'es');
}

/*if ($currcode=='USD') {
    $pdf->addText($Left_Margin - 5, $YPosCHQ - 80, $FontSize, strtoupper(ucwords($montoletra)) . ' DOLARES ' . $montoctvs2 . '/100 USD');
} else {
    $pdf->addText($Left_Margin - 5, $YPosCHQ - 80, $FontSize, strtoupper(ucwords($montoletra)) . ' PESOS ' . $montoctvs2 . '/100 M.N.');
} */

$sqllogo = "SELECT legalbusinessunit.logo
FROM tags
INNER JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
WHERE tags.tagref = '".$_GET['ur']."'";
$resutlogo = DB_query($sqllogo, $db);
$rowlogo = DB_fetch_array($resutlogo);

$jreport= "";
$JasperReport = new JasperReport($confJasper);

$jreport = $JasperReport->compilerReport("cheque");
$monto= number_format($monto, $_SESSION['DecimalPlaces'], '.', '') ;
$number = $monto;
setlocale(LC_MONETARY,"en_US");
$monto= money_format("%.2n", $number);
//number_format(($myrow['ovamount']  + $myrow['ovgst']), $_SESSION['DecimalPlaces'], '.', ',')
$JasperReport->addParameter("transno", $_GET["TransNo"]);
$JasperReport->addParameter("type", $_GET["type"]);
$JasperReport->addParameter("fecha", $_GET["fecha"]);
$JasperReport->addParameter("monto",$monto);
$JasperReport->addParameter("beneficiario", $_GET["beneficiario"]);
$JasperReport->addParameter("UR", $_GET["ur"]);
$JasperReport->addParameter("URName", $_GET["urName"]);
$JasperReport->addParameter("ue", $_GET["ue"]);
$JasperReport->addParameter("ueName", $_GET["ueName"]);
$JasperReport->addParameter("concepto", $_GET["concepto"]);
$JasperReport->addParameter("numerocheque", $_GET["numerocheque"]);

$ruta = $JasperReport->getPathFile()."/".$rowlogo ['logo'];
$ruta = str_replace('jasper/', '', $ruta);
$ruta = str_replace('jasperconfig/', '', $ruta);
$JasperReport->addParameter("imagen", $ruta);

$JasperReport->addParameter("letra", strtoupper($montoletra)." PESOS ".$decimales ."/100 M.N.");
$JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/");

//echo $JasperReport->getPathFile();
//exit;
$conexion = $JasperReport->getConexionDB($_SESSION ["DatabaseName"], $dbuser, $dbpassword);
$jPrint = $JasperReport->fillReport($jreport, $JasperReport->getParameters(), $conexion);
$pdfBytes = $JasperReport->exportReportPDF($jPrint);

header('Content-type: application/pdf');
header('Content-Length: ' . strlen($pdfBytes));
header('Content-Disposition: inline; filename=report.pdf');

echo $pdfBytes;
