<?php
/**
 * Corte de Caja Impresión
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 13/08/2018
 * Fecha Modificación: 13/08/2018
 * Vista para la generación de la impresión del corte de caja
 */


// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$funcion = 1981;
$PageSecurity = 3;
include ('includes/session.inc');
$title = _ ( 'Impresión Corte de Caja' );

//include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');
include ('includes/SecurityFunctions.inc');
$msg = '';

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

function NombreMes($MesId) {
	switch ($MesId) {
		case 1 :
			$nmes = 'Enero';
			break;
		case 2 :
			$nmes = 'Febrero';
			break;
		case 3 :
			$nmes = 'Marzo';
			break;
		case 4 :
			$nmes = 'Abril';
			break;
		case 5 :
			$nmes = 'Mayo';
			break;
		case 6 :
			$nmes = 'Junio';
			break;
		case 7 :
			$nmes = 'Julio';
			break;
		case 8 :
			$nmes = 'Agosto';
			break;
		case 9 :
			$nmes = 'Septiembre';
			break;
		case 10 :
			$nmes = 'Octubre';
			break;
		case 11 :
			$nmes = 'Noviembre';
			break;
		case 12 :
			$nmes = 'Diciembre';
			break;
	}
	return $nmes;
}

if (isset ( $_GET ['fechacorte'] )) {
	$fechacorte = $_GET ['fechacorte'];
} else {
	$fechacorte = '';
}

$arrfechacorte = explode ( "-", $fechacorte );
$diacorte = $arrfechacorte [2];
$mescorte = $arrfechacorte [1];
$aniocorte = $arrfechacorte [0];

if (isset ( $_GET ['unidadnegocio'] )) {
	$unidadnegocio = $_GET ['unidadnegocio'];
} else {
	$unidadnegocio = '0';
}

if (isset ( $_GET ['u_cortecaja'] )) {
	$u_cortecaja = $_GET ['u_cortecaja'];
} else {
	$u_cortecaja = '0';
}

if (isset ( $_GET ['usuario'] )) {
	$_POST['usuario'] = $_GET ['usuario'];
} else {
	$_POST['usuario'] = 'all';
}

if (isset ( $_GET ['fechaInicioImpresion'] )) {
	$fechaInicioImpresion = $_GET['fechaInicioImpresion'];
}else{
	$fechaInicioImpresion = "";
}

if (isset ( $_GET ['fechaTerminoImpresion'] )) {
	$fechaTerminoImpresion = $_GET['fechaTerminoImpresion'];
}else{
	$fechaTerminoImpresion = "";
}

$SQLUsuario = "";
if ($_POST['usuario'] != 'all') {
	$SQLUsuario = " AND debtortrans.userid = '".$_POST['usuario']."' ";
}

$nu_foliocorte = 0;
if (isset($_GET['nu_foliocorte'])) {
	$nu_foliocorte = $_GET['nu_foliocorte'];
}

$unidadejecutora = '';
if (isset($_POST['unidadejecutora'])) {
	$unidadejecutora = $_POST['unidadejecutora'];
} elseif (isset($_GET['unidadejecutora'])) {
	$unidadejecutora = $_GET['unidadejecutora'];
}

//var_dump($_POST);

/*echo "<br>fechacorte: ".$fechacorte;
echo "<br>unidadnegocio: ".$unidadnegocio;
echo "<br>u_cortecaja: ".$u_cortecaja;
echo "<br>usuario: ".$_POST['usuario'];*/
?>
<!--Estilos input date-->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" >
<link rel="stylesheet" href="css/FixedAssetLeasing.css">
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.css">

