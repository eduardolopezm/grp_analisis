<?php
/*
 *desarrollo- 09/SEPTIEMBRE/2013 - Agregue segunda linea para narrativa cuando no cabe en espacio...
 */
$PageSecurity = 1;
include ('includes/session.inc');
include('jasper/JasperReport.php');
 //include('includes/SQL_CommonFunctions.inc');

/*error_reporting ( E_ALL );
ini_set ( 'display_errors', '1' );
ini_set ( 'log_errors', 1 );*/
//  ini_set ( 'error_log', dirname ( __FILE__ ) . '/error_log.txt' );

// If this file is called from another script, we set the required POST variables from the GET
// We call this file from SelectCustomer.php when a customer is selected and we want a statement printed


//require_once ('jasper/ReportsWithXML.php');    
   /* $JasperReport = new JasperReport ();
    $jreport = $JasperReport->compilerReport ( "PrintJournalJasper" );
    $sqllogo = "SELECT legalbusinessunit.logo
    FROM gltrans
    INNER JOIN tags ON gltrans.tag = tags.tagref
    INNER JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
    WHERE typeno='" . $_GET ["TransNo"] . "' and type='" . $_GET ["type"] . "'";
    $resutlogo = DB_query ( $sqllogo, $db );
    $rowlogo = DB_fetch_array ( $resutlogo );
    $logo = $rowlogo ['logo'];
    $logo = "/var/www/html" . $rootpath . "/" . $logo;
    
    $JasperReport->addParameter ( "transno", $_GET ["TransNo"] );
    $JasperReport->addParameter ( "type", $_GET ["type"] );
    $JasperReport->addParameter ( "logo", $logo );
    
    if ($_SESSION ['UserID'] == "desarrollo") {
    //      echo '<br><pre>getConexionDB<br>' . 'Database: ' . $_SESSION ["DatabaseName"] . ' <br>Usuario: ' . $dbuser . '<br>Password: ' . $dbpassword;
    }*/

    /*echo "DB: ".$_SESSION ["DatabaseName"]."<br>";
    echo " ".$dbuser."<br>";
    echo " ".$dbpassword."<br>";*/

   /* $conexion = $JasperReport->getConexionDB ( $_SESSION ["DatabaseName"], $dbuser, $dbpassword );
    $jPrint = $JasperReport->fillReport ( $jreport, $JasperReport->getParameters (), $conexion );
    $pdfBytes = $JasperReport->exportReportPDF ( $jPrint );
    
    header ( 'Content-type: application/pdf' );
    header ( 'Content-Length: ' . strlen ( $pdfBytes ) );
    header ( 'Content-Disposition: inline; filename=report.pdf' );

    echo $pdfBytes;*/

