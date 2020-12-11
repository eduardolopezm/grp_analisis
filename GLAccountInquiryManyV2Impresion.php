<?php
/**
 * Auxiliar Mayor Impresion
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 13/08/2018
 * Fecha Modificación: 13/08/2018
 * Auxiliar Mayor Impresion
 */

$funcion = 503;
$PageSecurity = 3;
include ('includes/session.inc');
$title = traeNombreFuncion($funcion, $db);

//include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');
include ('includes/SecurityFunctions.inc');
$msg = '';

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

//****Datos PDF
require_once("lib/dompdf/dompdf_config.inc.php");

$tipo = $_GET['tipo'];
$dateDesde = $_GET['dateDesde'];
$dateHasta = $_GET['dateHasta'];
$selectUnidadNegocio = $_GET['selectUnidadNegocio'];
$selectUnidadEjecutora = $_GET['selectUnidadEjecutora'];
$cuentaDesde = $_GET['cuentaDesde'];
$cuentaHasta = $_GET['cuentaHasta'];
$saldoInicialAco = $_GET['saldoInicialAco'];

$dateDesde= date("Y-m-d", strtotime($dateDesde));
$dateHasta= date("Y-m-d", strtotime($dateHasta));

$SQL="SELECT gltrans.account,
chartmaster.accountname, gltrans.type,'' as debtorno, '' as name,suppliers.suppname
typename,
gltrans.typeno,
gltrans.trandate,
gltrans.chequeno,
gltrans.narrative as narrativeOrig,
CASE WHEN gltrans.type in(10,11,12,13,21,70,110) THEN concat(gltrans.narrative,' @ ','')
ELSE
CASE WHEN gltrans.type in(20,22) THEN concat(gltrans.narrative,' @ ',suppliers.suppname)
ELSE 'showorig' END END AS narrative,
amount,
periodno,
tag,'' as folio, '' as order_, chartmaster.naturaleza,
gltrans.posted,
systypescat.typename,
CASE WHEN tb_cat_poliza_visual.ln_nombre IS NULL THEN systypescat.typename ELSE tb_cat_poliza_visual.ln_nombre END as nombreVisual
FROM gltrans JOIN tags ON gltrans.tag = tags.tagref
INNER JOIN systypescat on gltrans.type=systypescat.typeid
LEFT JOIN tb_cat_poliza_visual ON tb_cat_poliza_visual.id = systypescat.nu_poliza_visual
JOIN chartmaster ON gltrans.account = chartmaster.accountcode

LEFT JOIN supptrans ON gltrans.type = supptrans.type and gltrans.typeno = supptrans.transno
LEFT JOIN suppliers ON supptrans.supplierno=suppliers.supplierid 
WHERE 1=1 AND gltrans.narrative NOT LIKE '%POLIZA DE APERTURA%' ";

if (!empty($selectUnidadNegocio)) {
	$ids = str_replace(",","','",$selectUnidadNegocio);
	$SQL.=" AND gltrans.tag IN ( '".$ids."' )";
}

if (!empty($selectUnidadEjecutora)) {
	$ids = join("','",$selectUnidadEjecutora);
	$SQL.=" AND  gltrans.ln_ue  IN ( '".$ids."' )";
	$SQL.=" AND  ( `chartmaster`.`ln_clave` IN ( '".$ids."' ) OR LENGTH(`chartmaster`.`ln_clave`) < 2 OR `chartmaster`.`ln_clave` LIKE '%.%' )";
}

if (empty($cuentaHasta) or $cuentaHasta =="") {
	$SQL.=" AND gltrans.account = '".$cuentaDesde."'";
} else {
	$SQL.=" AND gltrans.account between '".$cuentaDesde."' AND '".$cuentaHasta."'";
}

$SQL.=" AND gltrans.trandate >=  STR_TO_DATE('" . $dateDesde . "', '%Y-%m-%d')
AND gltrans.trandate <= STR_TO_DATE('"  . $dateHasta . "', '%Y-%m-%d') ";

$SQL.=" ORDER BY gltrans.account, gltrans.counterindex DESC";
// echo "<pre>".$SQL;
// exit();


$TransResult = DB_query($SQL, $db);
DB_Txn_Commit($db);
$info=array();

$sumCargo=0;
$sumAbono=0;
$saldo=0;

$totalCuentaAgrupada = 0;

$cuentaAcumuladora='';
$cuenta='';
$cuentaAnt = '';
$cuentaAntSola = '';
$lblSaldo=0;
$saldoInicial=0;
$saldoInicialTotal=0;
$nombreCuenta ='';
$saldoAcumulado=0;
$saldoTotal=0;
$cssAlign='style="text-align:right;"';

$head = '<tr class="header-verde"><th>Tipo</th><th>Número</th><th>Fecha</th><th>Concepto</th><th style="text-align:center;">Cargo</th><th style="text-align:center;">Abono</th><th style="text-align:center;">Saldo</th></tr>';

$htmlPDF .= '<table class="table table-bordered">';
$numRegistros = 0;

$decimalOp = 2;

