<?php
/**
 * PDF Archivos Recibidos
 *
 * @category Pdf
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 01/09/2017
 * Fecha Modificación: 01/09/2017
 * PDF Archivos Recibidos
 */

$PageSecurity = 2;
include "includes/SecurityUrl.php";
include('includes/session.inc');
include('includes/PDFStarter.php');

$FontSize=10;
$pdf->addinfo('Title', _('Nota de productos recibidos'));

$PageNumber=1;
$line_height=12;

$SQL= "SELECT distinct grnbatch,
	legalbusinessunit.address1,
	legalbusinessunit.address2,
	legalbusinessunit.address3,
	legalbusinessunit.address4,
	legalbusinessunit.address5,
	legalbusinessunit.telephone,
	legalbusinessunit.email,
	legalbusinessunit.logo,
	legalbusinessunit.fax,
	purchorders.currcode
	FROM grns,purchorderdetails inner join purchorders on purchorders.orderno=purchorderdetails.orderno
	inner join tags on tags.tagref=purchorders.tagref
	inner join legalbusinessunit on legalbusinessunit.legalid=tags.legalid
	WHERE purchorderdetails.orderno='".$_GET['PONo']."'
	AND grns.podetailitem=purchorderdetails.podetailitem
	AND grns.qtyrecd <> 0";
    $result2=DB_query($SQL, $db);
    //echo "<br>consulta de documentos<br>".$SQL;