/*
 * if (!isset($_GET['PrintPDF'])) {
 * }
 *
 * if (isset($_GET['FromCust'])) {
 * $getFrom = $_GET['FromCust'];
 * $_POST['FromCust'] = $getFrom;
 * }
 *
 * if (isset($_GET['ToCust'])) {
 * $getTo = $_GET['ToCust'];
 * $_POST['ToCust'] = $getTo;
 * }
 *
 * if (isset($_GET['TransNo'])) {
 * $TransNo = $_GET['TransNo'];
 * }
 *
 * if (isset($_GET['periodo'])) {
 * $periodo = $_GET['periodo'];
 * }
 *
 *
 * if (isset($_GET['trandate'])) {
 * $trandate = $_GET['trandate'];
 * }
 *
 * if (isset($_GET['type'])) {
 * $type = $_GET['type'];
 * }
 *
 * $sql = "SELECT typeid,
 * typename
 * FROM systypescat
 * WHERE typeid = " . $type;
 * $SResults=DB_query($sql,$db, $ErrMsg);
 * if ($myrow=DB_fetch_array($SResults)){
 * $typename = $myrow['typename'];
 * }
 *
 * if (isset($_POST['PrintPDF']) && isset($_POST['FromCust']) && $_POST['FromCust']!=''){
 * $_POST['FromCust'] = strtoupper($_POST['FromCust']);
 *
 * if (!isset($_POST['ToCust'])){
 * $_POST['ToCust'] = $_POST['FromCust'];
 * } else {
 * $_POST['ToCust'] = strtoupper($_POST['ToCust']);
 * }
 * include('includes/PDFStarter.php');
 *
 * $pdf->addinfo('Title', _('Customer Statements') );
 * $pdf->addinfo('Subject', _('Statements from') . ' ' . $_POST['FromCust'] . ' ' . _('to').' ' . $_POST['ToCust']);
 * $PageNumber = 1;
 * $line_height=16;
 * $FirstStatement = True;
 *
 * $sql = "SELECT t.tagdescription,
 * c.accountname as account,
 * c.accountcode,
 * o.amount,
 * o.narrative,
 * l.legalname,
 * l.logo,
 * l.address1,
 * l.address2,
 * l.address3,
 * l.address4,
 * l.address5,
 * l.telephone,
 * l.fax,
 * o.userid,
 * o.counterindex
 * FROM gltrans o,tags t, chartmaster c,legalbusinessunit l
 * WHERE t.tagref=o.tag
 * AND c.accountcode=o.account
 * AND t.legalid=l.legalid
 * AND o.type = " . $type . "
 * AND o.typeno=" . $TransNo;//
 * $StatementResults=DB_query($sql,$db, $ErrMsg);
 * $NumberOfRecordsReturned = DB_num_rows($StatementResults);
 * if (DB_Num_Rows($StatementResults) == 0){
 * echo $sql;
 * $title = _('Imprime sentencias') . ' - ' . _('No polizas encontradas');
 * require('includes/header.inc');
 * echo '<div class="centre"><p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Print') . '" alt="">' . ' ' . _('Print Customer Account Statements') . '';
 * prnMsg( _('No existen polizas que correspondan con la seleccion'). $_POST['FromCust']. ' - '.$_POST['ToCust'].'.' , 'error');
 * include('includes/footer.inc');
 * exit();
 * }
 * $PageNumber =1;
 * if ($FirstStatement==True){
 * $FirstStatement=False;
 * } else {
 * $pdf->newPage();
 * }
 * ///////////////imprime detalle
 * if ($NumberOfRecordsReturned>0){
 *
 * $myrow2=DB_fetch_array($StatementResults);
 * $userid=$myrow2['userid'];
 * $counterindex = $myrow2['counterindex'];
 *
 *
 * include('includes/PrintJournalHeader.inc');
 * $StatementResults=DB_query($sql,$db, $ErrMsg);
 * $paginacion = 1;
 * while ($myrow=DB_fetch_array($StatementResults)){
 * $rsocial=$myrow['legalname'];
 * $FontSize=7;
 * $LeftOvers = $pdf->addTextWrap($Left_Margin+1,$YPos,110,$FontSize,$myrow['tagdescription'], 'left');
 * $LeftOvers = $pdf->addTextWrap($Left_Margin+110,$YPos,170,$FontSize,$myrow['accountcode'].' '.$myrow['account']);
 * if ($myrow['amount'] > 0 ){
 * $LeftOvers = $pdf->addTextWrap($Left_Margin+265,$YPos,50,$FontSize,'$' . number_format($myrow['amount'],2), 'right');
 * $TCargo = $TCargo + $myrow['amount'];
 * }else{
 * $LeftOvers = $pdf->addTextWrap($Left_Margin+320,$YPos,50,$FontSize,'$' . number_format(($myrow['amount'] * (-1)),2), 'right');
 * $TAbono = $TAbono + $myrow['amount'];
 * }
 * $FontSize=6;
 * $LeftOvers = $pdf->addTextWrap($Left_Margin+380,$YPos,140,$FontSize,$myrow['narrative'], 'left');
 * if (strlen($LeftOvers)> 0) {
 * $YPos -= ($line_height/2);
 * $LeftOvers = $pdf->addTextWrap($Left_Margin+380,$YPos,140,$FontSize,$LeftOvers, 'left');
 * }
 * $YPos -= ($line_height);
 * if ($YPos - (2 *$line_height) < $Bottom_Margin){
 * Then set up a new page
 * $PageNumber++;
 * $pdf->addText(258, 30, $FontSize, _('Pagina ').$paginacion. ' ');
 * $paginacion = $paginacion +1;
 * $FontSize=8;
 *
 * include('includes/PrintJournalHeader.inc');
 * } end of new page header
 * }
 * }
 * //////////////imprime totales
 * //$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+(4*$line_height),$Left_Margin,$Bottom_Margin+(4*$line_height));
 * $FontSize=10;
 * $LeftOvers = $pdf->addTextWrap($Left_Margin+120,780,250,$FontSize,strtoupper($rsocial));
 * $YPos -= $line_height;
 * $TAbonom=$TAbono * (-1);
 * $TAbono=number_format($TAbonom,2);
 * $TCargo=number_format($TCargo,2);
 * $LeftOvers = $pdf->addTextWrap($Left_Margin+230,$YPos,60,$FontSize,'Total', 'left');
 * $LeftOvers = $pdf->addTextWrap($Left_Margin+260,$YPos,70,$FontSize,'$'.$TCargo, 'left');
 * $LeftOvers = $pdf->addTextWrap($Left_Margin+335,$YPos,70,$FontSize,'$'.$TAbono, 'left');
 * //$LeftOvers = $pdf->addTextWrap($Left_Margin+330,$YPos,50,$FontSize,'$'.$TAbono * (-1), 'left');
 * $YPos -= $line_height;
 * $FontSize=8;
 * $pdf->addText($Left_Margin+85, $Bottom_Margin+(3*$line_height)+4, $FontSize, _('___________________________________'). ' ');
 * $pdf->addText($Left_Margin+300, $Bottom_Margin+(3*$line_height)+4, $FontSize, _('___________________________________'). ' ');
 * $YPos -= $line_height;
 * $FontSize=8;
 * $pdf->addText($Left_Margin+150, $Bottom_Margin+(3*$line_height)-5, $FontSize, _('FIRMA'). ' ');
 * $pdf->addText($Left_Margin+350, $Bottom_Margin+(3*$line_height)-5, $FontSize, _('AUTORIZO'). ' ');
 * $pdf->addText(258, 30, $FontSize, _('Pagina ').$paginacion. ' ');
 *
 * if (isset($pdf)){
 * // Here we output the actual PDF file, we have given the file a name (this could perhaps be a variable based on the Customer name), and outputted via the "I" Inline method
 * $pdfcode = $pdf->output("Movimientos Polizas.pdf", "I");
 * $len = strlen($pdfcode);
 * header('Content-type: application/pdf');
 * header('Content-Length: ' . $len);
 * header('Content-Disposition: inline; filename=Statements.pdf');
 * header('Expires: 0');
 * header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
 * header('Pragma: public');
 * $pdf->Stream();
 * } else {
 * $title = _('Print Statements') . ' - ' . _('No Statements Found');
 * include('includes/header.inc');
 * echo '<br><br><br>' . prnMsg( _('There were no statements to print') );
 * echo '<br><br><br>';
 * include('includes/footer.inc');
 * }
 * } else { The option to print PDF was not hit
 *
 * $title = _('Select Statements to Print');
 * include('includes/header.inc');
 * echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Print') . '" alt="">' . ' ' . _('Print Customer Account Statements') . '';
 * if (!isset($_POST['FromCust']) || $_POST['FromCust']=='') {
 * if FromTransNo is not set then show a form to allow input of either a single statement number or a range of statements to be printed. Also get the last statement number created to show the user where the current range is up to
 * echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST"><table>';
 * echo '<tr><td>' . _('Starting Customer statement to print (Customer code)'). '</td>
 * <td><input Type=text max=6 size=7 name=FromCust value="1"></td>
 * </tr>
 * <tr><td>'. _('Ending Customer statement to print (Customer code)').'</td>
 * <td><input Type=text max=6 size=7 name=ToCust value="zzzzzz"></td>
 * </tr>
 * </table>
 * <br><div class="centre">
 * <input type=Submit Name="PrintPDF" Value="' .
 * _('Print All Statements in the Range Selected').'">
 * </div>';
 * }
 * echo '<br><br><br>';
 * include('includes/footer.inc');
 * } end of else not PrintPDF
 */
?>