<!--script input date-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
<?php
	$htmlPDF = "";
	//Estilos pdf
    $styleTablaTitulo = " style='background: #f3f3f3; color: black; font-size: 19px; margin-top: 10px; border-radius: 5px;' ";
    $styleTabla = " style='width: 100%; margin-top: -2px; border-radius: 5px;' border='0' cellpadding='0' cellspacing='0' "; // border: 2px solid #337ab7;
    $styleTablaHeader = " background: #f3f3f3; color: black; font-size: 13px;";

	$SQL = "SELECT gltempcashpayment,gltempcheckpayment,gltempccpayment,gltemptransferpayment,gltempcheckpostpayment
		FROM companies";
	$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
	$TransResult = DB_query ( $SQL, $db, $ErrMsg );
	
	$cadena = "";
	$CuentaEfectivo = "";
	while ( $myrow = DB_fetch_array ( $TransResult ) ) {
		$cadena = "'" . $myrow ['gltempcashpayment'] . "','" . $myrow ['gltempcheckpayment'] . "','" . $myrow ['gltempccpayment'] . "','" . $myrow ['gltemptransferpayment'] . "','" . $myrow ['gltempcheckpostpayment'] . "'";
		$CuentaEfectivo = $myrow ['gltempcashpayment'];
	}

	$SQL = "SELECT gl_accountsreceivable as cuenta
			FROM chartdebtortype
			UNION
			SELECT gl_debtoradvances as cuenta
			FROM chartdebtortype
			GROUP BY cuenta";
	$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
	$TransResult = DB_query ( $SQL, $db, $ErrMsg );
	
	$cadenaDevolucion = "";
	$num = 1;
	while ( $myrow = DB_fetch_array ( $TransResult ) ) {
		if ($num == 1) {
			$cadenaDevolucion = "'" . $myrow ['cuenta'] . "'";
		}else{
			$cadenaDevolucion = $cadenaDevolucion.",'" . $myrow ['cuenta'] . "'";
		}
		$num ++;
	}
	/*
	 * $SQL = "SELECT systypescat.typename, gltrans.trandate, gltrans.periodno, gltrans.account, chartmaster.accountname, gltrans.narrative, sum(gltrans.amount) as amount FROM gltrans, chartmaster, systypescat WHERE gltrans.trandate = STR_TO_DATE('" . $fechacorte . "', '%Y-%m-%d') and gltrans.account in (" . $cadena . ") and gltrans.amount <> 0 and gltrans.account = chartmaster.accountcode and gltrans.type = systypescat.typeid and gltrans.tag = " . $unidadnegocio . " and gltrans.type = 12 GROUP BY typename, trandate, periodno, account, accountname"; $ErrMsg = _('No transactions were returned by the SQL because');
	 */
	
	//muestra todos los ingresos//
	$SQLUsuario = "";
	if ($_POST['usuario'] != 'all') {
		$SQLUsuario = " and debtortransmovs.userid = '".$_POST['usuario']."' ";
	}
	//Rango de fechas (Prepoliza)
	$SQLPrepoliza = "";
	if (!empty($fechaInicioImpresion) and !empty($fechaTerminoImpresion)) {
		$SQLPrepoliza = " and debtortransmovs.origtrandate between '".$fechaInicioImpresion."' and '".$fechaTerminoImpresion."' ";
	}

	$SQLFolioPoliza = "";
	if (!empty($nu_foliocorte)) {
		$SQLFolioPoliza = " and debtortrans.nu_foliocorte = '".$nu_foliocorte."'";
	}

	$SQLUnidadEjecutora = "";
	if (!empty($unidadejecutora)) {
		$SQLUnidadEjecutora = " and debtortrans.nu_ue = '".$unidadejecutora."'";
	}

	$usuarioReporteJasper = "";
	$cajaReporteJasper = "";

	// and debtortransmovs.userid in (".$usuario.")
	//mustra ingresos por usuario//
	$SQL = "SELECT 
			systypescat.typename,
			gltrans.trandate, 
			gltrans.periodno, 
			gltrans.account,
			chartmaster.accountname, 
			gltrans.narrative,
			gltrans.typeno,
			debtortransmovs.userid,
			SUM(tb_debtortrans_forma_pago.nu_cantidad) as amount,
			CONCAT(tb_debtortrans_forma_pago.ln_paymentid, ' - ', paymentmethodssat.paymentname) as paymentname,
			www_users.realname,
			www_users.obraid
			FROM debtortransmovs
			JOIN debtortrans ON debtortrans.type = debtortransmovs.type AND debtortrans.transno = debtortransmovs.transno
			LEFT JOIN www_users ON www_users.userid = debtortrans.userid
			LEFT JOIN tb_debtortrans_forma_pago ON tb_debtortrans_forma_pago.nu_type = debtortransmovs.type AND tb_debtortrans_forma_pago.nu_transno = debtortransmovs.transno
			LEFT JOIN paymentmethodssat ON paymentmethodssat.paymentid = tb_debtortrans_forma_pago.ln_paymentid
			LEFT JOIN systypescat ON systypescat.typeid = debtortransmovs.type
			LEFT JOIN (
			SELECT
			MAX(gltrans.counterindex) as max,
			gltrans.type,
			gltrans.typeno
			FROM gltrans
			WHERE gltrans.type = 12
			AND DATE_FORMAT(gltrans.trandate, '%Y-%m-%d') = '".$fechacorte."'
			AND gltrans.account in (".$cadena.") 
			GROUP BY gltrans.type, gltrans.typeno
			) as gltransMax ON gltransMax.type = debtortransmovs.type AND gltransMax.typeno = debtortransmovs.transno
			LEFT JOIN gltrans ON gltrans.type = 12 and gltrans.typeno = debtortransmovs.transno AND gltrans.counterindex = gltransMax.max
			LEFT JOIN chartmaster ON chartmaster.accountcode = gltrans.account
			WHERE 
			DATE_FORMAT(debtortransmovs.trandate, '%Y-%m-%d') = '".$fechacorte."'
			and debtortrans.tagref = '".$unidadnegocio."'
			and gltrans.narrative not like '%cancelado%' 
			and gltrans.account in (".$cadena.") 
			
			and gltrans.narrative not like '% IVA %'
			and (gltrans.type = 12 and systypescat.typeid = 12)
			and debtortrans.nu_foliocorte <> 0
			and (debtortransmovs.reference not like '70 -%' and debtortransmovs.reference not like '10 -%')
			".$SQLUsuario.$SQLPrepoliza.$SQLFolioPoliza.$SQLUnidadEjecutora."
			GROUP BY systypescat.typename, gltrans.trandate, gltrans.periodno, gltrans.account, chartmaster.accountname, tb_debtortrans_forma_pago.ln_paymentid";
	// and gltrans.tag = '".$unidadnegocio."' 
	// and gltrans.amount > 0 
	if ($_SESSION['UserID'] == "desarrollo3") {
		// echo "<br><pre>Total de Ingresos: ".$SQL."<br>"; 
		// exit();
	}
	
	$TransResult = DB_query ( $SQL, $db, $ErrMsg );
	
	$usql = "SELECT tagdescription FROM tags WHERE tagref = " . $unidadnegocio;
	$ErrMsg = "LA SENTENCIA SQL FALLO DEBIDO A ";
	$uResult = DB_query ( $usql, $db, $ErrMsg );
	if ($umyrow = DB_fetch_array ( $uResult )) {
		$nombreunidad = $umyrow ['tagdescription'];
	}
	$SQLUsuario = "";
	if ($_POST['usuario'] != 'all') {
		$SQLUsuario = " AND debtortrans.userid = '".$_POST['usuario']."' ";
	}

	// Validacion 
	


	$styleResumen = "style='display: none;'";
	if (isset($_GET['resumen'])) {
		$styleResumen = "";
	}

	$htmlPDF .= "
		<div class='container-fluid' ".$styleResumen.">
		<div class='panel panel-primary'>
			<!-- Default panel contents -->
			<div class='panel-heading' align='center' ".$styleTablaTitulo."> Total de Ingresos </div>
			<!--<div class='panel-body'>
			</div>-->
			<!-- Table -->
			<table class='table table-hover' ".$styleTabla.">
				<tr>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Tipo</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Fecha Pago</b></th>
					<th style='text-align:center; display:none; ".$styleTablaHeader."'><b>Periodo</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Cuenta Contable</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Forma de Pago</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Monto</b></th>";
					if (! isset ( $_POST ['Editar'] )) {
						//$htmlPDF .= "<th style='text-align:center'><b>" . _ ( 'Asignar' ) . "</b></th>";
					}
					
					$htmlPDF .= "</tr>";
					$htmlPDF .= $tableheader;
					$htmlPDF .= "<tr class='bg-info'><td colspan=5 style='text-align:left;'><b>" . _ ( 'RECIBIDOS' ) . "</b></td></tr>";
					$accounanterior = "";
					$total = 0;
					$totaltotales1 = 0;
					$i = 0;
					$k = 0;
					$asignar = 1;
					$porasignar = 0;
					$totalporasignar = 0;
					$totaltemp1 = 0;
					$totalEfectivo = 0;
					while ( $myrow = DB_fetch_array ( $TransResult ) ) {
						$i = $i + 1;

						$usuarioReporteJasper = $myrow['realname'];
						$cajaReporteJasper = $myrow['obraid'];

						if ($CuentaEfectivo == $myrow['account']) {
                            $totalEfectivo = $totalEfectivo + $myrow ['amount'];
                        }

						$monto = $myrow ['amount'];
						$porasignar = $monto;
						if (count ( $_SESSION ['MOVS'] ) > 0) {
							foreach ( $_SESSION ['MOVS'] as $Movs ) {
								$tempfromaccount = substr ( $Movs->FromAccount, 0, strpos ( $Movs->FromAccount, '-' ) );
								if ($tempfromaccount == $myrow ['account']) {
									$porasignar = $porasignar - $Movs->Amount;
								}
							}
						}
						
						if (abs ( $porasignar ) > 0.01) {
							$asignar = 0;
						}
						
						$htmlPDF .=  "<tr>";
						$htmlPDF .=  "<td style='text-align:center;'>" . $myrow ['typename'] . "</td>";
						$htmlPDF .=  "<td style='text-align:center;'>" . $myrow ['trandate'] . "</td>";
						$htmlPDF .=  "<td style='text-align:center; display: none;'>" . $myrow ['periodno'] . "</td>";
						$htmlPDF .=  "<td style='text-align:center;'>" . $myrow ['account'] . " - " . $myrow ['accountname'] . "</td>";
						$htmlPDF .=  "<td style='text-align:center;'>" . $myrow ['paymentname'] . "</td>";
						$htmlPDF .=  "<td style='text-align:center;' class='number'>" . number_format ( $myrow ['amount'], 2 ) . "</td>";
						if (! isset ( $_POST ['Editar'] )) {
							//$htmlPDF .=  "<td style='text-align:center' class='number'>" . number_format ( $porasignar, 2 ) . "</td>";
						}
						
						$htmlPDF .=  "</tr>";
						$total = $total + $myrow ['amount'];
						$totalporasignar = $totalporasignar - $porasignar;
						$totaltotales1 = $totaltotales1 + $myrow ['amount'];
						$totaltemp1 = $totaltemp1 + $myrow ['amount'];
					}
					
					$htmlPDF .=  "<tr>";
					$htmlPDF .=  "<td colspan=4 style='text-align:right;'><b>" . _ ( 'Total Recibos' ) . ": &nbsp;</b></td>";
					$htmlPDF .=  "<td style='text-align:center' class='number'><b>" . number_format ( $totaltotales1, 2 ) . "</b></td>";
					if (! isset ( $_POST ['Editar'] )) {
						//$htmlPDF .=  "<td style='text-align:center' class='number'><b>" . number_format ( $totalporasignar, 2 ) . "</b></td>";
					}
					$htmlPDF .=  "</tr>";
					/**
					* *****************************************PERDIDA CAMBIARIA*****************************************************
					*/
					/**
					* MUESTRA LOS MOVIMIENTOS DE PERDIDA CAMBIARIA
					* *
					*/
					/*
					* $SQL = "SELECT systypescat.typename, gltrans.trandate, gltrans.periodno, gltrans.account, chartmaster.accountname, gltrans.narrative, sum(gltrans.amount) as amount FROM gltrans, chartmaster, systypescat WHERE gltrans.trandate = STR_TO_DATE('" . $fechacorte . "', '%Y-%m-%d') and gltrans.narrative like '%UTIL/PERD CAMBIARIA%' and gltrans.amount > 0 and gltrans.account = chartmaster.accountcode and gltrans.type = systypescat.typeid and gltrans.tag = " . $unidadnegocio . " and gltrans.type = 12 GROUP BY typename, trandate, periodno, account, accountname"; $ErrMsg = _('No transactions were returned by the SQL because'); echo $SQL;
					*/
				
					$SQL = "SELECT s.typename,
						g1.trandate,
						g1.periodno,
						g1.account,
						c.accountname,
						g1.narrative,
						(sum(g1.amount) + sum(ifnull(g2.amount,0))) as amount 
						FROM gltrans g1
						left join  gltrans g2 on g1.type = g2.type
							and g1.typeno = g2.typeno
							and g1.account = g2.account
							and g1.counterindex <> g2.counterindex
							and abs(g1.amount) = abs(g2.amount),
						chartmaster c, systypescat  s
						WHERE g1.trandate = STR_TO_DATE('" . $fechacorte . "', '%Y-%m-%d')
						and g1.narrative like '%UTIL/PERD CAMBIARIA%'
						and g1.amount > 0 
						and g1.account = c.accountcode
						and g1.type = s.typeid 
						and g1.tag = " . $unidadnegocio . "
						and g1.type = 12 
						GROUP BY s.typename, g1.trandate, g1.periodno, g1.account, c.accountname";
					
					$TransResult = DB_query ( $SQL, $db, $ErrMsg );
					$totaltemp3 = 0;
					$totaltotales3 = 0;
					if (DB_num_rows ( $TransResult ) > 0) {
						$htmlPDF .=  '<table class="table table-hover">';
						$htmlPDF .=  "<tr class='bg-info'><td colspan=8 style='text-align:left;'><b>" . _ ( 'Total de Perdida Cambiaria' ) . "</b></td></tr>";
						$tableheader = "<tr>
								<td style='text-align:center'><b>" . _ ( 'Tipo' ) . "</b></td>
								<td style='text-align:center'><b>" . _ ( 'Fecha' ) . "</b></td>
								<td style='text-align:center'><b>" . _ ( 'Periodo' ) . "</b></td>
								<td style='text-align:center'><b>" . _ ( 'No. Cuenta' ) . "</b></td>
								<td style='text-align:center'><b>" . _ ( 'Cuenta' ) . "</b></td>
								<td style='text-align:center'><b>" . _ ( 'Cantidad' ) . "</b></td>";
						$htmlPDF .=  $tableheader;
						$htmlPDF .=  "<tr><td colspan=8 style='text-align:left;'><b>" . _ ( 'Perdida Cambiaria' ) . "</b></td></tr>";
						
						$i = 0;
						$k = 0;
						
						$total = 0;
						$totaltotales3 = 0;
						$totaltemp3 = 0;
						while ( $myrow = DB_fetch_array ( $TransResult ) ) {
							$i = $i + 1;
							
							$htmlPDF .=  "<tr>";
							$htmlPDF .=  "<td>" . $myrow ['typename'] . "</td>";
							$htmlPDF .=  "<td>" . $myrow ['trandate'] . "</td>";
							$htmlPDF .=  "<td>" . $myrow ['periodno'] . "</td>";
							$htmlPDF .=  "<td>" . $myrow ['account'] . "</td>";
							$htmlPDF .=  "<td>" . $myrow ['accountname'] . "</td>";
							$htmlPDF .=  "<td class='number'>" . number_format ( $myrow ['amount'], 2 ) . "</td>";
							$htmlPDF .=  "</tr>";
							// $total = $total + $myrow['amount'];
							$totaltotales3 = $totaltotales3 + $myrow ['amount'];
							$totaltemp3 = $totaltemp3 + $myrow ['amount'];
						}
						$htmlPDF .=  "<tr height=5><td colspan=8 style='text-align:left;'></td></tr>";
						$htmlPDF .=  "<tr>";
						$htmlPDF .=  "<td colspan=5 style='text-align:right;'><b>" . _ ( 'TOTAL UTILIDAD/PERDIDA CAMBIARIA' ) . ": &nbsp;</b></td>";
						$htmlPDF .=  "<td class='number'><b>" . number_format ( $totaltotales3, 2 ) . "</b></td>";
						
						$htmlPDF .=  "</tr>";
					}

					/**
					 * ********************************************************************************************************************************
					 */
				
					$SQL = "SELECT systypescat.typename,
						gltrans.trandate,
						gltrans.periodno,
						gltrans.account,
						chartmaster.accountname,
						gltrans.narrative,
						sum(gltrans.amount) as amount
					FROM gltrans, debtortransmovs, chartmaster, systypescat
					WHERE gltrans.tag = " . $unidadnegocio . "
					and gltrans.trandate = STR_TO_DATE('" . $fechacorte . "', '%Y-%m-%d')
					and gltrans.counterindex = debtortransmovs.idgltrans
					and (debtortransmovs.type = 80 
					or debtortransmovs.reference = 21)
					and gltrans.account = chartmaster.accountcode
					and systypescat.typeid = debtortransmovs.type
					and gltrans.narrative not like '% IVA %'
					and alloc > 0 group by gltrans.account having sum(gltrans.amount) > 0";
					$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
					$TransResult = DB_query ( $SQL, $db, $ErrMsg );
					if (DB_num_rows ( $TransResult ) > 0) {
						$htmlPDF .= "<tr><td colspan=8 style='text-align:left;'><b>" . _ ( 'ANTICIPOS APLICADOS' ) . "</b></td></tr>";
						$accounanterior = "";
						$total = 0;
						$i = 0;
						$k = 0;
						$totaltemp2 = 0;
						while ( $myrow = DB_fetch_array ( $TransResult ) ) {
							$i = $i + 1;
							
							$htmlPDF .= "<tr>";
							$htmlPDF .= "<td style='text-align:center'>" . $myrow ['typename'] . "</td>";
							$htmlPDF .= "<td style='text-align:center'>" . $myrow ['trandate'] . "</td>";
							$htmlPDF .= "<td style='text-align:center'>" . $myrow ['periodno'] . "</td>";
							$htmlPDF .= "<td style='text-align:center'>" . $myrow ['account'] . "</td>";
							$htmlPDF .= "<td style='text-align:center'>" . $myrow ['accountname'] . "</td>";
							$htmlPDF .= "<td style='text-align:center' class='number'>" . number_format ( abs ( $myrow ['amount'] ), 2 ) . "</td>";
							if (! isset ( $_POST ['Editar'] )) {
								//$htmlPDF .= "<td style='text-align:center' class='number'>" . number_format ( 0, 2 ) . "</td>";
							}
							
							$htmlPDF .= "</tr>";
							$total = $total + $myrow ['amount'];
							// $totalporasignar = $totalporasignar - $porasignar;
							$totaltotales2 = $totaltotales2 + $myrow ['amount'];
							$totaltemp2 = $totaltemp2 + abs ( $myrow ['amount'] );
						}
						$htmlPDF .= "<tr>";
						$htmlPDF .= "<td colspan=5 style='text-align:right;'><b>" . _ ( 'TOTAL ANTICIPOS APLICADOS' ) . ": &nbsp;</b></td>";
						$htmlPDF .= "<td class='number'><b>" . number_format ( $totaltemp2, 2 ) . "</b></td>";
						if (! isset ( $_POST ['Editar'] )) {
							//$htmlPDF .= "<td class='number'><b>" . number_format ( 0, 2 ) . "</b></td>";
						}
						$htmlPDF .= "</tr>";
					}

					/**
					* MUESTRA LOS MOVIMIENTOS DE NOTAS DE DEVOLUCION DE EFECTIVO
					* 
					*/
					$SQLUsuario = "";
					if ($_POST['usuario'] != 'all') {
						$SQLUsuario = " and debtortransmovs.userid = '".$_POST['usuario']."' ";
					}
					//Rango de fechas (Prepoliza)
					$SQLPrepoliza = "";
					if (!empty($fechaInicioImpresion) and !empty($fechaTerminoImpresion)) {
						$SQLPrepoliza = " and debtortransmovs.origtrandate between '".$fechaInicioImpresion."' and '".$fechaTerminoImpresion."' ";
					}
					$totalNotaDevolucionAnticipos = 0;
					$SQL = "SELECT 
						systypescat.typename,
						gltrans.trandate, 
						gltrans.periodno, 
						gltrans.account,
						chartmaster.accountname, 
						gltrans.narrative,
						gltrans.typeno,
						debtortransmovs.userid,
						SUM(gltrans.amount) as amount
						FROM debtortransmovs
						LEFT JOIN systypescat ON systypescat.typeid = debtortransmovs.type
						LEFT JOIN gltrans ON gltrans.type = 4 and gltrans.typeno = debtortransmovs.transno
						LEFT JOIN chartmaster ON chartmaster.accountcode = gltrans.account
						LEFT JOIN debtortrans ON debtortrans.type = debtortransmovs.type and debtortrans.transno = debtortransmovs.transno
						WHERE 
						DATE_FORMAT(debtortransmovs.trandate, '%Y-%m-%d') = '".$fechacorte."'
						".$SQLUsuario.$SQLPrepoliza."
						and debtortransmovs.tagref = '".$unidadnegocio."'
						and gltrans.tag = '".$unidadnegocio."' 
						and debtortransmovs.type = 4
						and gltrans.account in (".$cadenaDevolucion.")
						and gltrans.narrative not like '%cancelado%'
						and debtortrans.invtext not like '%cancela%'
						and gltrans.amount > 0 
						and gltrans.narrative not like '% IVA %'
						GROUP BY systypescat.typename, gltrans.trandate, gltrans.periodno, gltrans.account, chartmaster.accountname";
					if ($_SESSION['UserID'] == "admin" OR $_SESSION['UserID'] == "desarrollo") {
						//echo "<br><pre>NOTAS DE DEVOLUCION DE ANTICIPOS: ".$SQL."<br>"; 
					}
					$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
					$TransResult = DB_query ( $SQL, $db, $ErrMsg );
					if (DB_num_rows ( $TransResult ) > 0) {
						$htmlPDF .= "<tr class='bg-info'><td colspan=8 style='text-align:left;'><b>" . _ ( 'NOTAS DE DEVOLUCION DE ANTICIPOS' ) . "</b></td></tr>";
						$accounanterior = "";
						$total = 0;
						$i = 0;
						$k = 0;
						$totaltemp2 = 0;
						while ( $myrow = DB_fetch_array ( $TransResult ) ) {
							$i = $i + 1;
							
							$htmlPDF .= "<tr>";
							$htmlPDF .= "<td style='text-align:center'>" . $myrow ['typename'] . "</td>";
							$htmlPDF .= "<td style='text-align:center'>" . $myrow ['trandate'] . "</td>";
							$htmlPDF .= "<td style='text-align:center'>" . $myrow ['periodno'] . "</td>";
							$htmlPDF .= "<td style='text-align:center'>" . $myrow ['account'] . "</td>";
							$htmlPDF .= "<td style='text-align:center'>" . $myrow ['accountname'] . "</td>";
							$htmlPDF .= "<td style='text-align:center' class='number'>" . number_format ( ( $myrow ['amount'] * -1 ), 2 ) . "</td>";
							
							$htmlPDF .= "</tr>";
							$totalNotaDevolucionAnticipos = $totalNotaDevolucionAnticipos + $myrow ['amount'];
						}
						$htmlPDF .= "<tr>";
						$htmlPDF .= "<td colspan=5 style='text-align:right;'><b>" . _ ( 'TOTAL NOTAS DE DEVOLUCION' ) . ": &nbsp;</b></td>";
						$htmlPDF .= "<td style='text-align:center' class='number'><b>" . number_format ( $totalNotaDevolucionAnticipos * -1, 2 ) . "</b></td>";
						$htmlPDF .= "</tr>";
					}

					if ($totalNotaDevolucionAnticipos > 0) {
                        $htmlPDF .= "<tr>";
                        $htmlPDF .= "<td colspan=4 style='text-align:right;'><b>" . _ ( 'EFECTIVO A ENTREGAR ' ) . $CuentaEfectivo . ": &nbsp;</b></td>";
                        $htmlPDF .= "<td style='text-align:center' class='number'><b>" . number_format ( $totalEfectivo - $totalNotaDevolucionAnticipos, 2 ) . "</b></td>";
                        $htmlPDF .= "</tr>";
                    }
                    
					$htmlPDF .= "<tr>";
					$htmlPDF .= "<td colspan=4 style='text-align:right;'><b>" . _ ( 'Total Ingresos' ) . ": &nbsp;</b></td>";
					$totalIngresosCorte = number_format ( ($totaltemp1 + $totaltemp2 + $totaltemp3) - $totalNotaDevolucionAnticipos, 2 );
					$totalIngresosCorte2= ($totaltemp1 + $totaltemp2 + $totaltemp3);
					$htmlPDF .= "<td style='text-align:center' class='number'><b>" . number_format ( ($totaltemp1 + $totaltemp2 + $totaltemp3) - $totalNotaDevolucionAnticipos, 2 ) . "</b></td>";
					if (! isset ( $_POST ['Editar'] )) {
						//$htmlPDF .= "<td style='text-align:center' class='number'><b>" . number_format ( $totalporasignar, 2 ) . "</b></td>";
					}
					$htmlPDF .= "</tr>";
	$htmlPDF .= "
			</table>
		</div>
	</div>
	";


	if (isset($_GET['ficha'])) {
		
		$unosql = "SELECT bankaccountnumber FROM bankaccounts WHERE nu_activo = 1";
		$ErrMsg = "LA SENTENCIA SQL FALLO DEBIDO A ";
		$unoResult = DB_query ( $unosql, $db, $ErrMsg );
		$bankaccountnumber = "";
		if ($unomyrow = DB_fetch_array ( $unoResult )) {
			$bankaccountnumber = $unomyrow ['bankaccountnumber'];
		}
	
		$dosql = "SELECT 
		legalbusinessunit.legalname,
		tags.tagref,
		tags.legalid
		FROM tags
		JOIN legalbusinessunit ON tags.legalid =  legalbusinessunit.legalid
		WHERE tags.tagref = " . $_GET['unidadnegocio'];
	 
		$ErrMsg = "LA SENTENCIA SQL FALLO DEBIDO A ";
		$dosResult = DB_query ( $dosql, $db, $ErrMsg );
		$legalname = "";
		if ($dosmyrow = DB_fetch_array ( $dosResult )) {
			$legalname = $dosmyrow ['legalname'];
		}
	
		$fecha = $_GET['fechacorte'];
		$fechaPartes = explode("-", $fecha);
		$fechaPartes[0]; // porción1
		$fechaPartes[1];
		$fechaPartes[2];
		$fechaCompleta= $fechaPartes[2].'/'.$fechaPartes[1].'/'.$fechaPartes[0];
				
	
					
						
		
	
				$PrintPDF = $_GET ['PrintPDF'];
				$_POST ['PrintPDF'] = $PrintPDF;
				include('jasper/JasperReport.php');
				include("includes/SecurityUrl.php");
			
				$ur = $_GET['unidadnegocio'];
				$ue = $_GET['unidadejecutora'];
				$datesita = $_GET['fechacorte'];

				$date = $datesita;
				
					$jreport= "";
					$JasperReport = new JasperReport($confJasper);
					$jreport = $JasperReport->compilerReport("/rptFichaRecibo");
					$JasperReport->addParameter("pNoCorte", $_GET['nu_foliocorte']);
					$JasperReport->addParameter("pNoCuenta", $bankaccountnumber);
					$JasperReport->addParameter("pCliente", $legalname);
					$JasperReport->addParameter("pFecha", $fechaCompleta);
					$JasperReport->addParameter("pCajero", $usuarioReporteJasper);
					$JasperReport->addParameter("pCaja", $cajaReporteJasper);
					$JasperReport->addParameter("pUr", $ur);
					$JasperReport->addParameter("pUe", $ue);
					$JasperReport->addParameter("pDate", $date);
					$JasperReport->addParameter("pUsuario", $_GET['usuario']);
					
					
			
					$conexion = $JasperReport->getConexionDB($_SESSION ["DatabaseName"], $dbuser, $dbpassword);
					$jPrint = $JasperReport->fillReport($jreport, $JasperReport->getParameters(), $conexion);
					$pdfBytes = $JasperReport->exportReportPDF($jPrint);
			
					header('Content-type: application/pdf');
					header('Content-Length: ' . strlen($pdfBytes));
					header('Content-Disposition: inline; filename=report.pdf');
			
					echo $pdfBytes;
			
				exit();
			
			
		}
	
	/* DEFINO ARRAY PARA IR ALMACENANDO MOVIMIENTOS */
	
	/* TIPO DE DOCUMENTOS
	110 - FACTURA DE CONTADO
	10 - FACTURA DE CREDITO
	21 - NOTA DE CARGO (MORATORIOS)
	90 - INGRESO DE CAJA
	80 - ANTICIPO
	*/

	//Cuentas puente Cadena
	$cadenaCuentaPuente = "";
	$SQL = "SELECT companies.gltempcashpayment as efectivo, companies.gltempcheckpayment as cheque, companies.gltempccpayment as credito, companies.gltempccpayment as debito, companies.gltemptransferpayment as transferencia FROM companies";
	$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
	$TransResult = DB_query ( $SQL, $db, $ErrMsg );
	while ( $myrow = DB_fetch_array ( $TransResult ) ) {
		$cadenaCuentaPuente = "'".$myrow ['efectivo']."','".$myrow ['cheque']."','".$myrow ['credito']."','".$myrow ['debito']."','".$myrow ['transferencia']."'";
	}
	
	$SQLUsuario = "";
	$SQLUsuario2 = "";
	if ($_POST['usuario'] != 'all') {
		$SQLUsuario = " and debtortrans.userid = '".$_POST['usuario']."' ";
		$SQLUsuario2 = " and dm.userid = '".$_POST['usuario']."' ";
	}
	//Rango de fechas (Prepoliza)
	$SQLPrepoliza = "";
	$SQLPrepoliza2 = "";
	if (!empty($fechaInicioImpresion) and !empty($fechaTerminoImpresion)) {
		$SQLPrepoliza = " and debtortrans.origtrandate between '".$fechaInicioImpresion."' and '".$fechaTerminoImpresion."' ";
		$SQLPrepoliza2 = " and dm.origtrandate between '".$fechaInicioImpresion."' and '".$fechaTerminoImpresion."' ";
	}

	$SQLFolioPoliza = "";
	if (!empty($nu_foliocorte)) {
		$SQLFolioPoliza = " and d2.nu_foliocorte = '".$nu_foliocorte."'";
	}

	$SQLUnidadEjecutora = "";
	if (!empty($unidadejecutora)) {
		$SQLUnidadEjecutora = " and d2.nu_ue = '".$unidadejecutora."'";
	}

	//Recibos y sus facturas
	$SQL = "SELECT 
			debtortrans.id, 
			debtortrans.reference,
			debtortrans.ovamount,
			debtortrans.ovgst,
			debtortrans.alloc,
			debtortrans.invtext,
			debtortrans.trandate,
			debtortrans.transno, 
			debtortrans.type,
			debtortrans.rate,
			debtortrans.transno as transnoFac,
			IFNULL(folio,'s/n') as folio,
			debtortrans.alloc as asignado,
			CASE WHEN debtortrans.type IN (10,110,119,125) THEN 1 ELSE 0 END AS pendiente,
			debtortrans.transno as foliorecibo,
			systypescat.typename,
			debtorsmaster.name, 
			debtorsmaster.debtorno,
			debtortrans.userid as userid,
			'' as realname,
			'' as idgltrans,
			debtortrans.codesat as datosfactura_codesat,
			debtortrans.folio as datosfactura_Folio,
			debtortrans.order_ as datosfactura_Orden,
			debtortrans.ovamount as datosfactura_Ovamount,
			debtortrans.ovgst as datosfactura_Ovgst,
			'sin recibo' as ordenar,
			(SELECT paymentname FROM paymentmethodssat WHERE paymentid in ( SUBSTRING(debtortrans.codesat, 1, 2) )) as paymentnameOrder,
			debtortrans.ovamount + debtortrans.ovgst as montoRecibo
			FROM debtortrans, systypescat, debtorsmaster
			WHERE debtortrans.type = systypescat.typeid 
			AND DATE_FORMAT(debtortrans.trandate, '%Y-%m-%d') = '".$fechacorte."'
			and debtortrans.tagref = '".$unidadnegocio."'
			and debtortrans.debtorno = debtorsmaster.debtorno
			AND (
				((debtortrans.type in(110,125))
					and ((abs(debtortrans.ovamount + debtortrans.ovgst) - abs(debtortrans.alloc)) > 0.02)
					)
				OR (debtortrans.type = 10
					and ((abs(debtortrans.ovamount + debtortrans.ovgst) - abs(debtortrans.alloc)) > 0.01)
					and debtortrans.alloc <> 0
					and day(debtortrans.trandate) = day(debtortrans.origtrandate)
					and month(debtortrans.trandate) = month(debtortrans.origtrandate)
					and year(debtortrans.trandate) = year(debtortrans.origtrandate)
					)
					
				OR (
					debtortrans.type = 21
					and ((abs(debtortrans.ovamount + debtortrans.ovgst) - abs(debtortrans.alloc)) > 0.01)
					and abs(debtortrans.alloc) <> 0
					and debtortrans.alloc < 0
					)
			)
			".$SQLUsuario.$SQLPrepoliza."
			UNION
			SELECT
			dm.id,
			dm.reference,
			dm.ovamount,
			dm.ovgst,
			dm.alloc,
			d2.invtext,
			dm.trandate,
			dm.transno,
			dm.type,
			dm.rate,
			SUBSTRING(dm.reference, LOCATE(' - ', dm.reference)+3 ) as transnoFac, 
			'' as folio,
			0 as asignado,
			0 as pendiente,
			d2.folio as foliorecibo,
			s.typename,
			m.name,
			m.debtorno,
			www_users.userid,
			www_users.realname,
			dm.idgltrans,
			datosfactura.codesat as datosfactura_codesat,
			datosfactura.folio as datosfactura_Folio,
			datosfactura.order_ as datosfactura_Orden,
			datosfactura.ovamount as datosfactura_Amount,
			datosfactura.ovgst as datosfactura_Ovgst,
			'con recibo' as ordenar,
			CASE gltrans.account
				WHEN 
					(SELECT companies.gltempcashpayment FROM companies LEFT JOIN legalbusinessunit ON legalbusinessunit.taxid = companies.companynumber LEFT JOIN tags ON tags.legalid = legalbusinessunit.legalid WHERE tags.tagref = '".$unidadnegocio."') 
				THEN (SELECT paymentname FROM paymentmethodssat WHERE paymentid = '01')
				WHEN (SELECT companies.gltempcheckpayment FROM companies LEFT JOIN legalbusinessunit ON legalbusinessunit.taxid = companies.companynumber LEFT JOIN tags ON tags.legalid = legalbusinessunit.legalid WHERE tags.tagref = '".$unidadnegocio."')
				THEN (SELECT paymentname FROM paymentmethodssat WHERE paymentid = '02')
				WHEN 
					(SELECT companies.gltempccpayment FROM companies LEFT JOIN legalbusinessunit ON legalbusinessunit.taxid = companies.companynumber LEFT JOIN tags ON tags.legalid = legalbusinessunit.legalid WHERE tags.tagref = '".$unidadnegocio."') 
				THEN (SELECT paymentname FROM paymentmethodssat WHERE paymentid = '04')
				WHEN 
					(SELECT companies.gltempccpayment FROM companies LEFT JOIN legalbusinessunit ON legalbusinessunit.taxid = companies.companynumber LEFT JOIN tags ON tags.legalid = legalbusinessunit.legalid WHERE tags.tagref = '".$unidadnegocio."') 
				THEN (SELECT paymentname FROM paymentmethodssat WHERE paymentid = '28')
				WHEN 
					(SELECT companies.gltemptransferpayment FROM companies LEFT JOIN legalbusinessunit ON legalbusinessunit.taxid = companies.companynumber LEFT JOIN tags ON tags.legalid = legalbusinessunit.legalid WHERE tags.tagref = '".$unidadnegocio."') 
				THEN (SELECT paymentname FROM paymentmethodssat WHERE paymentid = '03')
			END as paymentnameOrder,
			SUM(gltrans.amount) as montoRecibo
			FROM debtortransmovs dm
			LEFT JOIN debtortrans d2 ON d2.transno = dm.transno
			LEFT JOIN systypescat s ON s.typeid = d2.type
			LEFT JOIN debtorsmaster m ON m.debtorno = dm.debtorno
			LEFT JOIN www_users ON www_users.userid = d2.userid
			LEFT JOIN debtortrans datosfactura ON datosfactura.type = 110 and datosfactura.transno = SUBSTRING(dm.reference, LOCATE(' - ', dm.reference)+3 )
			LEFT JOIN gltrans ON gltrans.typeno = dm.transno
			WHERE
			DATE_FORMAT(dm.trandate, '%Y-%m-%d') = '".$fechacorte."'
			and dm.tagref = '".$unidadnegocio."'
			and (
					dm.type = 12 and ( (dm.alloc <> 0 and dm.reference not in ('0','80')) or (dm.reference='80') or (dm.alloc > 0 and dm.reference='0') )
				)
			and (
					( dm.transno = d2.transno AND dm.type = d2.type AND dm.tagref = d2.tagref AND dm.reference <> '' )
					or
					( SUBSTRING(dm.reference, LOCATE(' - ', dm.reference)+3 ) = d2.transno AND SUBSTRING(dm.reference, 1, LOCATE(' - ', dm.reference)-1) = d2.type AND dm.tagref = d2.tagref AND dm.type = 80 )
				)
			".$SQLUsuario2.$SQLPrepoliza2.$SQLFolioPoliza.$SQLUnidadEjecutora."
			and d2.nu_foliocorte <> 0
			and gltrans.narrative not like '%cancelado%' 
			and gltrans.account in (".$cadenaCuentaPuente.") 
			and gltrans.amount > 0 
			and gltrans.narrative not like '% IVA %'
			and (dm.reference not like '70 -%' and dm.reference not like '10 -%')
			GROUP BY dm.type, dm.transno
			ORDER BY ordenar, paymentnameOrder, datosfactura_Folio, realname, invtext";
	if ($_SESSION['UserID'] == "admin") {
		//echo "<br><pre>Detalle Aplicacion de Ingresos: ".$SQL."<br>";
	}
	$ErrMsg = _ ( 'No transactions were returned by the SQL becauses' );
	$TransResult = DB_query ( $SQL, $db, $ErrMsg );

	// Validacion resumen
	$styleResumen = "style='display: none;'";
	if (!isset($_GET['resumen'])) {
		$styleResumen = "";
	}

	$htmlPDF .= "
	<div class='container-fluid' ".$styleResumen.">
		<div class='panel panel-primary'>
			<!-- Default panel contents -->
			<div class='panel-heading' align='center' ".$styleTablaTitulo."> Detalle Aplicación de Ingresos</div>
			<!--<div class='panel-body'>
			</div>-->
			<!-- Table -->
			<table class='table table-hover' ".$styleTabla.">
				<tr>
					<th style='text-align:center; display: none; ".$styleTablaHeader."'><b>Folio Factura</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b></b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Folio Recibo</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Contribuyente</b></th>
					
					<th colspan='2' style='text-align:center; ".$styleTablaHeader."'><b>Detalle</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Forma de pago</b></th>
					<th style='text-align:center; display: none; ".$styleTablaHeader."'><b>Pago Recibo</b></th>
					<!--th style='text-align:center; ".$styleTablaHeader."'><b>Monto</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>IVA</b></th-->
					<th style='text-align:center; ".$styleTablaHeader."'><b>Monto</b></th>
				</tr>
	";
	//Agregar Recibos con factura
	// <th style='text-align:center; ".$styleTablaHeader."'><b>Usuario</b></th>
	$total = 0;
	$totaltotales2 = 0;
	$accounanterior = "";
	$totalamount = 0;
	$totaliva = 0;
	$totaltotales2 = 0;
	$Ttotalamount = 0;
	$Ttotaliva = 0;
	$Ttotaltotales2 = 0;
	$total = "";
	$k = 0;
	$i = 0;
	$typenameanterior = "";
	$tipoant = "";

	$paymentnameOrder = "";
	$paymentnameOrderAnt = "";
	$paymentnameOrderMonto = 0;
	$paymentnameOrderIva = 0;
	$paymentnameOrderTotal = 0;

	$facturasSinReciboMsj = 0;
	$numRegistro = 1;				
	while ( $myrow = DB_fetch_array ( $TransResult ) ) {
		$i = $i + 1;
		if ($myrow ['reference'] != "") {
			$arrreference = explode ( "-", $myrow ['reference'] );
			$u_tipo = $arrreference [0];
		} else {
			$u_tipo = $myrow ['type'];
		}

		//Total por metodo de pago
		$paymentnameOrder = trim($myrow ['paymentnameOrder']);
		$MostrarMetodoPago = 0;
		if ($paymentnameOrder != $paymentnameOrderAnt and !empty($paymentnameOrder)) {
			//echo "<tr><td colspan='11'><b>" . $paymentnameOrder . "&nbsp;</b></td></tr>";
			$MostrarMetodoPago = 1;
		}

		if ($paymentnameOrder != $paymentnameOrderAnt and !empty($paymentnameOrder)) {
			if ($paymentnameOrderMonto > 0 && $paymentnameOrderIva > 0 && $paymentnameOrderTotal > 0) {
				$htmlPDF .= "<tr>";
				$htmlPDF .= "<td colspan='6' style='text-align: right'><b>Subtotal " .  " : &nbsp;</b></td>";
				//echo "<td class='number'><b>" . number_format ( $paymentnameOrderMonto, 2 ) . "</b></td>";
				//echo "<td class='number'><b>" . number_format ( $paymentnameOrderIva, 2 ) . "</b></td>";
				$htmlPDF .= "<td class='number'><b>" . number_format ( $paymentnameOrderTotal, 2 ) . "</b></td>";
				$htmlPDF .= "</tr>";
				$paymentnameOrderMonto = 0;
				$paymentnameOrderIva = 0;
				$paymentnameOrderTotal = 0;
			}
		}

		// if ($myrow['typename'] != $typenameanterior)
		if ($u_tipo != $tipoant) {
			if ($i != 1) {
				$htmlPDF .= "<tr>";
				$htmlPDF .= "<td colspan='6' style='text-align: right'><b>Total"  . " : &nbsp;</b></td>"; //strtoupper ( $_SESSION ['systypes'] [intval ( $tipoant )] ) 
				//echo "<td class='number'><b>" . number_format ( $totalamount, 2 ) . "</b></td>";
				//echo "<td class='number'><b>" . number_format ( $totaliva, 2 ) . "</b></td>";
				$htmlPDF .= "<td class='number'><b>" . number_format ( $totaltotales2, 2 ) . "</b></td>";
				$htmlPDF .= "</tr>";
				//echo "<tr class='bg-success'><td colspan='11'><b>" . strtoupper ( $_SESSION ['systypes'] [intval ( $u_tipo )] ) . "&nbsp;</b></td></tr>";
			} else {
				//echo "<tr class='bg-success'><td colspan='11'><b>" . strtoupper ( $_SESSION ['systypes'] [intval ( $u_tipo )] ) . "&nbsp;</b></td></tr>";
			}

			$tipoant = $u_tipo;
			$typenameanterior = $myrow ['typename'];
			$totalamount = 0;
			$totaliva = 0;
			$totaltotales2 = 0;
		}

		//Mostrar mensaje de facturas sin recibo
		if ($myrow ['ordenar'] == "sin recibo" and $facturasSinReciboMsj == 0 ) {
			$htmlPDF .= "<tr class=''><td colspan='7'><b>" . "" . "&nbsp;</b></td></tr>";
			$htmlPDF .= "<tr class='bg-success'><td colspan='7'><b>" . "Facturas Sin Recibo" . "&nbsp;</b></td></tr>";
			$facturasSinReciboMsj = 1;
		}

		//Mostrar le metodo de pago
		$paymentnameOrderAnt = $paymentnameOrder;
		if ($MostrarMetodoPago == 1) {
			$htmlPDF .= "<tr class='bg-info'><td colspan='7'><b>" . ($paymentnameOrder) . "&nbsp;</b></td></tr>";
		}
		
		if ($myrow ['pendiente'] == 1) {
			$htmlPDF .= "<tr class='danger'>";
		} else {
			$htmlPDF .= "<tr>";
		}

		if (($myrow ['type'] == 90) or ($myrow ['type'] == 21)) {
			$xtotal = abs ( $myrow ['ovamount'] + $myrow ['ovgst'] );
		} else {
			$xtotal = abs ( $myrow ['ovamount'] + $myrow ['ovgst'] ) - abs ( $myrow ['asignado'] );
			
			$xtotal = $myrow ['montoRecibo'];
		}
		
		//if ($myrow['reference'] == '21'){ $varrate = 1; }else{
		$varrate = $myrow ['rate'];
		// }
		
		// echo "<td style='font-size:8pt;'>" . $_SESSION['systypes'][intval($u_tipo)] . "<span style='font-size:7pt; font-weight:bold;'></span>" . "</td>";
		$nameuser = $myrow ['realname'];
		if (empty($myrow ['realname'])) {
			$sql = "SELECT userid, realname FROM www_users WHERE userid = '".$myrow ['userid']."'";
			$ErrMsg = _ ( 'Error al obtener los datos de la factura');
			$rowUser = DB_query ( $sql, $db, $ErrMsg );
			if (DB_num_rows($rowUser) > 0) {
				$myrowUser= DB_fetch_array ( $rowUser );
				$myrow ['realname'] = $myrowUser['realname'];
			}
		}
		$htmlPDF .= "<td style='text-align:center; display: none;'>" . $myrow ['datosfactura_Folio'] . "</td>";
		$htmlPDF .= "<td style='text-align:center;'>" . $numRegistro . "</td>";
		$htmlPDF .= "<td style='text-align:center;'>" . $myrow ['foliorecibo'] . "</td>";
		$htmlPDF .= "<td style='text-align:center;'>" . $myrow ['debtorno'] . " - " . $myrow ['name'] . "</td>";
		// $htmlPDF .= "<td style='text-align:center;'>" . $myrow ['realname'] . "</td>";
		$htmlPDF .= "<td colspan='2' style='text-align:center;'>" . $myrow ['invtext'] . "</td>";

		$numRegistro ++;

		//Detalles de la factura, obtener nombre del metodo de pago
		$ban_efectivo = 0;
		$ban_cheque = 0;
		$ban_credito = 0;
		$ban_debito = 0;
		$ban_transferencia = 0;
		$ban_tarjetas = 0;
		$num_pagoFactura = 0;
		$name_efectivo = "";
		$name_cheque = "";
		$name_trasnferencia = "";
		$name_credito = "";
		$name_debito = "";
		if (empty($myrow ['datosfactura_codesat'])) {
			//evitar error en consulta
			$myrow ['datosfactura_codesat'] = "''";
		}
		$sql = "SELECT paymentmethodssat.paymentid, paymentmethodssat.paymentname FROM paymentmethodssat WHERE paymentmethodssat.paymentid in (".$myrow ['datosfactura_codesat'].")";
		$ErrMsg = _ ( 'Error al obtener los datos de la factura');
		$rowFactura = DB_query ( $sql, $db, $ErrMsg );
		$paymentname = "";
		if (DB_num_rows($rowFactura) > 0) {
			while ( $myrowFactura = DB_fetch_array ( $rowFactura ) ) {
				if ($myrowFactura['paymentid'] == '01') {
					//efectivo
					$ban_efectivo = 1;
					$name_efectivo = $myrowFactura['paymentname'];
				}else if ($myrowFactura['paymentid'] == '02') {
					//cheque
					$ban_cheque = 1;
					$name_cheque = $myrowFactura['paymentname'];
				}else if ($myrowFactura['paymentid'] == '03') {
					//transferencia
					$ban_transferencia = 1;
					$name_trasnferencia = $myrowFactura['paymentname'];
				}else if ($myrowFactura['paymentid'] == '04') {
					//tarjetas de credito
					$ban_tarjetas = 1;
					$ban_credito = 1;
					$name_credito = $myrowFactura['paymentname'];
				}else if ($myrowFactura['paymentid'] == '28') {
					//tarjetas de debito
					$ban_tarjetas = 1;
					$ban_debito = 1;
					$name_debito = $myrowFactura['paymentname'];
				}
				$paymentname = $paymentname.$myrowFactura['paymentname'].",";
				$num_pagoFactura = $num_pagoFactura + 1;
			}
		}
		$paymentname = substr($paymentname, 0, strlen($paymentname)-1);
		$htmlPDF .= "<td style='text-align:center'>".($paymentname)."</td>";

		//Detalles del recibo, obtener nombre del pago
		$transnoRecibo = $myrow ['transno'];
		$sql = "SELECT companies.gltempcashpayment as efectivo, companies.gltempcheckpayment as cheque, companies.gltempccpayment as credito, companies.gltempccpayment as debito, companies.gltemptransferpayment as transferencia FROM companies";
		$ErrMsg = _ ( 'Error al obtener de las cuentas puente');
		$rowCuentas = DB_query ( $sql, $db, $ErrMsg );
		$cuentasPuente = "";
		$cuenta_efectivo = "";
		$cuenta_cheque = "";
		$cuenta_transferencia = "";
		$cuenta_credito = "";
		$cuenta_debito = "";
		if (DB_num_rows($rowCuentas) > 0) {
			$myrowCuenta = DB_fetch_array ( $rowCuentas );
			//cuentas puente
			$cuenta_efectivo = $myrowCuenta['efectivo'];
			$cuenta_cheque = $myrowCuenta['cheque'];
			$cuenta_transferencia = $myrowCuenta['transferencia'];
			$cuenta_credito = $myrowCuenta['credito'];
			$cuenta_debito = $myrowCuenta['debito'];

			if ($ban_efectivo == 1) {
				$cuentasPuente = $cuentasPuente.$myrowCuenta['efectivo'].",";
			}
			if ($ban_cheque == 1) {
				$cuentasPuente = $cuentasPuente.$myrowCuenta['cheque'].",";
			}
			if ($ban_transferencia == 1) {
				$cuentasPuente = $cuentasPuente.$myrowCuenta['transferencia'].",";
			}
			if ($ban_credito == 1) {
				$cuentasPuente = $cuentasPuente.$myrowCuenta['credito'].",";
			}
			if ($ban_debito == 1) {
				$cuentasPuente = $cuentasPuente.$myrowCuenta['debito'].",";
			}
			$cuentasPuente = substr($cuentasPuente, 0, strlen($cuentasPuente)-1);
		}
		if (empty($cuentasPuente)) {
			//evitar error en consulta
			$cuentasPuente = "''";
		}
		$sql = "SELECT account FROM gltrans WHERE type='12' and typeno = '".$transnoRecibo."' and account in ('".$cuentasPuente."')";
		$ErrMsg = _ ( 'Error al obtener de las cuentas puente');
		$rowCuentas = DB_query ( $sql, $db, $ErrMsg );
		$num_pagoRecibos = "";
		$cuentasPuenteName = "";
		$cuentasPuenteNameMostrar = "";
		$mostrarTarjeta = 0;
		if (DB_num_rows($rowCuentas) > 0) {
			while ( $myrowCuenta = DB_fetch_array ( $rowCuentas ) ) {
				$account = $myrowCuenta['account'];
				if ($account == $cuenta_efectivo) {
					$cuentasPuenteName = $cuentasPuenteName.$name_efectivo;
					$cuentasPuenteNameMostrar = $cuentasPuenteNameMostrar.$name_efectivo;
				}
				if ($account == $cuenta_cheque) {
					$cuentasPuenteName = $cuentasPuenteName.$name_cheque;
					$cuentasPuenteNameMostrar = $cuentasPuenteNameMostrar.$name_cheque;
				}
				if ($account == $cuenta_transferencia) {
					$cuentasPuenteName = $cuentasPuenteName.$name_trasnferencia;
					$cuentasPuenteNameMostrar = $cuentasPuenteNameMostrar.$name_trasnferencia;
				}
				if ($account == $cuenta_credito) {
					$cuentasPuenteName = $cuentasPuenteName.$name_credito;
					if ($mostrarTarjeta == 0) {
						$cuentasPuenteNameMostrar = $cuentasPuenteNameMostrar."Tarjeta";
						$mostrarTarjeta = 1;
					}
				}
				if ($account == $cuenta_debito) {
					$cuentasPuenteName = $cuentasPuenteName.$name_debito;
					if ($mostrarTarjeta == 0) {
						$cuentasPuenteNameMostrar = $cuentasPuenteNameMostrar."Tarjeta";
						$mostrarTarjeta = 1;
					}
				}
				$cuentasPuenteName = $cuentasPuenteName.",";
				$cuentasPuenteNameMostrar = $cuentasPuenteNameMostrar.",";
				$num_pagoRecibos = $num_pagoRecibos + 1;
			}
		}
		$cuentasPuenteName = substr($cuentasPuenteName, 0, strlen($cuentasPuenteName)-1);
		$cuentasPuenteNameMostrar = substr($cuentasPuenteNameMostrar, 0, strlen($cuentasPuenteNameMostrar)-1);
		$style = "background-color: red; color: white;";
		//echo "<br>orden: ".$myrow ['datosfactura_Orden']." - paymentname: ".$paymentname." - cuentasPuenteName: ".$cuentasPuenteName;
		if ($num_pagoFactura == $num_pagoRecibos and trim(($paymentname)) == trim(($cuentasPuenteName)) and (!empty($cuentasPuenteNameMostrar))) {
			$style = "";
		}
		$htmlPDF .= "<td style='text-align:center; display: none; ".$style."'>" . ($cuentasPuenteNameMostrar) . "</td>";
		//echo "<td class='number'>" . ( (abs ( $myrow ['ovamount'] ) / $varrate), 2 ) . "</td>";
		//echo "<td class='number'>" . number_format ( (abs ( $myrow ['ovgst'] ) / $varrate), 2 ) . "</td>";
		$htmlPDF .= "<td class='number'>" . number_format ( $myrow ['montoRecibo'] /*($xtotal / $varrate)*/, 2 ) . "</td>";
		$htmlPDF .= "</tr>";

		$totalamount = $totalamount + abs ( $myrow ['ovamount'] );
		$totaliva = $totaliva + abs ( $myrow ['ovgst'] );
		$totaltotales2 = $totaltotales2 + ($xtotal / $varrate);
		
		$Ttotalamount = $Ttotalamount + abs ( $myrow ['ovamount'] );
		$Ttotaliva = $Ttotaliva + abs ( $myrow ['ovgst'] );
		$Ttotaltotales2 = $Ttotaltotales2 + ($xtotal / $varrate);

		//Total por metodo de pago
		$paymentnameOrderMonto = $paymentnameOrderMonto + abs ( $myrow ['ovamount'] );
		$paymentnameOrderIva = $paymentnameOrderIva + abs ( $myrow ['ovgst'] );
		$paymentnameOrderTotal = $paymentnameOrderTotal + ($xtotal / $varrate);
	}
	//subtotal ultimo
	$htmlPDF .= "<tr>";
	$htmlPDF .= "<td colspan='6' style='text-align: right'><b>Subtotal " .  " : &nbsp;</b></td>";
	//echo "<td class='number'><b>" . number_format ( $paymentnameOrderMonto, 2 ) . "</b></td>";
	//echo "<td class='number'><b>" . number_format ( $paymentnameOrderIva, 2 ) . "</b></td>";
	$htmlPDF .= "<td class='number'><b>" . number_format ( $paymentnameOrderTotal, 2 ) . "</b></td>";
	$htmlPDF .= "</tr>";
	//total
	$htmlPDF .= "<tr>";
	$htmlPDF .= "<td colspan=6 style='text-align: right'><b>" . _ ( 'Total' ) . ' ' . ": &nbsp;</b></td>"; //strtoupper ( $_SESSION ['systypes'] [intval ( $tipoant )] )
	//echo "<td class='number'><b>" . number_format ( $totalamount, 2 ) . "</b></td>";
	//echo "<td class='number'><b>" . number_format ( $totaliva, 2 ) . "</b></td>";
	$htmlPDF .= "<td class='number'><b>" . number_format ( $totaltotales2, 2 ) . "</b></td>";
	$htmlPDF .= "</tr>";
	$htmlPDF .= "<tr><td colspan=7 style='text-align:left;'><b>" . strtoupper ( $myrow ['typename'] ) . "&nbsp;</b></td></tr>";
	$htmlPDF .= "<tr>";
	$htmlPDF .= "<td colspan=6 style='text-align: right'><b>" . _ ( 'Total Aplicación Ingresos' ) . ": &nbsp;</b></td>";
	//echo "<td class='number'><b>" . number_format ( $Ttotalamount, 2 ) . "</b></td>";
	//echo "<td class='number'><b>" . number_format ( $Ttotaliva, 2 ) . "</b></td>";
	$htmlPDF .= "<td class='number'><b>" . number_format ( $Ttotaltotales2, 2 ) . "</b></td>";
	$htmlPDF .= "</tr>";

	//Facturas pagadas con notas de credito
	$SQL = "SELECT
			datosFactura.type,
			datosFactura.transno,
			datosFactura.folio as folioFactura,
			datosFactura.codesat,
			debtortrans.debtorno,
			debtorsmaster.name,
			www_users.realname,
			debtortrans.invtext,
			debtortrans.folio,
			abs(debtortrans.ovamount + debtortrans.ovgst) as total_Ant,
			abs(custallocns.amt) as total
			FROM debtortrans
			LEFT JOIN custallocns ON custallocns.transid_allocfrom = debtortrans.id
			LEFT JOIN debtortrans datosFactura ON datosFactura.id = custallocns.transid_allocto
			LEFT JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
			LEFT JOIN www_users ON www_users.userid = debtortrans.userid
			WHERE 
			DATE_FORMAT(debtortrans.trandate, '%Y-%m-%d') = '".$fechacorte."'
			AND debtortrans.type = 11
			AND debtortrans.tagref = '".$unidadnegocio."'
			".$SQLUsuario.$SQLPrepoliza;
	if ($_SESSION['UserID'] == "admin") {
		//echo "<br><pre>".$SQL;
	}
	$ErrMsg = _ ( 'No transactions were returned by the SQL becauses' );
	$TransResult = DB_query ( $SQL, $db, $ErrMsg );
	if (DB_num_rows ( $TransResult ) > 0) {
		$htmlPDF .= "<tr class='bg-info'><td colspan='12'><b>Facturas - Nota Credito</b></td></tr>";
		$totalFacNot = 0;
		while ( $myrow = DB_fetch_array ( $TransResult ) ) {
			$htmlPDF .= "<tr>";
			//$htmlPDF .= "<td style='text-align:center'><input type='checkbox' value='".$myrow ['type']."_".$myrow ['transno']."' name='FoliosCambiar[]' /></td>";
			$htmlPDF .= "<td style='text-align:center'>" . $myrow ['folioFactura'] . "</td>";
			$htmlPDF .= "<td style='text-align:center'>" . $myrow ['realname'] . "</td>";
			$htmlPDF .= "<td style='text-align:center'>" . $myrow ['folio'] . "</td>";
			$htmlPDF .= "<td style='text-align:center'>" . $myrow ['debtorno'] . "</td>";
			$htmlPDF .= "<td style='text-align:center'>" . $myrow ['name'] . "</td>";
			$htmlPDF .= "<td style='text-align:center'>" . $myrow ['invtext'] . "</td>";

			$paymentname = "";
			if (!empty($myrow['codesat'])) {
				$sql = "SELECT paymentmethodssat.paymentid, paymentmethodssat.paymentname FROM paymentmethodssat WHERE paymentmethodssat.paymentid in (".$myrow ['codesat'].")";
				$ErrMsg = _ ( 'Error al obtener los datos de la factura');
				$rowFactura = DB_query ( $sql, $db, $ErrMsg );
				if (DB_num_rows($rowFactura) > 0) {
					while ( $myrowFactura = DB_fetch_array ( $rowFactura ) ) {
						$paymentname = $paymentname.$myrowFactura['paymentname'].",";
					}
				}
				$paymentname = substr($paymentname, 0, strlen($paymentname)-1);
			}
			
			$htmlPDF .= "<td style='text-align:center'>".($paymentname)."</td>";
			$htmlPDF .= "<td style='text-align:center'>" . "" . "</td>";
			$htmlPDF .= "<td class='number'>" . number_format ( $myrow ['total'], 2 ) . "</td>";
			$htmlPDF .= "</tr>";

			$totalFacNot += $myrow ['total'];
		}

		$htmlPDF .= "<tr>";
		$htmlPDF .= "<td colspan='8' style='text-align: right'><b>TOTAL " .  " : &nbsp;</b></td>";
		//$htmlPDF .= "<td class='number'><b>" . number_format ( $paymentnameOrderMonto, 2 ) . "</b></td>";
		//$htmlPDF .= "<td class='number'><b>" . number_format ( $paymentnameOrderIva, 2 ) . "</b></td>";
		$htmlPDF .= "<td class='number'><b>" . number_format ( $totalFacNot, 2 ) . "</b></td>";
		$htmlPDF .= "</tr>";
	}
	
	$htmlPDF .= "
			</table>
		</div>
	</div>
	";
	
	/*
	 * INICIO MUESTRA LAS FACTURAS DE Credito SOLO POR CARACTER INFORMATIVO
	 */
	$SQL = "SELECT systypescat.typename,
		debtortrans.reference,
		debtortrans.ovamount,
		debtortrans.ovgst,
		debtortrans.alloc,
		debtortrans.invtext,
		debtortrans.trandate,
		debtortrans.transno, 
		debtorsmaster.name,
		debtortrans.alloc as asignado,
		debtortrans.folio
		FROM debtortrans, systypescat, debtorsmaster
		WHERE debtortrans.type = systypescat.typeid
		AND day(debtortrans.origtrandate) = " . $diacorte . "
		AND month(debtortrans.origtrandate) = " . $mescorte . "
		and year(debtortrans.origtrandate) = " . $aniocorte . "
		AND debtortrans.type in (10,119)
		AND debtortrans.tagref = " . $unidadnegocio . "
		AND debtortrans.debtorno = debtorsmaster.debtorno
		AND debtortrans.ovamount <> 0";
	if ($_SESSION['UserID'] == "admin") {
		//echo "<br><pre>Nota Informativa Facturas de Credito: ".$SQL."<br>";
	}
	$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
	$TransResult = DB_query ( $SQL, $db, $ErrMsg );

	$htmlPDF .= "
	<div class='container-fluid' style='display: none;'>
		<div class='panel panel-primary'>
			<!-- Default panel contents -->
			<div class='panel-heading' align='center' ".$styleTablaTitulo."> Nota Informativa Facturas de Credito</div>
			<!--<div class='panel-body'>
			</div>-->
			<!-- Table -->
			<table class='table table-hover' ".$styleTabla.">
				<tr>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Tipo</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Folio</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Contribuyente</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Fecha</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Detalle</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Monto</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>IVA</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>SubTotal</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Anticipo</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Total</b></th>
				</tr>
	";
					$xtotalamount = 0;
					$xtotaliva = 0;
					$xtotalanticipos = 0;
					$xtotalsubtotales = 0;
					$xtotaltotales = 0;
					
					$k = 0;
					$i = 0;
					$typenameanterior = "";
					while ( $myrow = DB_fetch_array ( $TransResult ) ) {
						$i = $i + 1;
						$typenameanterior = $myrow ['typename'];
						$htmlPDF .= '<tr>';
						$ovamount = abs ( $myrow ['ovamount'] );
						$ovgst = abs ( $myrow ['ovgst'] );
						$alloc = abs ( $myrow ['alloc'] );
						$anticipo = ($ovamount + $ovgst) - $alloc;
						$subtotal = $alloc;
						$total = $subtotal + $anticipo;
						
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['typename'] . "</td>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['folio'] . "</td>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['name'] . "</td>";
						$htmlPDF .= "<td style='text-align:center'>" . substr ( $myrow ['trandate'], 0, 10 ) . "</td>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['invtext'] . "</td>";
						$htmlPDF .= "<td class='number'>" . number_format ( $ovamount, 2 ) . "</td>";
						$htmlPDF .= "<td class='number'>" . number_format ( $ovgst, 2 ) . "</td>";
						$htmlPDF .= "<td class='number'>" . number_format ( $subtotal, 2 ) . "</td>";
						$htmlPDF .= "<td class='number'>" . number_format ( $anticipo, 2 ) . "</td>";
						$htmlPDF .= "<td class='number'>" . number_format ( $total, 2 ) . "</td>";
						$htmlPDF .= "</tr>";
						
						$xtotalamount = $xtotalamount + $ovamount;
						$xtotaliva = $xtotaliva + $ovgst;
						$xtotalanticipos = $xtotalanticipos + $anticipo;
						$xtotalsubtotales = $xtotalsubtotales + $subtotal;
						$xtotaltotales = $xtotaltotales + $total;
					}
					
					$htmlPDF .= "<tr>";
					$htmlPDF .= "<td colspan=5 style='text-align:right;'><b>" . _ ( 'TOTALES' ) . ' ' . strtoupper ( $xtypenameanterior ) . ": &nbsp;</b></td>";
					$htmlPDF .= "<td class='number'><b>" . number_format ( $xtotalamount, 2 ) . "</b></td>";
					$htmlPDF .= "<td class='number'><b>" . number_format ( $xtotaliva, 2 ) . "</b></td>";
					$htmlPDF .= "<td class='number'><b>" . number_format ( $xtotalsubtotales, 2 ) . "</b></td>";
					$htmlPDF .= "<td class='number'><b>" . number_format ( $xtotalanticipos, 2 ) . "</b></td>";
					$htmlPDF .= "<td class='number'><b>" . number_format ( $xtotaltotales, 2 ) . "</b></td>";
					$htmlPDF .= "</tr>";
	
	$htmlPDF .= "
			</table>
		</div>
	</div>
	";

	/*
	 * echo '<table class="table2" cellpadding="2" width="93%">'; echo "<tr>"; echo "<td colspan=8 style='text-align:center;'>"; $diferencia = $totaltotales1 - $Ttotaltotales2; $diferencia = abs($totaltemp1 + $totaltemp2) - $Ttotaltotales2; if((abs(($totaltemp1 + $totaltemp2) - $Ttotaltotales2) < 0.9)){ echo "<input class='clsbtnimportante' type='submit' name='Procesar' value='PROCESAR'>"; }else{ prnMsg(_('No puedes realizar el corte de caja debido a que existe diferencia entre Total de Ingresos y Detalle Aplicacion de Ingresos: ' . number_format($diferencia,2)),'warn'); } echo "</td></tr></table>";
	 */
	
	/* FIN
	  MUESTRA LAS FACTURAS DE CONTADO SOLO POR CARACTER INFORMATIVO
	*/


	/* INICIO
	  MUESTRA LAS NOTAS DE CREDITO O NOTAS DE  CARGO CON ALLOC = 0;
	*/
	
	//Rango de fechas (Prepoliza)
	$SQLPrepoliza = "";
	if (!empty($fechaInicioImpresion) and !empty($fechaTerminoImpresion)) {
		$SQLPrepoliza = " and debtortrans.origtrandate between '".$fechaInicioImpresion."' and '".$fechaTerminoImpresion."' ";
	}
	$SQL = "SELECT systypescat.typename,
		debtortrans.reference,
		debtortrans.ovamount,
		debtortrans.ovgst,
		debtortrans.alloc,
		debtortrans.invtext,
		debtortrans.reference,
		debtortrans.trandate,
		debtortrans.transno, 
		debtortrans.folio, 
		debtorsmaster.name,
		debtortrans.alloc as asignado
		FROM debtortrans, systypescat, debtorsmaster
		WHERE debtortrans.type = systypescat.typeid
		AND day(debtortrans.origtrandate) = " . $diacorte . "
		AND month(debtortrans.origtrandate) = " . $mescorte . "
		and year(debtortrans.origtrandate) = " . $aniocorte . "
		AND (debtortrans.type = 21 or debtortrans.type = 11)
		AND debtortrans.tagref = " . $unidadnegocio . "
		AND debtortrans.debtorno = debtorsmaster.debtorno
		AND debtortrans.ovamount <> 0
		AND debtortrans.alloc = 0 ".$SQLUsuario.$SQLPrepoliza;
	if ($_SESSION['UserID'] == "admin") {
		//echo "<br><pre>Nota Informativa Notas de Cargo o Notas de Credito: ".$SQL."<br>";
	}
	$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
	$TransResult = DB_query ( $SQL, $db, $ErrMsg );

	$htmlPDF .= "
	<div class='container-fluid' style='display: none;'>
		<div class='panel panel-primary'>
			<!-- Default panel contents -->
			<div class='panel-heading' align='center' ".$styleTablaTitulo."> Nota Informativa Notas de Cargo o Notas de Credito</div>
			<!--<div class='panel-body'>
			</div>-->
			<!-- Table -->
			<table class='table table-hover' ".$styleTabla.">
				<tr>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Tipo</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Folio</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Contribuyente</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Fecha</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Detalle</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Monto</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>IVA</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Total</b></th>
				</tr>
	";
					$xtotalamount = 0;
					$xtotaliva = 0;
					$xtotalanticipos = 0;
					$xtotalsubtotales = 0;
					$xtotaltotales = 0;
					
					$k = 0;
					$i = 0;
					$typenameanterior = "";
					while ( $myrow = DB_fetch_array ( $TransResult ) ) {
						$i = $i + 1;
						$typenameanterior = $myrow ['typename'];
						$htmlPDF .= "<tr>";
						$ovamount = abs ( $myrow ['ovamount'] );
						$ovgst = abs ( $myrow ['ovgst'] );
						$alloc = abs ( $myrow ['alloc'] );
						$anticipo = ($ovamount + $ovgst) - $alloc;
						$subtotal = $alloc;
						$total = $subtotal + $anticipo;
						
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['typename'] . "</td>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['folio'] . "</td>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['name'] . "</td>";
						$htmlPDF .= "<td style='text-align:center' nowrap>" . substr ( $myrow ['trandate'], 0, 10 ) . "</td>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['reference'] . "</td>";
						$htmlPDF .= "<td class='number'>" . number_format ( $ovamount, 2 ) . "</td>";
						$htmlPDF .= "<td class='number'>" . number_format ( $ovgst, 2 ) . "</td>";
						// echo "<td style='font-size:8pt;' class='number'>" . number_format($subtotal,2) . "</td>";
						// echo "<td style='font-size:8pt;' class='number'>" . number_format($anticipo,2) . "</td>";
						$htmlPDF .= "<td class='number'>" . number_format ( $total, 2 ) . "</td>";
						$htmlPDF .= "</tr>";
						
						$xtotalamount = $xtotalamount + $ovamount;
						$xtotaliva = $xtotaliva + $ovgst;
						$xtotalanticipos = $xtotalanticipos + $anticipo;
						$xtotalsubtotales = $xtotalsubtotales + $subtotal;
						$xtotaltotales = $xtotaltotales + $total;
					}
					
					$htmlPDF .= "<tr>";
					$htmlPDF .= "<td colspan=5 style='text-align:right;'><b>" . _ ( 'TOTALES' ) . ' ' . strtoupper ( $xtypenameanterior ) . ": &nbsp;</b></td>";
					$htmlPDF .= "<td class='number'><b>" . number_format ( $xtotalamount, 2 ) . "</b></td>";
					$htmlPDF .= "<td class='number'><b>" . number_format ( $xtotaliva, 2 ) . "</b></td>";
					// echo "<td class='number'><b>" . number_format($xtotalsubtotales,2) . "</b></td>";
					// echo "<td class='number'><b>" . number_format($xtotalanticipos,2) . "</b></td>";
					$htmlPDF .= "<td class='number'><b>" . number_format ( $xtotaltotales, 2 ) . "</b></td>";
					$htmlPDF .= "</tr>";
	
	$htmlPDF .= "
			</table>
		</div>
	</div>
	";
	
	/**
	 * **************************************************************************
	 * MUESTRA LOS ANTICIPOS DEL DIA
	 * Se requiere que las aplicaciones de anticipos del d�a se reporten en las notas informativas del corte de caja, porque por el momento no aparecen en ninguna parte.
	 */
	$SQLUsuario = "";
	if ($_POST['usuario'] != 'all') {
		$SQLUsuario = " AND debtortrans.userid = '".$_POST['usuario']."' ";
	}
	//Rango de fechas (Prepoliza)
	$SQLPrepoliza = "";
	if (!empty($fechaInicioImpresion) and !empty($fechaTerminoImpresion)) {
		$SQLPrepoliza = " and debtortrans.origtrandate between '".$fechaInicioImpresion."' and '".$fechaTerminoImpresion."' ";
	}
	$fechaCorte = $aniocorte . "-" . $mescorte . "-" . $diacorte;
	// Me falta poner el filtro de Ur
	$SQL = " SELECT debtortrans.debtorno,debtorsmaster.name, debtortrans.folio as folioDoc, 
			custallocns.amt,debtor.folio,systypescat.typename, debtortrans.trandate, debtortrans.invtext, debtortrans.reference
			FROM custallocns JOIN debtortrans ON custallocns.transid_allocfrom = debtortrans.id
			JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
			JOIN systypescat ON systypescat.typeid = debtortrans.type
      		JOIN debtortrans debtor ON debtor.id= custallocns.transid_allocto
			AND systypescat.typeid = debtortrans.type
			WHERE debtortrans.type in (80,13,11)
			AND custallocns.datealloc = '" . $fechaCorte . "'
			AND debtortrans.tagref = " . $unidadnegocio . $SQLUsuario . $SQLPrepoliza . "
			GROUP BY debtortrans.id";
	if ($_SESSION['UserID'] == "admin") {
		//echo "<br><pre>Anticipo Clientes, Notas de Credito Directa, Nota de Credito: ".$SQL."<br>";
	}
	$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
	
	$TransResult = DB_query ( $SQL, $db, $ErrMsg );

	$htmlPDF .= "
	<div class='container-fluid' style='display: none;'>
		<div class='panel panel-primary'>
			<!-- Default panel contents -->
			<div class='panel-heading' align='center' ".$styleTablaTitulo."> Anticipo Clientes, Notas de Credito Directa, Nota de Credito</div>
			<!--<div class='panel-body' ".$styleTabla.">
			</div>-->
			<!-- Table -->
			<table class='table table-hover'>
				<tr>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Anticipo</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Folio</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Codigo</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Contribuyente</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Fecha</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Detalle</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Factura</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Monto</b></th>
				</tr>
	";
					$total = 0;
					while ( $myrow = DB_fetch_array ( $TransResult ) ) {
						$htmlPDF .= "<tr>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['typename'] . "</td>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['folioDoc'] . "</td>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['debtorno'] . "</td>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['name'] . "</td>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['trandate'] . "</td>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['reference'] . "</td>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['folio'] . "</td>";
						$htmlPDF .= "<td class='number'>" . number_format ( $myrow ['amt'], 2 ) . "</td>";
						$htmlPDF .= "</tr>";
						
						$total = $total + $myrow ['amt'];
					}
					
					$htmlPDF .= "<tr>";
					$htmlPDF .= "<td colspan=7 style='text-align:right;'><b>" . _ ( 'TOTALES' ) . ": &nbsp;</b></td>";
					$htmlPDF .= "<td class='number'><b>" . number_format ( $total, 2 ) . "</b></td>";
					$htmlPDF .= "</tr>";
	
	$htmlPDF .= "
			</table>
		</div>
	</div>
	";

	/**
	 * **************************************************************************
	 * MUESTRA LAS DEVOLUCIONES DE LOS ANTICIPOS
	 */
	$SQLUsuario = "";
	if ($_POST['usuario'] != 'all') {
		$SQLUsuario = " and debtortransmovs.userid = '".$_POST['usuario']."' ";
	}
	//Rango de fechas (Prepoliza)
	$SQLPrepoliza = "";
	if (!empty($fechaInicioImpresion) and !empty($fechaTerminoImpresion)) {
		$SQLPrepoliza = " and debtortransmovs.origtrandate between '".$fechaInicioImpresion."' and '".$fechaTerminoImpresion."' ";
	}
	$SQL = "SELECT 
					systypescat.typename,
					gltrans.trandate, 
					gltrans.periodno, 
					gltrans.account,
					chartmaster.accountname, 
					gltrans.narrative,
					gltrans.typeno,
					debtortransmovs.userid,
					SUM(gltrans.amount) as amount,
					debtorsmaster.debtorno,
					debtorsmaster.name,
					debtortrans.folio,
					debtortrans.reference
					FROM debtortransmovs
					LEFT JOIN systypescat ON systypescat.typeid = debtortransmovs.type
					LEFT JOIN gltrans ON gltrans.type = 4 and gltrans.typeno = debtortransmovs.transno
					LEFT JOIN chartmaster ON chartmaster.accountcode = gltrans.account
					LEFT JOIN debtorsmaster ON debtorsmaster.debtorno = debtortransmovs.debtorno
					LEFT JOIN debtortrans ON debtortrans.type = debtortransmovs.type and debtortrans.transno = debtortransmovs.transno
					WHERE 
					DATE_FORMAT(debtortransmovs.trandate, '%Y-%m-%d') = '".$fechaCorte."'
					".$SQLUsuario.$SQLPrepoliza."
					and debtortransmovs.tagref = '".$unidadnegocio."'
					and gltrans.tag = '".$unidadnegocio."' 
					and debtortransmovs.type = 4
					and gltrans.account in (".$cadenaDevolucion.")
					and gltrans.narrative not like '%cancelado%'
					and debtortrans.invtext not like '%cancela%'
					and gltrans.amount > 0 
					and gltrans.narrative not like '% IVA %'
					GROUP BY gltrans.counterindex";
	if ($_SESSION['UserID'] == "admin") {
		//echo "<br><pre>Notas de Devolucion de Anticipos: ".$SQL."<br>";
	}
	$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
	
	$TransResult = DB_query ( $SQL, $db, $ErrMsg );

	$htmlPDF .= "
	<div class='container-fluid' style='display: none;'>
		<div class='panel panel-primary'>
			<!-- Default panel contents -->
			<div class='panel-heading' align='center' ".$styleTablaTitulo."> Notas de Devolucion de Anticipos</div>
			<!--<div class='panel-body'>
			</div>-->
			<!-- Table -->
			<table class='table table-hover' ".$styleTabla.">
				<tr>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Nota</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Folio</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Codigo</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Contribuyente</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Fecha</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Detalle</b></th>
					<!--th style='text-align:center; ".$styleTablaHeader."'><b>Factura</b></th-->
					<th style='text-align:center; ".$styleTablaHeader."'><b>Monto</b></th>
				</tr>
	";
					$total = 0;
					while ( $myrow = DB_fetch_array ( $TransResult ) ) {
						$htmlPDF .= "<tr>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['typename'] . "</td>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['folio'] . "</td>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['debtorno'] . "</td>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['name'] . "</td>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['trandate'] . "</td>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['reference'] . "</td>";
						//echo "<td style='text-align:center'>" . $myrow ['folio'] . "</td>";
						$htmlPDF .= "<td class='number'>" . number_format ( $myrow ['amount'], 2 ) . "</td>";
						$htmlPDF .= "</tr>";

						$total = $total + $myrow ['amount'];
					}

					$htmlPDF .= "<tr>";
					$htmlPDF .= "<td colspan=6 style='text-align:right;'><b>" . _ ( 'TOTALES' ) . ": &nbsp;</b></td>";
					$htmlPDF .= "<td class='number'><b>" . number_format ( $total, 2 ) . "</b></td>";
					$htmlPDF .= "</tr>";
	
	$htmlPDF .= "
			</table>
		</div>
	</div>
	";

	$SQL = "SELECT u_detallecortecaja,
		referencia,
		cuentapuente,
		cuentacheques,
		monto,
		fechacorte,
		u_status,
		cm1.accountname as accountname1,
		cm2.accountname as accountname2
		FROM usrdetallecortecaja as dc, chartmaster as cm1, chartmaster as cm2
		WHERE dc.u_cortecaja = " . $u_cortecaja . "
		and dc.cuentapuente = cm1.accountcode
		and dc.cuentacheques = cm2.accountcode AND monto>0";

		// para sobrante
	$SQL2 = "SELECT u_detallecortecaja,
		referencia,
		cuentapuente,
		cuentacheques,
		monto,
		fechacorte,
		u_status,
		cm1.accountname as accountname1,
		cm2.accountname as accountname2
		FROM usrdetallecortecaja as dc, chartmaster as cm1, chartmaster as cm2
		WHERE dc.u_cortecaja = " . $u_cortecaja . "
		and dc.cuentapuente = cm1.accountcode
		and dc.cuentacheques = cm2.accountcode AND monto<0";
	if ($_SESSION['UserID'] == "admin") {
	//echo "<br><pre>Notas de Devolucion de Anticipos: ".$SQL."<br>";
	}
	$ErrMsg = _ ( 'No transactions were returned by the SQL because' );

	$TransResult = DB_query ( $SQL, $db, $ErrMsg );
	$TransResult2 = DB_query ( $SQL2, $db, $ErrMsg );

	$htmlPDF .= "
	<div class='container-fluid' style='display: none;'>
		<div class='panel panel-primary'>
			<!-- Default panel contents -->
			<div class='panel-heading' align='center' ".$styleTablaTitulo."> Movimientos a Cuenta de Cheques</div>
			<!--<div class='panel-body'>
			</div>-->
			<!-- Table -->
			<table class='table table-hover' ".$styleTabla.">
				<tr>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Referencia</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Origen</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Destino</b></th>
					<th style='text-align:center; ".$styleTablaHeader."'><b>Cantidad</b></th>
				</tr>
	";
					$total = 0;
					while ( $myrow = DB_fetch_array ( $TransResult ) ) {
						$htmlPDF .= "<tr>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['referencia'] . "</td>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['accountname1'] . "</td>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['accountname2'] . "</td>";
						$htmlPDF .= "<td class='number'>" . number_format ( $myrow ['monto'], 2 ) . "</td>";
						$htmlPDF .= "</tr>";

						$total = $total + $myrow ['monto'];
					}

					$htmlPDF .= "<tr>";
					$htmlPDF .= "<td colspan=3 style='text-align:right;'><b>" . _ ( 'TOTALES' ) . ": &nbsp;</b></td>";
					$htmlPDF .= "<td class='number'><b>" . number_format ( $total, 2 ) . "</b></td>";
					$htmlPDF .= "</tr>";

					while ( $myrow = DB_fetch_array ( $TransResult2 ) ) {
						$htmlPDF .= "<tr>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['referencia'] . "</td>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['accountname1'] . "</td>";
						$htmlPDF .= "<td style='text-align:center'>" . $myrow ['accountname2'] . "</td>";
						$htmlPDF .= "<td class='number'>" . number_format ( abs($myrow ['monto']), 2 ) . "</td>";
						$htmlPDF .= "</tr>";

						$total = $total + $myrow ['monto'];
					}
	
	$htmlPDF .= "
			</table>
		</div>
	</div>
	";

	//****Datos PDF
	require_once("lib/dompdf/dompdf_config.inc.php");

	$SQL = "SELECT www_users.userid, www_users.realname
			FROM usrcortecaja, www_users
			WHERE usrcortecaja.fechacorte = STR_TO_DATE('" . $fechacorte . "', '%Y-%m-%d')
			and usrcortecaja.userid = www_users.userid
			and usrcortecaja.u_cortecaja = " . $u_cortecaja;
	$ErrMsg = _ ( 'No har registros de la consulta ' );
	$TransResult = DB_query ( $SQL, $db, $ErrMsg );

	while ( $StmtHeader = DB_fetch_array ( $TransResult ) ) {
		$userid = $StmtHeader ['userid'];
		$realname = $StmtHeader ['realname'];
	}

	$sqlLogo = "SELECT CONCAT(tags.tagref, ' - ', tags.tagdescription) as tagdescription, legalbusinessunit.address1, legalbusinessunit.address2, legalbusinessunit.address3, 
	legalbusinessunit.address4, legalbusinessunit.address5, legalbusinessunit.telephone, legalbusinessunit.fax, legalbusinessunit.logo
	FROM tags
	LEFT JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
	WHERE tags.tagref = '".$unidadnegocio."'";
    $resultLogo = DB_query ( $sqlLogo, $db, '', '', False, False );
    $row = DB_fetch_array ( $resultLogo );

    $unidadejecutoraDes = "";
    if (!empty($unidadejecutora)) {
    	$SQL = "SELECT CONCAT(ue, ' - ', desc_ue) as desc_ue FROM tb_cat_unidades_ejecutoras
    	WHERE ue = '".$unidadejecutora."'";
    	$resultUe = DB_query ( $SQL, $db, '', '', False, False );
    	$rowUe = DB_fetch_array ( $resultUe );
    	$unidadejecutoraDes = "UE: ".$rowUe['desc_ue'];
    }

    $sqlNameUsu = "SELECT realname FROM www_users WHERE userid = '".$_SESSION['UserID']."'";
    $resulNameUsu = DB_query ( $sqlNameUsu, $db, '', '', False, False );
    $rowNameUsu = DB_fetch_array ( $resulNameUsu );

	$img = "";
	if (file_exists($row['logo'])) {
		$img = "<img src='".$row["logo"]."' width='150' height='80' style='margin-left: 15px; margin-top: -25px;' title='".$row['logo']."'>";
	}

	$style = "style='font-size: 13px;'";

	$Header .= $img."";
    $Header .= "<p style='font-size: 20px; width: 100%; text-align: center; padding-top: -45px;'>Corte de Caja</p>";
    $Header .= "<br><p style='font-size: 16px; width: 100%; text-align: center; padding-top: -30px;'>".$fechacorte."</p>";

    $Header .= '<table cellspacing="0" border="0" cellpadding="0" style="padding-top: -14px; width: 100%;">';
    $Header .= '<tr>';
    $Header .= '<td '.$style.'>'.$row['address1'].', '.$row['address2'].'</td>';
    $Header .= '<td></td>';
    $Header .= '</tr>';
    $Header .= '<tr>';
    $Header .= '<td '.$style.'>'.$row['address3'].', '.$row['address4'].', '.$row['address5'].'</td>';
    $Header .= '<td></td>';
    $Header .= '</tr>';
    if (!empty($row['telephone'])) {
    	$Header .= '<tr>';
	    $Header .= '<td '.$style.'>Teléfono: '.$row['telephone'].'</td>';
	    $Header .= '<td></td>';
	    $Header .= '</tr>';
    }

    $Header .= '<tr>';
    $Header .= '<td '.$style.'>UA: '.$row['tagdescription'].'</td>';
    $Header .= '<td '.$style.'>'.$unidadejecutoraDes.'</td>';
    $Header .= '</tr>';
    $Header .= '<tr>';
    $Header .= '<td '.$style.'>Impreso: '.date('d/m/Y').'</td>';
    // $Header .= '<td '.$style.'>Realizo Prepoliza: '.$userid . ' - ' . $realname.'</td>';
    $Header .= '<td '.$style.'>Usuario: '.$_SESSION['UserID']." - ".$rowNameUsu['realname'].'</td>';
    $Header .= '</tr>';

    //Rango de fechas (Prepoliza)
	if (!empty($fechaInicioImpresion) and !empty($fechaTerminoImpresion)) {
		$Header .= '<tr>';
	    $Header .= '<td '.$style.'>Desde: '.$fechaInicioImpresion . '</td>';
	    $Header .= '<td '.$style.'>Hasta: '.$fechaTerminoImpresion.'</td>';
	    $Header .= '</tr>';
	}

    $Header .= '</table>';

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

	