/*
$sql='SELECT itemcode, grnno, deliverydate, itemdescription, qtyrecd, supplierid,
 (stdcostunit*qtyrecd) as total
 from grns where grnbatch='.
	$_GET['GRNNo'];
	
  CAMBIE SQL PARA QUE TAMBIEN DESPLIEGUE LOS NUMEROS DE SERIE CUANDO RECIBO MAQUINARIA
*/
//echo '<br>'.var_dump($result2);
while ($myrow2=DB_fetch_array($result2)) {
    $_POST['GRNNo']=$myrow2['grnbatch'];
    $MyCurrencyCode = $myrow2['currcode'];

    include('includes/PDFGrnHeader.inc');

    $FontSize =10;

    $sql1="SELECT  distinct grnno, itemcode, deliverydate, itemdescription, qtyrecd, supplierid,
		serialno, case when moveqty is null then 0 else moveqty end  as moveqty,stdcostunit -- ,
		-- count(*)
	from grns
		LEFT JOIN stockmoves ON grns.grnbatch =  stockmoves.transno and grns.itemcode=stockmoves.stockid and stockmoves.type = 25
		LEFT JOIN stockserialmoves ON stockserialmoves.stockmoveno = stockmoves.stkmoveno and stockserialmoves.stockid=stockmoves.stockid
	where grnbatch='".$myrow2['grnbatch']."'
	-- group by grnno, itemcode, deliverydate, itemdescription, qtyrecd, supplierid, serialno, moveqty
	order by itemcode,serialno";

    //echo $sql1;
    //exit;
    
    $result=DB_query($sql1, $db);
    $counter=1;
    $totalunidades=0;
    $serialnoAnt = " ";
    $StockIDAnt = " ";
    while ($myrow=DB_fetch_array($result)) {
        $StockID=$myrow['itemcode'];
        $Date=$myrow['deliverydate'];
        $Description=$myrow['itemdescription'];
        $Quantity=$myrow['qtyrecd'];
        $SupplierID=$myrow['supplierid'];
        $serialno=$myrow['serialno'];
        $stdcostunit = $myrow['stdcostunit'];

    
    
    
        //if (($StockID==$StockIDAnt and $myrow['serialno']!=$serialnoAnt) or ($StockID != $StockIDAnt)){
        $FontSize = 7;
        $LeftOvers = $pdf->addTextWrap($Left_Margin+1, $YPos-(10*$counter), 70, $FontSize, $StockID);
        $LeftOversProd = $pdf->addTextWrap($Left_Margin+70, $YPos-(10*$counter), 95, $FontSize, $Description);
        $LeftOvers = $pdf->addTextWrap($Left_Margin+250, $YPos-(10*$counter), 200-$Left_Margin, $FontSize, $serialno);
        
        if ($myrow['serialno'] == null) {
            $LeftOvers = $pdf->addTextWrap($Left_Margin+360, $YPos-(10*$counter), 300-$Left_Margin, $FontSize, $Quantity);
            $cant = $Quantity;
        } else {
            $LeftOvers = $pdf->addTextWrap($Left_Margin+360, $YPos-(10*$counter), 300-$Left_Margin, $FontSize, $myrow['moveqty']);
            $cant = $myrow['moveqty'];
        }
        
            /*
            $LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos-(10*$counter),25,$FontSize, $Currcode);   
            $LeftOvers = $pdf->addTextWrap($Left_Margin+475,$YPos-(10*$counter),50,$FontSize, $total,'right');
            NO TIENE SENTIDO QUE DESPLIEGUE COSTOS EN ESTA RECEPCION DE PRODUCTO
            */
            $LeftOvers = $pdf->addTextWrap($Left_Margin+400, $YPos-(10*$counter), 50, $FontSize, "$" . number_format($stdcostunit, 2), 'right');
            $LeftOvers = $pdf->addTextWrap($Left_Margin+460, $YPos-(10*$counter), 50, $FontSize, "$" . number_format($cant*$stdcostunit, 2), 'right');
            $LeftOvers = $pdf->addTextWrap($Left_Margin+480, $YPos-(10*$counter), 50, $FontSize, $MyCurrencyCode, 'right');
        
            $counter = $counter + 1;
        if (strlen($LeftOversProd) > 0) {
            $LeftOversProd = $pdf->addTextWrap($Left_Margin+55, $YPos-(10*$counter), 95, $FontSize, $LeftOversProd);
            $LeftOversProv = $pdf->addTextWrap($Left_Margin+235, $YPos-(10*$counter), 150, $FontSize, $LeftOversProv);
            $counter = $counter + 1;
        }
        
        //}
        $StockIDAnt=$StockID;
        $serialnoAnt=$myrow['serialno'];
        if ($counter >= 61) {
            $counter = 1;
            $PageNumber = $PageNumber +1;
            include('includes/PDFGrnHeader.inc');
        }
    }

    $sql='select suppname,currcode from suppliers where supplierid="'.$SupplierID.'"';
    $supplierresult=DB_query($sql, $db);
    $suppliermyrow=DB_fetch_array($supplierresult);
    $Supplier=$suppliermyrow[0];
    $Currcode=$suppliermyrow[1];

    $sql="SELECT sum(qtyrecd)as cantidadtotal from grns where grnbatch=".
    $_POST['GRNNo'];
    $cantidadresult=DB_query($sql, $db);
    $cantidadmyrow=DB_fetch_array($cantidadresult);
    $Cantidadtot=$cantidadmyrow[0];

    $FontSize=10;
    $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos-(10*$counter+60), 300-$Left_Margin, $FontSize, _('Proveedor: ').$Supplier);
    $LeftOvers = $pdf->addTextWrap($Left_Margin+310, $YPos-(10*$counter+80), 300-$Left_Margin, $FontSize, _('Unidades recibidas: ').$Cantidadtot);
    $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos-(10*$counter+80), 300-$Left_Margin, $FontSize, utf8_decode('Fecha de Recepción: ').$Date);
    $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos-(10*$counter+100), 600, 7, _('PROVEEDOR, FAVOR DE ENTREGAR ESTE DOCUMENTO CON SU FACTURA PARA QUE CUENTAS POR PAGAR TRAMITE SU PAGO'));

    $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos-(10*$counter+130), 300-$Left_Margin, $FontSize, _('Recibido por ').'______________________');
}//fin while
$pdfcode = $pdf->output();
$len = strlen($pdfcode);

if ($len<=20) {
    $title = _('Print Price List Error');
    include('includes/header.inc');
    prnMsg(_('No hubo datos de existencias de transferencia para imprimir'), 'warn');
    echo '<br><a href="'.$rootpath.'/index.php?' . SID . '">'. _('Regresar al menu').'</a>';
    include('includes/footer.inc');
    exit;
} else {
    header('Content-type: application/pdf');
    header('Content-Length: ' . $len);
    header('Content-Disposition: inline; filename=GRN.pdf');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');

    $pdf->Stream();
}


 /*end of else not PrintPDF */