function fixDecimales2 ($monto = 0, $decimal = 2) {
	return number_format($monto, $decimal, '.', ',');
}

while ($myrow = DB_fetch_array($TransResult)) {
	$abonoAux="";
	$cargoAux="";

	if ($myrow['amount']>0) {
		$cargoAux=$myrow['amount'];
	} else {
		$abonoAux=$myrow['amount']*-1;
	}

	if($numRegistros == 0){
      $htmlPDF .= '<tr style="background-color:white;"><td colspan="6" style="border: 0pt solid white;"><h5><b>SALDO INICIAL ACUMULADO</b></h5></td><td style="text-align:right; border: 0pt solid white;"><h5><b>$ '.fixDecimales2($saldoInicialAco, $decimalOp).'</b></5></td></tr>';
    }

	if($cuenta != $cuentaAnt){

		if($cuentaAnt!=""){
			$htmlPDF .='<tr><td colspan="4" '.$cssAlign.'><b>Total</b></td><td '.$cssAlign.'> Cargo: '.fixDecimales2( $sumCargo, $decimalOp ).'</td><td '.$cssAlign.'> Abono: '.fixDecimales2( $sumAbono, $decimalOp ).'</td><td></td></tr><tr><td></td><td colspan="7" '.$cssAlign.' >Saldo Final: '.fixDecimales2( $saldo, $decimalOp ).'</td></tr>';
		}

		$nombreCuenta=trim($myrow['account'])." ".trim($myrow['accountname']);
		$totalCuentaAgrupada = $totalCuentaAgrupada + $saldo;
		//console.log('saldo:'+saldo + ' total:'+totalCuentaAgrupada);

		if($numRegistros != 0){
			$htmlPDF .= '<tr style="background-color:white;"><td colspan="7">&nbsp;</td></tr>';
		}

		$htmlPDF .= '<tr style="background-color:white;">';
		$htmlPDF .= '  <td colspan="6" style="border: 0pt solid white;" ><h6><b>'.strtoupper($nombreCuenta).'</b></h6></td>';
		$saldoIniciaIndividual = 0;
		// for(info2 in saldosIniciales){
		// 	saldoIniciaIndividual ='0.00';
		// 	if(saldosIniciales[info2].cuenta == cuenta){
		// 		saldoIniciaIndividual = fixDecimales(saldosIniciales[info2].saldoInicial+"");
		// 		if(saldoIniciaIndividual==""){
		// 			saldoIniciaIndividual="0.00"
		// 		}
		// 		break;
		// 	}
		// }

		$saldo = 0;
		$htmlPDF .= '  <td style="text-align:right; border: 0pt solid white;">Saldo Inicial: $ '.$saldoIniciaIndividual.'</td>';
		$saldo = $saldoIniciaIndividual;
		$htmlPDF .= '</tr>';
		$htmlPDF .= $head;
		$cuentaAnt = $cuenta;

		$sumCargo = 0;
		$sumAbono = 0;

		$saldoAcumulado = 0;

	}

	$info[] = array( "cuenta"=>trim($myrow['account'])." ".trim($myrow['accountname']),
		"fecha"=>$myrow['trandate'],
		"concepto"=>$myrow['narrativeOrig'],
		"tipo"=>$myrow['nombreVisual'],
		"trans"=>$myrow['typeno'],
		"cheque"=>$myrow['chequeno'],
		"cargo"=>$cargoAux,
		"abono"=> $abonoAux,
		"account"=>trim($myrow['account']),
		"tipoMovimiento"=> $myrow['type'],
		"amount"=> $myrow['amount']
	);

	$numRegistros ++;
}

$htmlPDF .= '</table>';

$style = "style='font-size: 13px;'";

$Header .= "<p style='font-size: 20px; width: 100%; text-align: center; padding-top: -45px;'>".$title."</p>";

$htmlHeader = "<html style='margin: 1mm 4mm 1mm 4mm;font-size: 10px;' > 
        <body style='margin: 15mm 12mm 18mm 12mm;font-size: 10px;'>
           ";
$htmlFooter = "
            </body>
        </html>";
 //echo $htmlPDF;
//$htmlPDF .= "<h1>Mostrar Reporte</h1>";
//$htmlCompleto = $htmlHeader.$html.$htmlFooter;

try{
    $dompdf = new DOMPDF();
    //$dompdf->set_paper("A4", "portrait");
    $dompdf->set_paper("A4", "portrait"); 
    
    $dompdf->load_html(($htmlHeader.$Header.$htmlPDF.$htmlFooter));
    ini_set("memory_limit","4096M");
    //ini_set('max_execution_time', 180);
    $dompdf->render();

    //echo $htmlPDF;
    header('Content-type: application/pdf'); 
    echo $dompdf->output();

    //$filename = 'Reporte.pdf';
    //$dompdf->stream($filename,array("Attachment"=>false));
    //$dompdf->stream($filename);
}catch(Exception $e){
    if ($_SESSION['UserID'] == 'desarrollo') {
        echo $e;
    }
}

?>

	