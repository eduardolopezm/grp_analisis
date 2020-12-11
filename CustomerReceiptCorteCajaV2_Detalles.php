<?php
/**
 * Corte de Caja
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 13/08/2018
 * Fecha Modificación: 13/08/2018
 * Vista para la generación del corte de caja
 */

$PageSecurity = 3;
include ('includes/session.inc');
$funcion = 1981;
$title = traeNombreFuncion($funcion, $db);
$debug_sql = false;
include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');
include ('includes/SecurityFunctions.inc');
include 'javascripts/libreriasGrid.inc';
$msg = '';
$JIBE = array('erpjibe','erpjibe_CAPA','erpjibe_DES');

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

?>

<script type="text/javascript">

	function preguntaSobrante() {
		if(confirm("Desea registrar algun sobrante?")) {
			$('#sobrante').show();
			$('#sobrante2').show();
			$('#enCajas').hide();
			$('#enCajas2').hide();

		}
		return false;
	}
</script>
<?php
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

if (isset ( $_POST ['ToYear'] )) {
	$ToYear = $_POST ['ToYear'];
} else {
	$ToYear = date ( 'Y' );
}

if (isset ( $_POST ['ToMes'] )) {
	$ToMes = $_POST ['ToMes'];
} else {
	$ToMes = date ( 'm' );
}
if (isset ( $_POST ['ToDia'] )) {
	$ToDia = $_POST ['ToDia'];
} else {
	$ToDia = date ( 'd' );
}

if (isset($_POST['cmbHora'])) {
    $cmbHora = $_POST['cmbHora'];
} else {
    $cmbHora = date('h');
}

if (isset($_POST['cmbMinuto'])) {
    $cmbMinuto = $_POST['cmbMinuto'];
} else {
    $cmbMinuto = date('i');
}

if (isset($_POST['amount2'])) {
    $cantidadSobrante = $_POST['amount2'];
}

$horaPrepoliza = $cmbHora.":".$cmbMinuto.":00";

//var_dump($_POST);
$verificarSql = "";

if (isset ( $_POST ['btnAbrir'] )) {
	$arrbtnabrir = explode ( "_", $_POST ['btnAbrir'] );
	if ($_POST ['chkabrircorte_' . $arrbtnabrir [1]] != "") {
		//Eliminar poliza de gltrans
		$MSQL = "SELECT usrdetallecortecaja.referencia, usrdetallecortecaja.cuentapuente, usrdetallecortecaja.cuentacheques, 
				DATE_FORMAT(usrcortecaja.fechacorte, '%Y-%m-%d') as fechacorte, usrcortecaja.u_cortecaja, usrcortecaja.tag
				FROM usrdetallecortecaja 
				LEFT JOIN usrcortecaja ON usrcortecaja.u_cortecaja = usrdetallecortecaja.u_cortecaja
				WHERE usrdetallecortecaja.u_cortecaja = '".$arrbtnabrir [1]."'";
		$Result = DB_query ( $MSQL, $db );
		if (DB_num_rows ( $Result ) > 0) {
			$myrow = DB_fetch_array ( $Result );
			$referencia = $myrow['referencia'];
			$cuentapuente = $myrow['cuentapuente'];
			$cuentacheques = $myrow['cuentacheques'];
			$feCorte = $myrow['fechacorte'];
			$tag = $myrow['tag'];

			$MSQL = "DELETE FROM gltrans WHERE type='120' and account in ('".$cuentapuente."', '".$cuentacheques."') 
					and tag = '".$tag."' and trandate = '".$feCorte."'";
			$Result = DB_query ( $MSQL, $db );

			$MSQL = "DELETE FROM usrdetallecortecaja WHERE u_cortecaja = '".$arrbtnabrir [1]."'";
			$Result = DB_query ( $MSQL, $db );
		}

		$MSQL = 'UPDATE usrcortecaja
		SET u_status = 0
		WHERE u_cortecaja = ' . $arrbtnabrir [1];
		if (! $Result = DB_query ( $MSQL, $db )) {
			$ErrMsg = _ ( 'ERROR CRITICO' ) . '! ' . _ ( 'ANOTE ESTE ERROR Y BUSQUE AYUDA' ) . ': ' . _ ( 'El registro de transaccion de corte da caja no se actualizo' );
			$DbgMsg = _ ( 'El siguiente SQL fue utilizado' );
		}
	}
}

if (isset( $_POST ['btnCambiarUsuario'] )) {
	$_POST ['Editar'] = "yes";
	if (!empty($_POST['FoliosCambiar'])  and $_POST['cmbCambiarUsuario'] != 'all') {
		foreach ($_POST['FoliosCambiar'] as $FoliosCambiar) {
			$Datos = explode('_', $FoliosCambiar);
			//echo "<br>type: ".$Datos[0];
			//echo "<br>transno: ".$Datos[1];
			$SQL = "SELECT id, transno, type FROM debtortrans WHERE type = '".$Datos[0]."' AND transno = '".$Datos[1]."'";
			$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
			$TransResult = DB_query ( $SQL, $db, $ErrMsg );
			$idFactura = "";
			$typeFactura = "";
			$transnoFactura = "";
			while ( $myrow = DB_fetch_array ( $TransResult ) ) {
				$idFactura = $myrow['id'];
				$typeFactura = $myrow['type'];
				$transnoFactura = $myrow['transno'];
			}

			//Actualizar usuario debtortrans factura
			$SQL = "UPDATE debtortrans SET userid = '".$_POST['cmbCambiarUsuario']."' WHERE type = '".$typeFactura."' AND transno = '".$transnoFactura."'";
			$DbgMsg = _ ( 'El SQL fallo al actualizar usuario:' );
			$ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable' );
			$TransResult = DB_query ( $SQL, $db, $ErrMsg, $DbgMsg, true );
			//Actualizar usuario gltrans factura
			$SQL = "UPDATE gltrans SET userid = '".$_POST['cmbCambiarUsuario']."' WHERE type = '".$typeFactura."'  AND typeno = '".$transnoFactura."'";
			$DbgMsg = _ ( 'El SQL fallo al actualizar usuario:' );
			$ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable' );
			$TransResult = DB_query ( $SQL, $db, $ErrMsg, $DbgMsg, true );
			//Actualizar usuario debtortransmovs factura
			$SQL = "SELECT * FROM debtortransmovs WHERE type = '".$typeFactura."' AND transno = '".$transnoFactura."'";
			//echo "<br>select1 ".$SQL;
			$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
			$TransResult = DB_query ( $SQL, $db, $ErrMsg );
			if (DB_num_rows ( $TransResult ) > 0) {
				$SQL = "UPDATE debtortransmovs SET userid = '".$_POST['cmbCambiarUsuario']."' WHERE type = '".$typeFactura."' AND transno = '".$transnoFactura."'";
				$DbgMsg = _ ( 'El SQL fallo al actualizar usuario:' );
				$ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable' );
				$TransResult = DB_query ( $SQL, $db, $ErrMsg, $DbgMsg, true );	
			}

			//Obtener datos de recibo
			$SQL = "SELECT transid_allocfrom FROM custallocns WHERE transid_allocto = '".$idFactura."'";
			//echo "<br>select2 ".$SQL;
			$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
			$TransResult = DB_query ( $SQL, $db, $ErrMsg );
			$transid_allocfrom = "";
			while ( $myrow = DB_fetch_array ( $TransResult ) ) {
				$transid_allocfrom = $myrow['transid_allocfrom'];

				if (!empty($transid_allocfrom)) {
					$SQL = "SELECT id, transno, type FROM debtortrans WHERE id = '".$transid_allocfrom."'";
					//echo "<br>select3 ".$SQL;
					$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
					$TransResult2 = DB_query ( $SQL, $db, $ErrMsg );
					$idRecibo = "";
					$typeRecibo = "";
					$transnoRecibo = "";
					while ( $myrow = DB_fetch_array ( $TransResult2 ) ) {
						$idRecibo = $myrow['id'];
						$typeRecibo = $myrow['type'];
						$transnoRecibo = $myrow['transno'];
					}

					//Actualizar usuario debtortrans recibo
					$SQL = "UPDATE debtortrans SET userid = '".$_POST['cmbCambiarUsuario']."' WHERE type = '".$typeRecibo."' and transno = '".$transnoRecibo."'";
					$DbgMsg = _ ( 'El SQL fallo al actualizar usuario:' );
					$ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable' );
					$TransResult2 = DB_query ( $SQL, $db, $ErrMsg, $DbgMsg, true );
					//Actualizar usuario gltrans recibo
					$SQL = "UPDATE gltrans SET userid = '".$_POST['cmbCambiarUsuario']."' WHERE type = '".$typeRecibo."'  AND typeno = '".$transnoRecibo."'";
					$DbgMsg = _ ( 'El SQL fallo al actualizar usuario:' );
					$ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable' );
					$TransResult2 = DB_query ( $SQL, $db, $ErrMsg, $DbgMsg, true );
					//Actualizar usuario debtortransmovs recibo
					$SQL = "UPDATE debtortransmovs SET userid = '".$_POST['cmbCambiarUsuario']."' WHERE type = '".$typeRecibo."' and transno = '".$transnoRecibo."'";
					$DbgMsg = _ ( 'El SQL fallo al actualizar usuario:' );
					$ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable' );
					$TransResult2 = DB_query ( $SQL, $db, $ErrMsg, $DbgMsg, true );
				}
			}
		}
	}else{
		//Si seleeciono cambiar pero no selecciono datos
		echo "<p class='bg-danger'>Seleccionar por lo menos un folio y un usuario para cambiar la factura a otro corte</p>";
	}
}

/*
 * **DEFINO CLASE PARA GUARDAR LOS MOVIENTOS DE SALDOS
 */
class Movimiento {
	var $FromAccount; /* cuenta puenta */
	var $Amount; /* cantidad del movimiento */
	var $ToAccount; /* cuenta de bancos */
	var $Reference;
	var $u_DetalleCorte;
	var $ID = 0;
	var $Fechadeposito;
	var $vendedorid;
	var $vendedorname;
	function Movimiento($fromaccount, $amount, $ToAccount, $reference, $u_detallecorte, $id, $Fechadeposito, $vendedorid,$vendedorname) {
		/* funcion constructor */
		$this->FromAccount = $fromaccount;
		$this->Amount = $amount;
		$this->ToAccount = $ToAccount;
		$this->Reference = $reference;
		$this->u_DetalleCorte = $u_detallecorte;
		$this->ID = $id;
		$this->Fechadeposito = $Fechadeposito;
		$this->vendedorid = $vendedorid;
		$this->vendedorname = $vendedorname;
	}
}

if (isset ( $_GET ['fechacorte'] )) {
	$_POST ['fechacorte'] = $_GET ['fechacorte'];
}
if (isset ( $_GET ['fechacortede'] )) {
	$_POST ['fechacortede'] = $_GET ['fechacortede'];
}

if (isset ( $_GET ['fechacortea'] )) {
	$_POST ['fechacortea'] = $_GET ['fechacortea'];
}

if (isset ( $_GET ['unidadnegocio'] )) {
	$_POST ['unidadnegocio'] = $_GET ['unidadnegocio'];
}

if (isset ( $_GET ['u_cortecaja'] )) {
	$_POST ['u_cortecaja'] = $_GET ['u_cortecaja'];
}

if (isset ( $_GET ['Editar'] )) {
	$_POST ['Editar'] = $_GET ['Editar'];
}

$u_cortecaja = $_POST ['u_cortecaja'];
$fechacorte = $_POST ['fechacorte'];
if (empty($fechacorte)) {
	$fechacorte = date ( 'Y' ) . "-" . date ( 'm' ) . "-" . date ( 'd' );
}
if (isset ( $_POST ['fechacortede'] )) {
	$fechacortede = $_POST ['fechacortede'];
} else {
	$fechacortede = date ( 'd' ) . "-" . date ( 'm' ) . "-" . date ( 'Y' );
}
if (isset ( $_POST ['fechacortea'] )) {
	$fechacortea = $_POST ['fechacortea'];
} else {
	$fechacortea = date ( 'd' ) . "-" . date ( 'm' ) . "-" . date ( 'Y' );
}

//echo "tipoAbono: " . $_POST ['tipoAbono'] ;
// echo "<br/> fechacortede: " . $fechacortede;
// echo "<br/> fechacortea: " . $fechacortea;

$Fechadeposito = date ( 'Y' ) . "-" . date ( 'm' ) . "-" . date ( 'd' );	

if (  $_POST ['tipoAbono'] == "CajaPuente") {
	$Fechadeposito = $_POST['FechadepositoCajaFuente'];
}

if( $_POST ['tipoAbono'] == "Sobrante") {
	$Fechadeposito = $_POST['FechadepositoSobrantes'];
}

$unidadnegocio = $_POST ['unidadnegocio'];
$unidadejecutora = '';
if (isset($_POST['unidadejecutora'])) {
	$unidadejecutora = $_POST['unidadejecutora'];
} elseif (isset($_GET['unidadejecutora'])) {
	$unidadejecutora = $_GET['unidadejecutora'];
}

$arrfechacorte = explode ( "-", $fechacorte );

//Filtro Usuario o Vendedor
if (isset( $_GET['usuario'] )) {
	$_POST['usuario'] = $_GET['usuario'];
}else{
	$_POST['usuario'] = $_POST['usuario'];
	if (empty($_POST['usuario'])) {
		$_POST['usuario'] = $_SESSION['UserID'];
	}
}

//filtrar por vendedor todos o individual, consultas
$usuario = "";
if ($_POST['usuario'] == 'all') {
	//si es todos los usuarios
	$SQL = "SELECT userid, realname FROM www_users WHERE active = 1 ORDER BY realname asc";
	$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
	$TransResult = DB_query ( $SQL, $db, $ErrMsg );
	
	while ( $myrow = DB_fetch_array ( $TransResult ) ) {
		$usuario = $usuario."'" . $myrow ['userid'] . "',";
	}
	//Quitar ultima ,
	$usuario = substr($usuario, 0, strlen($usuario)-1);
}else{
	$usuario = "'" .$_POST['usuario']."'";
}

/*
 * hago split en la fechacorte para hacer mas facil la comparacion en las consultas con fechas
 */
$diacorte = $arrfechacorte [2];
$mescorte = $arrfechacorte [1];
$aniocorte = $arrfechacorte [0];

//total de los ingresos y mensaje
$totalIngresosCorte = 0;
$totalIngresosCorte2= 0;

if (isset ( $_POST ['addmovs'] )) {
	$fromaccount = $_POST ['fromaccount'];
	$amount = $_POST ['amount'];
	$toaccount = $_POST ['toaccount'];
	$reference = $_POST ['reference'];
	$u_detallecorte = $_POST ['u_detallecorte'];
	
	if (strlen ( $_POST ['ToMes'] ) == 1)
		$mes = '0' . $_POST ['ToMes'];
	else
		$mes = $_POST ['ToMes'];
	if (strlen ( $_POST ['ToDia'] ) == 1)
		$dia = '0' . $_POST ['ToDia'];
	else
		$dia = $_POST ['ToDia'];

	$user = "";
	if ($_POST['usuario'] == 'all') {
		$user = $_SESSION['UserID'];
	}else{
		$user = $_POST['usuario'];
	}

	$realname = "";
	$sql = "SELECT realname FROM www_users WHERE userid = '".$user."'";
	$ErrMsg = _ ( 'LA SENTENCIA SQL NO ARROJO RESULTADOS DEBIDO A..' );
	$xResult = DB_query ( $sql, $db, $ErrMsg );
	if ($xmyrow = DB_fetch_array ( $xResult )) {
		$realname = $xmyrow['realname'];
	}

	//$Fechadeposito = $_POST ['Fechadeposito']; //$_POST ['ToYear'] . '-' . $mes . '-' . $dia;
	$_SESSION ['MOVS'] [$_SESSION ['iMOVS']] = new Movimiento ( $fromaccount, $amount, $toaccount, $reference, $u_detallecorte, $_SESSION ['iMOVS'], $Fechadeposito, $user, $realname);
	$_SESSION ['iMOVS'] = $_SESSION ['iMOVS'] + 1;
}
if (isset ( $_GET ['DeleteMov'] ) AND $_GET ['DeleteMov'] =='ALL' ) {
	//$movid = $_GET ['DeleteMov'];
	unset ($_SESSION ['MOVS']);
}

if (isset ( $_GET ['DeleteMov'] )) {
	$movid = $_GET ['DeleteMov'];
	unset ( $_SESSION ['MOVS'] [$movid] );
}

echo '<form action=' . $_SERVER ['PHP_SELF'] . ' method=post name=form1>';
echo '<input type="hidden" id="tipoAbono" name="tipoAbono" >';
//echo '<p class="page_title_text">' . ' ' . _ ( 'MANTENIMIENTO DE CAJEROS' ) . '</p>';

if (isset ( $_POST ['Aceptarprepoliza'] )) {

	if (isset($_POST['txtIdsFacturas']) && !empty($_POST['txtIdsFacturas'])) {
		
		// $PeriodNo = GetPeriod(date("d/m/Y"),$db);
		$fecha = substr ( $fechacorte, 0, 10 );
		$arrfecha = explode ( "-", $fecha );
		$fecha = $arrfecha [2] . "/" . $arrfecha [1] . "/" . $arrfecha [0];
		// $PeriodNo = GetPeriod(substr($fechacorte,0,10),$db);
		$PeriodNo = GetPeriod ( $fecha, $db, $unidadnegocio );
		$BatchNo = GetNextTransNo ( 120, $db );
		
		$DSQL = "DELETE FROM usrdetallecortecaja
			WHERE u_cortecaja = '" . $u_cortecaja . "'";
		$DbgMsg = _ ( 'El SQL fallo al eliminar los registros:' );
		$ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable' );
		// $result = DB_query ( $DSQL, $db, $ErrMsg, $DbgMsg, true );

		$infoFoliosCompromiso = array();

		$SQL = "SELECT gltempcashpayment,gltempcheckpayment,gltempccpayment,gltemptransferpayment,gltempcheckpostpayment
		FROM companies";
		$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
		$TransResult = DB_query ( $SQL, $db, $ErrMsg );

		$cadena = "";
		while ( $myrow = DB_fetch_array ( $TransResult ) ) {
			$cadena = "'" . $myrow ['gltempcashpayment'] . "','" . $myrow ['gltempcheckpayment'] . "','" . $myrow ['gltempccpayment'] . "','" . $myrow ['gltemptransferpayment'] . "','" . $myrow ['gltempcheckpostpayment'] . "'";
		}

		$sql = "SELECT realname FROM www_users WHERE userid = '".$_SESSION['UserID']."'";
		$resultUser = DB_query ( $sql, $db, $ErrMsg );
		$rsUser = DB_fetch_array($resultUser);
		$descriptionCajero = "";
		if (!empty($rsUser['realname'])) {
			$descriptionCajero = ", Cajero: ".$rsUser['realname'];
		}

		// echo "<br>cadena: ".$cadena;

		$SQL = "SELECT
		salesorders.fromstkloc,
		salesorderdetails.stkcode,
		SUM((salesorderdetails.unitprice * salesorderdetails.quantity) - ((salesorderdetails.unitprice * salesorderdetails.quantity) * salesorderdetails.discountpercent)) as total,
		tb_cat_objeto_detalle.cuenta_banco,
		debtortrans.nu_ue,
		debtortrans.tagref,
		CONCAT(debtortrans.tagref, ' - ', tags.tagdescription) as tagdescription,
		CONCAT(debtortrans.nu_ue, ' - ', tb_cat_unidades_ejecutoras.desc_ue) as uedescription,
		debtortransRecibo.type as typeRecibo,
		debtortransRecibo.transno as transnoRecibo,
		cuentapuente.account,
		debtortransRecibo.cuenta_banco as cuentaRecibo
		FROM debtortrans
		JOIN salesorders ON salesorders.orderno = debtortrans.order_
		JOIN salesorderdetails ON salesorderdetails.orderno = debtortrans.order_
		JOIN tags ON tags.tagref = debtortrans.tagref
		JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ur = debtortrans.tagref AND tb_cat_unidades_ejecutoras.ue = debtortrans.nu_ue
		JOIN salestypes ON salestypes.typeabbrev = salesorders.ordertype
		JOIN tb_cat_objeto_detalle ON tb_cat_objeto_detalle.loccode = salesorders.fromstkloc AND tb_cat_objeto_detalle.stockid = salesorderdetails.stkcode AND tb_cat_objeto_detalle.ano = salestypes.anio
		JOIN custallocns ON custallocns.transid_allocto = debtortrans.id
		JOIN debtortrans debtortransRecibo ON debtortransRecibo.id = custallocns.transid_allocfrom
		LEFT JOIN (
		SELECT
		DISTINCT
		gltrans.account,
		gltrans.type,
		gltrans.typeno
		FROM gltrans
		WHERE
		gltrans.type = 12
		AND gltrans.trandate = '".$fechacorte."'
		AND gltrans.account IN (".$cadena.")
		) cuentapuente ON cuentapuente.type = debtortransRecibo.type AND cuentapuente.typeno = debtortransRecibo.transno
		WHERE concat(debtortrans.type,'_',debtortrans.transno) in (".$_POST['txtIdsFacturas'].")
		GROUP BY tb_cat_objeto_detalle.cuenta_banco, debtortrans.nu_ue";
		// echo "<pre>".$SQL;exit();
		$ErrMsg = _ ( 'Al obtener el detalle para movimientos contables' );
		$resultGen = DB_query ( $SQL, $db, $ErrMsg );
		while ($myrowGeneral = DB_fetch_array ( $resultGen )) {
			$referencia = "Corte de Caja del ".$fechacorte." de la UR ".$myrowGeneral['tagdescription'].", UE: ".$myrowGeneral['uedescription'].$descriptionCajero;
			// echo "<br>referencia: ".$referencia;
			$unidadejecutora = $myrowGeneral['nu_ue'];
			$folioPolizaUe = 0;

			$cuenta_banco = $myrowGeneral['cuenta_banco'];
			if (!empty($myrowGeneral['cuentaRecibo'])) {
				// Si es cuenta del recibo de pago
				$cuenta_banco = $myrowGeneral['cuentaRecibo'];
			}

			$ISQL = "INSERT INTO usrdetallecortecaja (referencia,
			cuentapuente,
			cuentacheques,
			monto,
			fechacorte,
			u_status,
			u_cortecaja,
			fechadeposito, 
			userid,
			nu_foliocorte
			)
			VALUES ('" . $referencia . "',
			'" . $myrowGeneral['account'] . "',
			'" . $cuenta_banco . "',
			" . $myrowGeneral['total'] . ",
			'" . substr ( $fechacorte, 0, 10 ) . "',
			0,
			" . $u_cortecaja . ",
			'" . $fechacorte . " " . $horaPrepoliza . "',
			'" . $_SESSION['UserID'] . "',
			'".$BatchNo."')";
			$DbgMsg = _ ( 'El SQL fallo al insertar la transaccion:' );
			$ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable' );
			$result = DB_query ( $ISQL, $db, $ErrMsg, $DbgMsg, true );

			// Cargo, cuenta de bancos
			$ISQL = "INSERT INTO gltrans (type,
			typeno,
			trandate,
			periodno,
			account,
			narrative,
			tag,
			amount,
			posted,
			rate,
			userid,
			ln_ue,
			nu_folio_ue)
			VALUES (120,
			" . $BatchNo . ",
			STR_TO_DATE('" . $fechacorte . "', '%Y-%m-%d'),
			" . $PeriodNo . ",
			'" . $cuenta_banco . "',
			'" . $referencia . "',
			'" . $unidadnegocio . "',
			" . $myrowGeneral['total'] . ",
			'0',
			'1',
			'".$_SESSION['UserID']."',
			'".$unidadejecutora."',
			'".$folioPolizaUe."')";
			$DbgMsg = _ ( 'El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:' );
			$ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable para la cuenta puente de caja' );
			$result = DB_query ( $ISQL, $db, $ErrMsg, $DbgMsg, true );

			// Abono, cuenta puente, metodo de pago
			$ISQL = "INSERT INTO gltrans (type,
			typeno,
			trandate,
			periodno,
			account,
			narrative,
			tag,
			amount,
			posted,
			rate,
			userid,
			ln_ue,
			nu_folio_ue)
			VALUES (120,
			" . $BatchNo . ",
			STR_TO_DATE('" . $fechacorte . "', '%Y-%m-%d'),
			" . $PeriodNo . ",
			'" . $myrowGeneral['account'] . "',
			'" . $referencia . "',
			'" . $unidadnegocio . "',
			" . - $myrowGeneral['total'] . ",
			'0',
			'1',
			'".$_SESSION['UserID']."',
			'".$unidadejecutora."',
			'".$folioPolizaUe."')";
			//	echo "<pre/>SQL 545".$ISQL;
			$DbgMsg = _ ( 'El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:' );
			$ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable para la cuenta puente de caja' );
			$result = DB_query ( $ISQL, $db, $ErrMsg, $DbgMsg, true );

			// $usql = "UPDATE debtortrans
			// SET nu_cortecaja = '1', nu_foliocorte = '".$BatchNo."'
			// WHERE type = '" . $myrowGeneral['typeRecibo'] . "'
			// and transno = '" . $myrowGeneral['transnoRecibo'] . "'";
			// $DbgMsg = _ ( 'El SQL fallo al actualizar la transaccion en banktrans:' );
			// $ErrMsg = _ ( 'No se pudo realizar la Transaccion Contable en bankrans' );
			// $result = DB_query ( $usql, $db, $ErrMsg, $DbgMsg, true );

			$referencia2 = trim ( $referencia ) . "@" . $unidadnegocio . "@" . $myrowGeneral['account'];
			$referencia2 = $referencia . "@" . $myrowGeneral['total'] . "@";

			$beneficiario = "Corte de Caja " . $diacorte . " de " . strtoupper ( NombreMes ( $mescorte ) ) . " del " . $aniocorte;

			$xsql = "SELECT count(*) as movs FROM banktrans WHERE type = 120
			and bankact = '" . $cuenta_banco . "'
			and transno='" . $BatchNo . "'
			and SUBSTR(ref,1,LOCATE('@',ref)-1) = '" . trim ( $referencia ) . "'";
			$ErrMsg = _ ( 'LA SENTENCIA SQL NO ARROJO RESULTADOS DEBIDO A..' );
			$xResult = DB_query ( $xsql, $db, $ErrMsg );

			if ($xmyrow = DB_fetch_array ( $xResult )) {
				if ($xmyrow ['movs'] == 0) {
					$ISQL = "INSERT INTO banktrans(banktransid,
					type,
					transno,
					bankact,
					ref,
					amountcleared,
					exrate,
					functionalexrate,
					transdate,
					banktranstype,
					amount,
					currcode,
					tagref,
					beneficiary,
					chequeno,
					nu_type,
					ln_ue,
					nu_anio_fiscal)
					VALUES(NULL,
					'120',
					'" . $BatchNo . "',
					'" . $cuenta_banco . "',
					'" . $referencia2 . "',
					0,
					1,
					1,
					'" . $fechacorte . "',
					'Corte Caja',
					" . $myrowGeneral['total'] . ",
					'MXN',
					'" . $unidadnegocio . "',
					'" . $beneficiario . "',
					'',
					'120',
					'".$unidadejecutora."',
					'".$_SESSION['ejercicioFiscal']."')";
					$DbgMsg = _ ( 'El SQL fallo al insertar la transaccion en banktrans:' );
					$ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable en bankrans' );
					// echo "<br>" . $ISQL;
					$result = DB_query ( $ISQL, $db, $ErrMsg, $DbgMsg, true );
				} else {
					$usql = "UPDATE banktrans
					SET amount = amount + " . $myrowGeneral['total'] . ",
					ref = CONCAT(ref,'@','" . $referencia2 . "')
					WHERE type = 120
					and bankact = '" . $cuenta_banco . "'
					and transno='" . $BatchNo . "'
					and SUBSTR(ref,1,LOCATE('@',ref)-1) = '" . trim ( $referencia ) . "'";
					$DbgMsg = _ ( 'El SQL fallo al actualizar la transaccion en banktrans:' );
					$ErrMsg = _ ( 'No se pudo realizar la Transaccion Contable en bankrans' );
					$result = DB_query ( $usql, $db, $ErrMsg, $DbgMsg, true );
				}
			}
		}

		$usql = "UPDATE debtortrans
		JOIN custallocns ON custallocns.transid_allocto = debtortrans.id
		JOIN debtortrans debtortransRecibo ON debtortransRecibo.id = custallocns.transid_allocfrom
		SET debtortransRecibo.nu_cortecaja = '1', debtortransRecibo.nu_foliocorte = '".$BatchNo."'
		WHERE concat(debtortrans.type,'_',debtortrans.transno) in (".$_POST['txtIdsFacturas'].")";
		$DbgMsg = _ ( 'El SQL fallo al actualizar la transaccion en banktrans:' );
		$ErrMsg = _ ( 'No se pudo realizar la Transaccion Contable en bankrans' );
		$result = DB_query ( $usql, $db, $ErrMsg, $DbgMsg, true );

		$MSQL = 'UPDATE usrcortecaja
			SET u_status = 1
			WHERE u_cortecaja = ' . $u_cortecaja;
		if (! $Result = DB_query ( $MSQL, $db )) {
			$ErrMsg = _ ( 'ERROR CRITICO' ) . '! ' . _ ( 'ANOTE ESTE ERROR Y BUSQUE AYUDA' ) . ': ' . _ ( 'El registro de transaccion de cliente no se pudo insertar' );
			$DbgMsg = _ ( 'El siguiente SQL fue utilizado' );
		}
		
		// ELIMINO VARIABLE DE SESSION DE MOVIMIENTOS.
		unset ( $_SESSION ['MOVS'] );

		prnMsg("El cierre de caja del " . $diacorte . " de " . NombreMes ( $mescorte ) . " del " . $aniocorte . " se ha realizado exitosamente.", "success");

		echo '<div align="center">';
		echo '<p class="page_title_text">' . '<a href="' . $rootpath . '/PrintCorteCajaV2_Detalles.php?fechacorte=' . substr ( $myrow ['fechacorte'], 0, 10 ) . '&unidadnegocio=' . $myrow ['tag']. '&unidadejecutora='.$myrow['ln_ue'].'&u_cortecaja=' . $myrow ['u_cortecaja'] . '&usuario=' . $row['userid'] . "&nu_foliocorte=" . $row['nu_foliocorte'] . '&ficha=1" target="_blank"><span class="glyphicon glyphicon-print" aria-hidden="true"></span>' . _ ( ' Imprimir Ficha ' ) . '</a></p>';
		echo '<br>';
		echo '<p class="page_title_text">' . '<a href="' . $rootpath . '/PrintCorteCajaV2_Detalles.php?fechacorte=' . substr ( $fechacorte, 0, 10 ) . '&unidadnegocio=' . $unidadnegocio. '&unidadejecutora='.$unidadejecutora.'&u_cortecaja=' . $u_cortecaja . '&usuario=' . $_SESSION['UserID'] . "&nu_foliocorte=" . $BatchNo . '&resumen=1" target="_blank"><span class="glyphicon glyphicon-print" aria-hidden="true"></span>' . _ ( ' Imprimir Resumen ' ) . '</a></p>';
		echo '<br>';
		echo "<p class='page_title_text'><a href='" . $rootpath . "/PrintCorteCajaV2_Detalles.php?fechacorte=" . substr ( $fechacorte, 0, 10 ) . "&unidadnegocio=" . $unidadnegocio . "&unidadejecutora=".$unidadejecutora."&u_cortecaja=" . $u_cortecaja . "&usuario=" . $_SESSION['UserID'] . "&nu_foliocorte=" . $BatchNo . "' target='_blank'><span class='glyphicon glyphicon-print' aria-hidden='true'></span>" . _ ( ' Imprimir Detalle' ) . "</a></p>";
	
		
		echo '</div>';
		include 'includes/footer_Index.inc';
		exit();
		
		echo "<table width='80%' cellpadding='0' cellspacing='0' border=0>";
		echo "<tr><td colspan='2' style='font-size:12pt;'>";
		//echo "El cierre de caja del " . $diacorte . " de " . NombreMes ( $mescorte ) . "del " . $aniocorte . " se ha realizado exitosamente.";
		prnMsg("El cierre de caja del " . $diacorte . " de " . NombreMes ( $mescorte ) . " del " . $aniocorte . " se ha realizado exitosamente.", "success");
		echo "</td></tr>";
		echo "<tr><td colspan='2' height='10'></td></tr>";
		echo "<tr><td width='50%' style='text-align:center'>";
		$permiso = Havepermission ( $_SESSION ['UserID'], 10, $db );
		if ($permiso == 0) {
			echo "&nbsp;";
		} else {
			echo "Ir a <a href='CustomerReceiptcls4.php?&NewReceipt=Yes'>Alta de Recibo de Pago</a>";
		}
		echo "</td>";
		echo "<td style='text-align:center'>";
		
		$permiso = Havepermission ( $_SESSION ['UserID'], 193, $db );
		if ($permiso == 0) {
			echo "&nbsp;";
		} else {
			echo "Ir a <a href='CustomerReceiptCorteCajaV2_Detalles.php'>Cierre de Caja</a>";
		}
		echo "</td></tr>";
		echo "</table>";
		exit;
	} else {
		echo "<table width='80%' cellpadding='0' cellspacing='0' border=0>";
		echo "<tr><td colspan='2' style='font-size:12pt;'>";
		echo "ERROR!!  No se genero el cierre de caja del " . $diacorte . " de " . NombreMes ( $mescorte ) . "del " . $aniocorte . ". NO EXISTEN MOVIMIENTOS A BANCOS";
		echo "</td></tr>";
		echo "<tr><td colspan='2' height='10'></td></tr>";
		echo "<tr><td width='50%' style='text-align:center'>";
		$permiso = Havepermission ( $_SESSION ['UserID'], 10, $db );
		if ($permiso == 0) {
			echo "&nbsp;";
		} else {
			echo "Ir a <a href='CustomerReceiptcls4.php?&NewReceipt=Yes'>Alta de Recibo de Pago</a>";
		}
		echo "</td>";
		echo "<td style='text-align:center'>";
		
		$permiso = Havepermission ( $_SESSION ['UserID'], 193, $db );
		if ($permiso == 0) {
			echo "&nbsp;";
		} else {
			echo "Ir a <a href='CustomerReceiptCorteCajaV2_Detalles.php'>Cierre de Caja</a>";
		}
		echo "</td></tr>";
		echo "</table>";
	}
	
	include 'includes/footer_Index.inc';
	exit ();
}

if (isset ( $_POST ['Aceptarprepoliza_ANT'] )) {

	if (isset($_POST['txtIdsFacturas']) && !empty($_POST['txtIdsFacturas'])) {

		echo "txtIdsFacturas: ".$_POST['txtIdsFacturas'];
		exit();
		
		// $PeriodNo = GetPeriod(date("d/m/Y"),$db);
		$fecha = substr ( $fechacorte, 0, 10 );
		$arrfecha = explode ( "-", $fecha );
		$fecha = $arrfecha [2] . "/" . $arrfecha [1] . "/" . $arrfecha [0];
		// $PeriodNo = GetPeriod(substr($fechacorte,0,10),$db);
		$PeriodNo = GetPeriod ( $fecha, $db, $unidadnegocio );
		$BatchNo = GetNextTransNo ( 120, $db );
		
		$DSQL = "DELETE FROM usrdetallecortecaja
			WHERE u_cortecaja = '" . $u_cortecaja . "'";
		$DbgMsg = _ ( 'El SQL fallo al eliminar los registros:' );
		$ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable' );
		$result = DB_query ( $DSQL, $db, $ErrMsg, $DbgMsg, true );

		$infoFoliosCompromiso = array();
	
		foreach ( $_SESSION ['MOVS'] as $Movs ) {
			$realizarOperacion = 1;
			//obtener si ya esta ese registo
			$ISQL = "SELECT * 
					FROM usrdetallecortecaja 
					WHERE referencia = '".$Movs->Reference."' 
					and cuentapuente = '".substr ( $Movs->FromAccount, 0, strpos ( $Movs->FromAccount, "-" ) )."' 
					and cuentacheques = '".substr ( $Movs->ToAccount, 0, strpos ( $Movs->ToAccount, "-" ) )."' 
					and u_cortecaja = '".$u_cortecaja."'
					and ROUND(monto,2) = '".number_format($Movs->Amount,2,'.','')."'
					and usrdetallecortecaja.userid = '".$Movs->vendedorid."'
					and usrdetallecortecaja.fechadeposito = '".$Movs->Fechadeposito."'";
			$DbgMsg = _ ( 'El SQL fallo al obtener datos de prepoliza:' );
			$ErrMsg = _ ( 'No se pudo obteenr la informacion' );
			$result = DB_query ( $ISQL, $db, $ErrMsg, $DbgMsg, true );
			if (DB_num_rows ( $result ) > 0) {
				$realizarOperacion = 0;
			}

			if ($realizarOperacion == 1) {

				$folioPolizaUe = 0;
                foreach ($infoFoliosCompromiso as $datosFolios) {
                    // Recorrer para ver si exi
                    if ($datosFolios['tagref'] == $unidadnegocio && $datosFolios['ue'] == $unidadejecutora) {
                        // Si existe
                        $type = $datosFolios['type'];
                        $transno = $datosFolios['transno'];
                        $folioPolizaUe = $datosFolios['folioPolizaUe'];
                    }
                }
                if ($folioPolizaUe == 0 && 1 == 2) {
                    // Si no existe folio sacar folio
                    // $transno = GetNextTransNo($type, $db);
                    // Folio de la poliza por unidad ejecutora
                    $folioPolizaUe = fnObtenerFolioUeGeneral($db, $unidadnegocio, $unidadejecutora, 120);
                    $infoFoliosCompromiso[] = array(
                        'tagref' => $tagref,
                        'ue' => $datoUE,
                        'type' => $datosClave ['type'],
                        'transno' => $datosClave ['transno'],
                        'folioPolizaUe' => $folioPolizaUe
                    );
                }

				$ISQL = "INSERT INTO usrdetallecortecaja (referencia,
						cuentapuente,
						cuentacheques,
						monto,
						fechacorte,
						u_status,
						u_cortecaja,
						fechadeposito, 
						userid)
					VALUES ('" . $Movs->Reference . "',
						'" . substr ( $Movs->FromAccount, 0, strpos ( $Movs->FromAccount, "-" ) ) . "',
						'" . substr ( $Movs->ToAccount, 0, strpos ( $Movs->ToAccount, "-" ) ) . "',
						" . $Movs->Amount . ",
						'" . substr ( $fechacorte, 0, 10 ) . "',
						0,
						" . $u_cortecaja . ",
						'" . $Movs->Fechadeposito . " " . $horaPrepoliza . "',
						'" . $_SESSION['UserID'] . "')";
				$DbgMsg = _ ( 'El SQL fallo al insertar la transaccion:' );
				$ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable' );
				$result = DB_query ( $ISQL, $db, $ErrMsg, $DbgMsg, true );
		        //	echo "<pre/>SQL 528".$ISQL;
				$ISQL = "INSERT INTO gltrans (type,
				typeno,
				trandate,
				periodno,
				account,
				narrative,
				tag,
				amount,
				posted,
				rate,
				userid,
				ln_ue,
				nu_folio_ue)
				VALUES (120,
				" . $BatchNo . ",
				STR_TO_DATE('" . $fechacorte . "', '%Y-%m-%d'),
				" . $PeriodNo . ",
				'" . substr ( $Movs->FromAccount, 0, strpos ( $Movs->FromAccount, "-" ) ) . "',
				'" . $Movs->Reference . "',
				'" . $unidadnegocio . "',
				" . - $Movs->Amount . ",
				'0',
				'1',
				'".$_SESSION['UserID']."',
				'".$unidadejecutora."',
				'".$folioPolizaUe."')";
		        //	echo "<pre/>SQL 545".$ISQL;
				$DbgMsg = _ ( 'El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:' );
				$ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable para la cuenta puente de caja' );
				$result = DB_query ( $ISQL, $db, $ErrMsg, $DbgMsg, true );

				$myrowmonto= db_fetch_row($result);
				
				$montoCorrecto = 0;
				//echo "<br>". substr ( $Movs->FromAccount, 0, strpos ( $Movs->FromAccount, "-" ) )." - ".$CuentaEfectivo. " - ". $totalNotaDevolucionAnticipos;
				$cuenta = trim(substr ( $Movs->FromAccount, 0, strpos ( $Movs->FromAccount, "-" )));
				//echo "<br> ". $cuenta ." - ".trim($CuentaEfectivo);
				if( $cuenta== trim($CuentaEfectivo)){
					$montoCorrecto =  $myrowmonto[8]- $totalNotaDevolucionAnticipos;
					//echo "<br> 592 ". $montoCorrecto." - ".$Movs->Amount;
				}else{
					$montoCorrecto = $Movs->Amount -($Movs->Amount -  $myrowmonto[8]);	
					if($Movs->Amount< $myrowmonto[8]){
						$montoCorrecto = $Movs->Amount;
					}
				}

				$ISQL = "INSERT INTO gltrans (type,
				typeno,
				trandate,
				periodno,
				account,
				narrative,
				tag,
				amount,
				posted,
				rate,
				userid,
				ln_ue,
				nu_folio_ue)
				VALUES (120,
				" . $BatchNo . ",
				STR_TO_DATE('" . $fechacorte . "', '%Y-%m-%d'),
				" . $PeriodNo . ",
				'" . substr ( $Movs->ToAccount, 0, strpos ( $Movs->ToAccount, "-" ) ) . "',
				'" . $Movs->Reference . "',
				'" . $unidadnegocio . "',
				" . $Movs->Amount . ",
				'0',
				'1',
				'".$_SESSION['UserID']."',
				'".$unidadejecutora."',
				'".$folioPolizaUe."')";
				
				$DbgMsg = _ ( 'El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:' );
				$ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable para la cuenta puente de caja' );
				$result = DB_query ( $ISQL, $db, $ErrMsg, $DbgMsg, true );
		        //echo "<pre/>SQL 626".$ISQL;
				// INSERTA REGISTRO EN EL BANKTRANS, CONCILIACIONES BANCARIAS
				
				$referencia = trim ( $Movs->Reference ) . "@" . $unidadnegocio . "@" . substr ( $Movs->FromAccount, 0, strpos ( $Movs->FromAccount, "-" ) );
				$referencia = $referencia . "@" . $Movs->Amount . "@";
				// $referencia = $referencia . $Movs->Reference;
				
				$beneficiario = "CORTE DE CAJA " . $diacorte . " DE " . strtoupper ( NombreMes ( $mescorte ) ) . " DEL " . $aniocorte;
				
				$xsql = "SELECT count(*) as movs FROM banktrans WHERE type = 120
    				and bankact = '" . substr ( $Movs->ToAccount, 0, strpos ( $Movs->ToAccount, "-" ) ) . "'
    				and transno='" . $BatchNo . "'
    				and SUBSTR(ref,1,LOCATE('@',ref)-1) = '" . trim ( $Movs->Reference ) . "'";
				$ErrMsg = _ ( 'LA SENTENCIA SQL NO ARROJO RESULTADOS DEBIDO A..' );
				$xResult = DB_query ( $xsql, $db, $ErrMsg );
				
				if ($xmyrow = DB_fetch_array ( $xResult )) {
					if ($xmyrow ['movs'] == 0) {
						$ISQL = "INSERT INTO banktrans(banktransid,
							type,
							transno,
							bankact,
							ref,
							amountcleared,
							exrate,
							functionalexrate,
							transdate,
							banktranstype,
							amount,
							currcode,
							tagref,
							beneficiary,
							chequeno)
							VALUES(NULL,
							'120',
							'" . $BatchNo . "',
							'" . substr ( $Movs->ToAccount, 0, strpos ( $Movs->ToAccount, "-" ) ) . "',
							'" . $referencia . "',
							0,
							1,
							1,
							'" . $Movs->Fechadeposito . "',
							'CORTE CAJA',
							" . $Movs->Amount . ",
							'MXN',
							'" . $unidadnegocio . "',
							'" . $beneficiario . "',
							'')";
			             //	echo "<pre/>SQL 674".$ISQL;			
						$DbgMsg = _ ( 'El SQL fallo al insertar la transaccion en banktrans:' );
						$ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable en bankrans' );
						// echo "<br>" . $ISQL;
						$result = DB_query ( $ISQL, $db, $ErrMsg, $DbgMsg, true );
					} else {
						$usql = "UPDATE banktrans
						SET amount = amount + " . $Movs->Amount . ",
						ref = CONCAT(ref,'@','" . $referencia . "')
						WHERE type = 120
						and bankact = '" . substr ( $Movs->ToAccount, 0, strpos ( $Movs->ToAccount, "-" ) ) . "'
						and transno='" . $BatchNo . "'
						and SUBSTR(ref,1,LOCATE('@',ref)-1) = '" . trim ( $Movs->Reference ) . "'";
						$DbgMsg = _ ( 'El SQL fallo al actualizar la transaccion en banktrans:' );
						$ErrMsg = _ ( 'No se pudo realizar la Transaccion Contable en bankrans' );
						$result = DB_query ( $usql, $db, $ErrMsg, $DbgMsg, true );
					}
				}
			}
		}

		if($_POST['montosobrante']>0){ 
            // se registra el sobrante 
            $realizarOperacionS=1;
            $ISQL = "SELECT * 
                    FROM usrdetallecortecaja 
                    WHERE referencia = '".$_POST['referenciasobrante']."' 
                    AND cuentapuente = '".substr ( $_POST['toSobrante'], 0, strpos ( $_POST['toSobrante'], "-" ) )."' 
                    AND cuentacheques = '".substr ( $_POST['montosobrante'], 0, strpos ( $_POST['montosobrante'], "-" ) )."' 
                    AND u_cortecaja = '".$u_cortecaja."'
                    AND ROUND(monto,2) = '".number_format($montosobrante,2,'.','')."'
                    AND usrdetallecortecaja.userid = '".$vendedorid."'
                    AND usrdetallecortecaja.fechadeposito = '".$fechacorte."'";
            $DbgMsg = _ ( 'El SQL fallo al obtener datos de prepoliza:' );
            $ErrMsg = _ ( 'No se pudo obteenr la informacion' );
            $result = DB_query ( $ISQL, $db, $ErrMsg, $DbgMsg, true );
            if (DB_num_rows ( $result ) > 0) {
                $realizarOperacionS = 0;
            }

            if ($realizarOperacionS == 1) {

                $ISQL = "INSERT INTO usrdetallecortecaja_prepoliza (referencia,
                            cuentapuente,
                            cuentacheques,
                            monto,
                            fechacorte,
                            u_status,
                            u_cortecaja,fechadeposito, userid)
                        VALUES ('" . $_POST['referenciasobrante'] . "',
                            '".$_POST['toSobrante']."',
                            '".$_POST['fromSombrante']."',
                            -" . $_POST['montosobrante'] . ",
                            '" . substr ( $fechacorte, 0, 10 ) . "',
                            0,
                            '" .$u_cortecaja . "','" . substr ( $fechacorte, 0, 10 )  . " " . $horaPrepoliza . "',
                            '".$vendedorid."')";
                $DbgMsg = _ ( 'El SQL fallo al insertar la transaccion:' );
                $ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable' );
                $result = DB_query ( $ISQL, $db, $ErrMsg, $DbgMsg, true );
                //  echo "<pre/>SQL PRE 3589 ".$ISQL;

                $ISQL = "INSERT INTO usrdetallecortecaja(referencia,
                            cuentapuente,
                            cuentacheques,
                            monto,
                            fechacorte,
                            u_status,
                            u_cortecaja,fechadeposito, userid)
                        VALUES ('" . $_POST['referenciasobrante'] . "',
                            '".$_POST['toSobrante']."',
                            '".$_POST['fromSobrante']."',
                            -" . $_POST['montosobrante'] . ",
                            '" . substr ( $fechacorte, 0, 10 ) . "',
                            0,
                            '" . $u_cortecaja. "','" . substr ( $fechacorte, 0, 10 )  . " " . $horaPrepoliza . "',
                            '".$vendedorid."')";
                $DbgMsg = _ ( 'El SQL fallo al insertar la transaccion:' );
                $ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable' );
                $result = DB_query ( $ISQL, $db, $ErrMsg, $DbgMsg, true );
                //  echo "<pre/>SQL PRE 3609".$ISQL;

                $ISQL = "INSERT INTO gltrans (type,
                        typeno,
                        trandate,
                        periodno,
                        account,
                        narrative,
                        tag,
                        amount)
                    VALUES (120,
                        " . $BatchNo . ",
                        STR_TO_DATE('" . $fechacorte . "', '%Y-%m-%d'),
                        " . $PeriodNo . ",
                        '" . $_POST['fromSobrante'] . "',
                        '" . $_POST['referenciasobrante'] . "',
                        '" . $unidadnegocio . "',
                        " . - $_POST['montosobrante']. ')';
                //  echo "<pre/>SQL 545".$ISQL;
                $DbgMsg = _ ( 'El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:' );
                $ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable para la cuenta puente de caja' );
                $result = DB_query ( $ISQL, $db, $ErrMsg, $DbgMsg, true );

                
                $ISQL = "INSERT INTO gltrans (type,
                        typeno,
                        trandate,
                        periodno,
                        account,
                        narrative,
                        tag,
                        amount)
                    VALUES (120,
                        " . $BatchNo . ",
                        STR_TO_DATE('" . $fechacorte . "', '%Y-%m-%d'),
                        " . $PeriodNo . ",
                        '" . $_POST['toSobrante'] . "',
                        '" . $_POST['referenciasobrante']. "',
                        '" . $unidadnegocio . "',
                        " . $_POST['montosobrante'] . ')';
                
                $DbgMsg = _ ( 'El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:' );
                $ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable para la cuenta puente de caja' );
                $result = DB_query ( $ISQL, $db, $ErrMsg, $DbgMsg, true );

            }
        }
		$MSQL = 'UPDATE usrcortecaja
			SET u_status = 2
			WHERE u_cortecaja = ' . $u_cortecaja;
		if (! $Result = DB_query ( $MSQL, $db )) {
			$ErrMsg = _ ( 'ERROR CRITICO' ) . '! ' . _ ( 'ANOTE ESTE ERROR Y BUSQUE AYUDA' ) . ': ' . _ ( 'El registro de transaccion de cliente no se pudo insertar' );
			$DbgMsg = _ ( 'El siguiente SQL fue utilizado' );
		}
		
		// ELIMINO VARIABLE DE SESSION DE MOVIMIENTOS.
		unset ( $_SESSION ['MOVS'] );
		
		echo "<table width='80%' cellpadding='0' cellspacing='0' border=0>";
		echo "<tr><td colspan='2' style='font-size:12pt;'>";
		//echo "El cierre de caja del " . $diacorte . " de " . NombreMes ( $mescorte ) . "del " . $aniocorte . " se ha realizado exitosamente.";
		prnMsg("El cierre de caja del " . $diacorte . " de " . NombreMes ( $mescorte ) . " del " . $aniocorte . " se ha realizado exitosamente.", "success");
		echo "</td></tr>";
		echo "<tr><td colspan='2' height='10'></td></tr>";
		echo "<tr><td width='50%' style='text-align:center'>";
		$permiso = Havepermission ( $_SESSION ['UserID'], 10, $db );
		if ($permiso == 0) {
			echo "&nbsp;";
		} else {
			echo "Ir a <a href='CustomerReceiptcls4.php?&NewReceipt=Yes'>Alta de Recibo de Pago</a>";
		}
		echo "</td>";
		echo "<td style='text-align:center'>";
		
		$permiso = Havepermission ( $_SESSION ['UserID'], 193, $db );
		if ($permiso == 0) {
			echo "&nbsp;";
		} else {
			echo "Ir a <a href='CustomerReceiptCorteCajaV2_Detalles.php'>Cierre de Caja</a>";
		}
		echo "</td></tr>";
		echo "</table>";
		exit;
	} else {
		echo "<table width='80%' cellpadding='0' cellspacing='0' border=0>";
		echo "<tr><td colspan='2' style='font-size:12pt;'>";
		echo "ERROR!!  No se genero el cierre de caja del " . $diacorte . " de " . NombreMes ( $mescorte ) . "del " . $aniocorte . ". NO EXISTEN MOVIMIENTOS A BANCOS";
		echo "</td></tr>";
		echo "<tr><td colspan='2' height='10'></td></tr>";
		echo "<tr><td width='50%' style='text-align:center'>";
		$permiso = Havepermission ( $_SESSION ['UserID'], 10, $db );
		if ($permiso == 0) {
			echo "&nbsp;";
		} else {
			echo "Ir a <a href='CustomerReceiptcls4.php?&NewReceipt=Yes'>Alta de Recibo de Pago</a>";
		}
		echo "</td>";
		echo "<td style='text-align:center'>";
		
		$permiso = Havepermission ( $_SESSION ['UserID'], 193, $db );
		if ($permiso == 0) {
			echo "&nbsp;";
		} else {
			echo "Ir a <a href='CustomerReceiptCorteCajaV2_Detalles.php'>Cierre de Caja</a>";
		}
		echo "</td></tr>";
		echo "</table>";
	}
	
	include 'includes/footer_Index.inc';
	exit ();
}

?>
<!--Estilos input date-->
<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" >
<link rel="stylesheet" href="css/FixedAssetLeasing.css">
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css"> 
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.css"> -->
<script type="text/javascript">
	$( document ).ready(function() {
		$('#sobrante').hide();
		$('#sobrante2').hide();
		$('#enCajas').show();
		$('#enCajas2').show();

		$("#FechadepositoCajaFuente").datepicker({
				 	dateFormat: "yy-mm-dd",
			        defaultDate:  "Now",
			        dayNames: [ "Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado" ],
			        dayNamesShort: [ "Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sab" ],
			  		dayNamesMin: [ "Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa" ],
			        monthNames: [ "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre" ],
			        monthNamesShort: [ "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic" ]
			    });
		$("#FechadepositoSobrantes").datepicker({
				 	dateFormat: "yy-mm-dd",
			        defaultDate:  "Now",
			        dayNames: [ "Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado" ],
			        dayNamesShort: [ "Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sab" ],
			  		dayNamesMin: [ "Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa" ],
			        monthNames: [ "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre" ],
			        monthNamesShort: [ "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic" ]
			    });



		$("#fechacortede, #fechacortea").datepicker({
		 	dateFormat: "mm/dd/yy",
	        defaultDate:  "Now",
	        dayNames: [ "Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado" ],
	        dayNamesShort: [ "Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sab" ],
	  		dayNamesMin: [ "Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa" ],
	        monthNames: [ "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre" ],
	        monthNamesShort: [ "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic" ]
	    });

	    

	});

</script>
<!-- <link rel="stylesheet" href="css/listabusqueda.css" /> -->
<div id="divMensajeOperacion" name="divMensajeOperacion"></div>
<!-- target="_blank" -->

<!--Panel Busqueda-->
<div class="panel panel-default" align="left">
	<div class="panel-heading" role="tab" id="headingOne">
		<h4 class="panel-title row">
			<div class="col-md-3 col-xs-3">
				<a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">
					<b>Criterios de filtrado</b>
				</a>
			</div>
		</h4>
	</div>
	<div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
		<div class="panel-body">
			<div class="row clearfix">
				<div class="col-md-4">  
					<div class="form-inline row">
						<div class="col-md-3">
							<span><label>UR: </label></span>
						</div>
						<div class="col-md-9">
							<select class="form-control  selectGeneral" name='unidadnegocio' align="center">
								<option selected value='0'>Seleccionar...</option>
								<?php
									$SQL = "SELECT  t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription
						            FROM sec_unegsxuser u, tags t
						            WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "'
						            ORDER BY t.tagref";
									$ErrMsg = _ ( 'No se obtuvo la informacion de la unidad de negocio' );
									$TransResult = DB_query ( $SQL, $db, $ErrMsg );
									while ( $myrow = DB_fetch_array ( $TransResult ) ) {
										if ($myrow ['tagref'] == $unidadnegocio) {
											echo "<option selected value='" . $myrow ['tagref'] . "'>" . $myrow ['tagdescription'] . "</option>";
										} else {
											echo "<option value='" . $myrow ['tagref'] . "'>" . $myrow ['tagdescription'] . "</option>";
										}
									}
								?>
							</select>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<component-date-label label="Desde: " type='date' name="fechacortede"  value="<?php echo $fechacortede ;?>" size='10' ></component-date-label>
				</div>
				<div class="col-md-4">
					<component-date-label label="Hasta: "  type='date' name="fechacortea" value="<?php echo $fechacortea ;?>" size='10' ></component-date-label>
				</div>

				<div class="row"></div>

				<br>
				
				<div class="col-md-4"> 
					<div class="form-inline row">
						<div class="col-md-3">
							<span><label>Usuario: </label></span>
						</div>
						<div class="col-md-9">
							<select class="form-control  selectGeneral" name='usuario' align="center">
								<option selected value='all'>Seleccionar...</option>
								<?php
									$SQL = "";
									if (empty($unidadnegocio)) {
										$SQL = "SELECT userid, realname FROM www_users WHERE active = 1 ORDER BY realname asc";
									}else{
										$SQL = "SELECT www_users.userid, www_users.realname 
										FROM www_users 
										LEFT JOIN sec_unegsxuser ON sec_unegsxuser.userid = www_users.userid
										WHERE www_users.active = 1 and sec_unegsxuser.tagref = '".$unidadnegocio."' 
										GROUP BY www_users.userid
										ORDER BY www_users.realname asc";
									}
									$ErrMsg = _ ( 'No se obtuvo informacion de los usuarios' );
									$TransResult = DB_query ( $SQL, $db, $ErrMsg );

									while ( $myrow = DB_fetch_array ( $TransResult ) ) {
										if ($myrow ['userid'] == $_POST['usuario']) {
											echo "<option selected value='" . $myrow ['userid'] . "'>" . $myrow ['realname'] . "</option>";
										} else {
											echo "<option value='" . $myrow ['userid'] . "'>" . $myrow ['realname'] . "</option>";
										}
									}
								?>
							</select>
						</div>
					</div>
				</div>
				<div class="col-md-4">
				</div>
				<div class="col-md-4">
				</div>
			</div>
			<div class="row"></div>
			<div align="center">
				<br>
				<component-button type="submit" id="consultar" name='consultar'  class="glyphicon glyphicon-search" value="Consultar"></component-button>
				<input type='hidden' name='fechacorte' value='<?php echo $fechacorte; ?>'>
				<input type='hidden' name='u_cortecaja' value='<?php echo $u_cortecaja; ?>'>
			</div>
		</div>
	</div>
</div>

<!--script input date-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
<?php

if (isset ( $_POST ['consultar'] ) or isset ( $_POST ['btnAbrir'] )) {

	$fechacortedeConsulta = date_create($fechacortede);
    $fechacortedeConsulta = date_format($fechacortedeConsulta, 'Y-m-d');

    $fechacorteaConsulta = date_create($fechacortea);
    $fechacorteaConsulta = date_format($fechacorteaConsulta, 'Y-m-d');

    $SQLUsuario = "";
	if ($_POST['usuario'] != 'all') {
		$SQLUsuario = " and usrdetallecortecaja.userid = '".$_POST['usuario']."' ";
	}

	$SQL = "SELECT usrcortecaja.u_cortecaja, usrcortecaja.fechacorte, usrcortecaja.u_status, usrcortecaja.tag, CONCAT(usrcortecaja.tag, ' - ', tags.tagdescription) as tagdescription, sum(monto) as monto, tb_cortecaja_estatus.sn_nombre, DATE_FORMAT(usrcortecaja.fechacorte, '%d-%m-%Y') as fechaVisual, usrcortecaja.ln_ue, CONCAT(usrcortecaja.ln_ue, ' - ', tb_cat_unidades_ejecutoras.desc_ue) as desc_ue
	FROM usrcortecaja 
	LEFT JOIN tb_cortecaja_estatus ON tb_cortecaja_estatus.nu_tipo = usrcortecaja.u_status
	LEFT JOIN usrdetallecortecaja ON usrcortecaja.u_cortecaja = usrdetallecortecaja.u_cortecaja ".$SQLUsuario.", 
	tags 
	JOIN sec_unegsxuser ON tags.tagref = sec_unegsxuser.tagref
	JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = tags.tagref
	JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ur = tags.tagref
	WHERE usrcortecaja.fechacorte between '".$fechacortedeConsulta." 00:00:00' AND '".$fechacorteaConsulta." 23:59:59'
	and (tag = '" . $unidadnegocio . "' or ('" . $unidadnegocio . "'=0)) 
	and sec_unegsxuser.userid = '" . $_SESSION ['UserID'] . "'
	and tb_sec_users_ue.userid = '" . $_SESSION ['UserID'] . "'
	and usrcortecaja.tag = tags.tagref
	and tb_sec_users_ue.tagref = usrcortecaja.tag
	and tb_sec_users_ue.ue = usrcortecaja.ln_ue
	and tb_cat_unidades_ejecutoras.ur = usrcortecaja.tag
	and tb_cat_unidades_ejecutoras.ue = usrcortecaja.ln_ue
	GROUP BY usrcortecaja.u_cortecaja, usrcortecaja.fechacorte, usrcortecaja.u_status, tag, tagdescription
	ORDER BY usrcortecaja.fechacorte desc, usrcortecaja.u_cortecaja, usrcortecaja.tag, tags.tagdescription, usrcortecaja.ln_ue, tb_cat_unidades_ejecutoras.desc_ue";
	if ($_SESSION['UserID'] == "desarrollo3") {
		// echo "<br><pre>".$SQL."</pre>";
	}
	$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
	$TransResult = DB_query ( $SQL, $db, $ErrMsg );
	//echo "<form name='FDatosB' method='post' action=''>";
	?>
	<div class="panel panel-default" align="left">
		<!-- Default panel contents -->
		<div class="panel-heading" align="center"> Total de Ingresos</div>
		<!--<div class="panel-body">
		</div>-->
		<!-- Table -->
		<table class="table table-hover">
			<tr>
				<th style='text-align:center;'><b>Corte</b></th>
				<th style='text-align:center;'><b>Fecha</b></th>
				<th style='text-align:center;'><b>Estatus</b></th>
				<th style='text-align:center;'><b>UR</b></th>
				<th style='text-align:center;'><b>UE</b></th>
				<th style='text-align:center;'><b>Monto</b></th>
				<th style='text-align:center;'><b>Operación</b></th>
				<th style='text-align:center;'><b>Abrir Corte</b></th>
				<th style='text-align:center;'><b>Imprimir</b></th>
			</tr>
		
			<?php
			$montototal = 0;
			while ( $myrow = DB_fetch_array ( $TransResult ) ) {
				$i = $i + 1;

				$SQLUsuario = "";
				if ($_POST['usuario'] != 'all') {
					$SQLUsuario = " AND debtortrans.userid = '".$_POST['usuario']."' ";
				}

				// Validar, si no tiene movimientos no visualizar en el corte
				$SQL = "SELECT SUM(debtortrans.ovamount + debtortrans.ovgst) as totalRecibos, debtortrans.tagref, debtortrans.nu_ue
				FROM debtortrans
				WHERE debtortrans.type IN (110,10,119)
				AND debtortrans.trandate between '".$fechacortedeConsulta." 00:00:00' AND '".$fechacorteaConsulta." 23:59:59'
				AND debtortrans.tagref = '".$myrow ['tag']."'
				AND debtortrans.nu_ue = '".$myrow ['ln_ue']."'
				".$SQLUsuario."
				GROUP BY debtortrans.tagref, debtortrans.nu_ue";
				$resultNum = DB_query ( $SQL, $db, $ErrMsg );
				if (DB_num_rows($resultNum) == 0) {
					continue;
				}
				
				echo "<tr>";
				echo "<td style='text-align:center'>" . $myrow ['u_cortecaja'] . "</td>";
				echo "<td style='text-align:center'>" . $myrow ['fechaVisual'] . "</td>";
				echo "<td style='text-align:center'>" . $myrow ['sn_nombre'] . "</td>";
				echo "<td style='text-align:center'>" . $myrow ['tagdescription'] . "</td>";
				echo "<td style='text-align:center'>" . $myrow ['desc_ue'] . "</td>";
				echo "<td style='text-align:right'>" . number_format ( $myrow ['monto'], 2 ) . "</td>";
				
				$montototal = $montototal + $myrow ['monto'];
				$permiso = Havepermission ( $_SESSION ['UserID'], 199, $db );
				if (($permiso == 0) && ($myrow ['u_status'] != 0) && (1 == 2)) {
					// No entrar
					echo "<td style='text-align:center'>" . _ ( '  ' ) . "</td>";
					echo "<td style='text-align:center'>" . _ ( '  ' ) . "</td>";
					echo "<td style='text-align:center'>" . _ ( ' Editar ' ) . "</td>";
				} else {
					if ($myrow ['u_status'] != 2) {
						echo "<td style='text-align:center;'><a href='" . $_SERVER ['PHP_SELF'] . "?" . SID . "&Editar=yes&fechacorte=" . substr ( $myrow ['fechacorte'], 0, 10 ) . "&fechacortede=" . $fechacortede . "&fechacortea=" . $fechacortea . "&unidadnegocio=" . $myrow ['tag'] . "&unidadejecutora=".$myrow['ln_ue']."&u_cortecaja=" . $myrow ['u_cortecaja']. "&usuario=". $_POST['usuario'] . "'>" . _ ( ' Procesar Cierre ' ) . "</a></td>";

						if (($myrow ['u_status'] == 1) and (Havepermission ( $_SESSION ['UserID'], 577, $db ) > 0)) {
							echo "<td style='text-align:center;'>";
							echo "<input type='checkbox' name='chkabrircorte_" . $myrow ['u_cortecaja'] . "' value='1'> &nbsp;&nbsp;";
							echo "<input class='btn btn-primary' type='submit' name='btnAbrir' value='Abrir_" . $myrow ['u_cortecaja'] . "'>";
							echo "</td>";
						} else {
							echo "<td style='text-align:center;'>&nbsp;</td>";
						}
					} else {						
						echo "<td style='text-align:center;'></td>";
						echo "<td style='text-align:center;'></td>";
					}
					// &unidadejecutora='.$myrow['ln_ue']
					// Imprimir corte
					echo "<td style='text-align:center;'>";
					
					echo '<p class="page_title_text">' . '<a href="' . $rootpath . '/PrintCorteCajaV2_Detalles.php?fechacorte=' . substr ( $myrow ['fechacorte'], 0, 10 ) . '&unidadnegocio=' . $myrow ['tag']. '&unidadejecutora='.$myrow['ln_ue'].'&u_cortecaja=' . $myrow ['u_cortecaja'] . '&usuario=' . $_POST['usuario'] . '&resumen=1" target="_blank"><span class="glyphicon glyphicon-print" aria-hidden="true"></span>' . _ ( ' Resumen ' ) . '</a></p>';
					echo "<p class='page_title_text'><a href='" . $rootpath . "/PrintCorteCajaV2_Detalles.php?fechacorte=" . substr ( $myrow ['fechacorte'], 0, 10 ) . "&unidadnegocio=" . $myrow ['tag'] . "&unidadejecutora=".$myrow['ln_ue']."&u_cortecaja=" . $myrow ['u_cortecaja'] . "&usuario=" . $_POST['usuario'] . "' target='_blank'><span class='glyphicon glyphicon-print' aria-hidden='true'></span>" . _ ( ' Detalle' ) . "</a></p>";
					echo "</td>";
				}
				echo "</tr>";

				$SQLUsuario = "";
				if ($_POST['usuario'] != 'all') {
					$SQLUsuario = " AND usrdetallecortecaja.userid = '".$_POST['usuario']."' ";
				}

				//Impresion de Prepolizas
				$SQL = "SELECT 
				DISTINCT
				usrdetallecortecaja.referencia, 
				usrdetallecortecaja.fechadeposito, 
				usrdetallecortecaja.userid,
				www_users.realname,
				usrdetallecortecaja.nu_foliocorte
				FROM usrdetallecortecaja
				JOIN www_users ON www_users.userid = usrdetallecortecaja.userid
				WHERE usrdetallecortecaja.u_cortecaja = '".$myrow ['u_cortecaja']."'
				".$SQLUsuario."";
				$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
				$Result = DB_query ( $SQL, $db, $ErrMsg );
				if (DB_num_rows ( $Result ) > 0) {
					$fechaInicioImpresion = $myrow ['fechacorte'];
					while ( $row = DB_fetch_array ( $Result ) ) {
						echo "<tr>";
						echo "<td style='text-align:center' colspan='6'></td>";
						echo "<td style='text-align:center'>".$row ['realname']."</td>";
						echo "<td style='text-align:center'>" . $row ['referencia'] . "</td>";
						echo "<td style='text-align:center;'>";
						
						// echo "<a href='" . $rootpath . "/PrintCorteCajaV2_Detalles.php?fechacorte=" . substr ( $myrow ['fechacorte'], 0, 10 ) . "&unidadnegocio=" . $myrow ['tag'] . "&u_cortecaja=" . $myrow ['u_cortecaja'] . "&usuario=" . $row['userid'] . "
						// 	&fechaInicioImpresion=" . $fechaInicioImpresion . "&fechaTerminoImpresion=" . $row ['fechadeposito'] . "' target='_blank'>" . _ ( 'Imprimir' ) . "</a>";
						echo '<p class="page_title_text">' . '<a href="' . $rootpath . '/PrintCorteCajaV2_Detalles.php?fechacorte=' . substr ( $myrow ['fechacorte'], 0, 10 ) . '&unidadnegocio=' . $myrow ['tag']. '&unidadejecutora='.$myrow['ln_ue'].'&u_cortecaja=' . $myrow ['u_cortecaja'] . '&usuario=' . $row['userid'] . "&nu_foliocorte=" . $row['nu_foliocorte'] . '&ficha=1" target="_blank"><span class="glyphicon glyphicon-print" aria-hidden="true"></span>' . _ ( ' Ficha ' ) . '</a></p>';
						echo '<p class="page_title_text">' . '<a href="' . $rootpath . '/PrintCorteCajaV2_Detalles.php?fechacorte=' . substr ( $myrow ['fechacorte'], 0, 10 ) . '&unidadnegocio=' . $myrow ['tag']. '&unidadejecutora='.$myrow['ln_ue'].'&u_cortecaja=' . $myrow ['u_cortecaja'] . '&usuario=' . $row['userid'] . "&nu_foliocorte=" . $row['nu_foliocorte'] . '&resumen=1" target="_blank"><span class="glyphicon glyphicon-print" aria-hidden="true"></span>' . _ ( ' Resumen ' ) . '</a></p>';
						echo "<p class='page_title_text'><a href='" . $rootpath . "/PrintCorteCajaV2_Detalles.php?fechacorte=" . substr ( $myrow ['fechacorte'], 0, 10 ) . "&unidadnegocio=" . $myrow ['tag'] . "&unidadejecutora=".$myrow['ln_ue']."&u_cortecaja=" . $myrow ['u_cortecaja'] . "&usuario=" . $row['userid'] . "&nu_foliocorte=" . $row['nu_foliocorte'] . "' target='_blank'><span class='glyphicon glyphicon-print' aria-hidden='true'></span>" . _ ( ' Detalle' ) . "</a></p>";

						echo "</td>";
						echo "</tr>";
						$fechaInicioImpresion = $row ['fechadeposito'];
					}
				}
			}
			
			echo '<tr>';
			echo "<td colspan=5></td>";
			echo "<td style='text-align:right'><b>" . number_format ( $montototal, 2 ) . "</b></td>";
			echo "<td colspan=5></td>";
			?>
		</table>
	</div>
	<?php
	//echo "</form>";
}

if ((isset ( $_POST ['Editar'] )) or (isset ( $_POST ['Procesar'] )) or (isset ( $_POST ['addmovs'] )) or (isset ( $_GET ['DeleteMov'] )) or (isset ( $_POST['btnCambiarUsuario'] )) or (isset ( $_POST['addsobrante'] ))) {

	// Consulta documentos sin timbrar
	$SQLUsuario = "";
	if ($_POST['usuario'] != 'all') {
		$SQLUsuario = " AND debtortrans.userid = '".$_POST['usuario']."' ";
	}
	$SQL = "SELECT 
	debtortrans.id,
	CONCAT(tags.tagref, ' - ', tags.tagdescription) as tagname,
	CONCAT(systypescat.typeid, ' - ', systypescat.typename) as typename,
	debtortrans.folio,
	(debtortrans.ovamount + debtortrans.ovgst) as total,
	debtortrans.userid,
	debtortrans.origtrandate,
	debtortrans.uuid,
	debtortrans.reference,
	debtortrans.invtext
	FROM debtortrans
	LEFT JOIN tags ON tags.tagref = debtortrans.tagref 
	LEFT JOIN systypescat ON systypescat.typeid = debtortrans.type
	WHERE
	year(debtortrans.origtrandate) >= '2018'
	AND debtortrans.type IN (10, 110,11,13)
	AND (debtortrans.uuid = '' or debtortrans.uuid is null)
	AND debtortrans.invtext not like '%CANCELADA%' 
	AND debtortrans.tagref = '".$unidadnegocio."'
	".$SQLUsuario;
	$ErrMsg = 'No se obtuvieron las Facturas Sin Timbrar';
	$TransResult = DB_query ( $SQL, $db, $ErrMsg );
	?>
	<!--Panel de Cambios de Usuario-->
	<div class="container-fluid" style="display: none;">
		<div class="panel panel-warning">
			<!-- Default panel contents -->
			<div class="panel-heading" align="center"><h4>Facturas Sin Timbrar</h4></div>
			<table class="table table-hover">
				<tr class="bg-warning">
					<th style='text-align:center'><b>UR</b></th>
					<th style='text-align:center'><b>Tipo de Documento</b></th>
					<th style='text-align:center'><b>Folio</b></th>
					<th style='text-align:center'><b>Monto</b></th>
				</tr>
				<?php
					$totalDoc = 0;
					while ( $myrow = DB_fetch_array ( $TransResult ) ) {
						echo "<tr class='bg-warning'>";
						echo "<td style='text-align:center'>" . $myrow ['tagname'] . "</td>";
						echo "<td style='text-align:center'>" . $myrow ['typename'] . "</td>";
						echo "<td style='text-align:center'>" . $myrow ['folio'] . "</td>";
						echo "<td class='number'>" . number_format ( $myrow ['total'], 2 ) . "</td>";
						echo "</tr>";
						$totalDoc ++;
						// $total = $total + $myrow ['amt'];
					}
					
					if ($totalDoc > 0) {
						echo "<tr>";
						echo "<td colspan=4 style='text-align:center'><b><a href='SelectSalesOrderV6_0.php' target='_blank' style='font-size: 18px;'>Timbrar Documentos</a></b></td>";
						echo "</tr>";
					}
				?>
			</table>
		</div>
	</div>
	<?php
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
	CONCAT(tb_debtortrans_forma_pago.ln_paymentid, ' - ', paymentmethodssat.paymentname) as paymentname
	FROM debtortransmovs
	JOIN debtortrans ON debtortrans.type = debtortransmovs.type AND debtortrans.transno = debtortransmovs.transno
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
	and debtortrans.nu_ue = '".$unidadejecutora."'
	and gltrans.narrative not like '%cancelado%' 
	and gltrans.account in (".$cadena.") 
	
	and gltrans.narrative not like '% IVA %'
	and (gltrans.type = 12 and systypescat.typeid = 12)
	and (debtortransmovs.reference not like '70 -%' and debtortransmovs.reference not like '10 -%')
	AND debtortrans.nu_cortecaja = '0'
	AND debtortrans.alloc <> 0
	".$SQLUsuario."
	GROUP BY systypescat.typename, gltrans.trandate, gltrans.periodno, gltrans.account, chartmaster.accountname, tb_debtortrans_forma_pago.ln_paymentid";
	// and gltrans.tag = '".$unidadnegocio."' 
	// and gltrans.ln_ue = '".$unidadejecutora."' 
	// and gltrans.amount > 0 
	
	if ($_SESSION['UserID'] == "desarrollo3") {
		// echo "<br><pre>Total de Ingresos: ".$SQL."</pre><br>"; 
	}
	$verificarSql =$SQL ;
	// echo "<br>SQL: <pre>";
	// 	print_r($SQL);
	// echo "</pre><br>";
	
	$TransResult = DB_query ( $SQL, $db, $ErrMsg );
	//$CuentasSobrante = $TransResult;
	
	$nombreunidad = "";
	$nombreUnidadEjecutora = "";
	$usql = "SELECT CONCAT(tags.tagref, ' - ', tags.tagdescription) as tagdescription, CONCAT(tb_cat_unidades_ejecutoras.ue, ' - ', tb_cat_unidades_ejecutoras.desc_ue) as desc_ue 
	FROM tags 
	JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ur = tags.tagref
	WHERE tags.tagref = '".$unidadnegocio."'
	AND tb_cat_unidades_ejecutoras.ue = '".$unidadejecutora."'";
	$ErrMsg = "LA SENTENCIA SQL FALLO DEBIDO A ";
	$uResult = DB_query ( $usql, $db, $ErrMsg );
	if ($umyrow = DB_fetch_array ( $uResult )) {
		$nombreunidad = $umyrow ['tagdescription'];
		$nombreUnidadEjecutora = $umyrow ['desc_ue'];
	}
	?>
	<div class="container-fluid">
		<div class="col-md-12">
			<div class="col-md-4">
				<h5>Fecha Corte: <span style='font-size:11pt; font-weight:bold;'><?php echo $diacorte . " - " . NombreMes ( $mescorte ) . " - " . $aniocorte; ?></span></h5>
			</div>
			<div class="col-md-4">
				<h5>UR: <span style='font-size:11pt; font-weight:bold;'><?php echo $nombreunidad; ?></span></h5>
			</div>
			<div class="col-md-4">
				<h5>UE: <span style='font-size:11pt; font-weight:bold;'><?php echo $nombreUnidadEjecutora; ?></span></h5>
			</div>
		</div>
	</div>
	<div class="container-fluid">
		<div class="panel panel-default">
			<!-- Default panel contents -->
			<div class="panel-heading" align="center"> Total de Ingresos</div>
			<!--<div class="panel-body">
			</div>-->
			<!-- Table -->
			<table class="table table-hover">
				<tr>
					<th style='text-align:center;'><b>Tipo</b></th>
					<th style='text-align:center;'><b>Fecha Pago</b></th>
					<th style='text-align:center; display: none;'><b>Periodo</b></th>
					<th style='text-align:center;'><b>Cuenta Contable</b></th>
					<th style='text-align:center;'><b>Forma de Pago</b></th>
					<th style='text-align:center;'><b>Monto</b></th>
					<th style='text-align:center; display: none;'><b>Cantidad</b></th>
				<?php
					if(in_array($_SESSION['DatabaseName'],$JIBE))
					{
						$ncu = array();

						$SQLA = "SELECT 
								gltrans.account,
								SUM(gltrans.amount) as amount,chartmaster.accountname
								FROM debtortransmovs
								LEFT JOIN systypescat ON systypescat.typeid = debtortransmovs.type
								LEFT JOIN gltrans ON gltrans.type = 12 and gltrans.typeno = debtortransmovs.transno
								LEFT JOIN chartmaster ON chartmaster.accountcode = gltrans.account
								WHERE 
								DATE_FORMAT(debtortransmovs.trandate, '%Y-%m-%d') = '".$fechacorte."'
								and debtortransmovs.tagref = '".$unidadnegocio."'
								and gltrans.tag = '".$unidadnegocio."' 
								and gltrans.narrative not like '%cancelado%' 
								and gltrans.account in (".$cadena.") 
								and gltrans.amount > 0 
								and gltrans.narrative not like '% IVA %'
								and (gltrans.type = 12 and systypescat.typeid = 12)
								and (debtortransmovs.reference not like '70 -%' and debtortransmovs.reference not like '10 -%')
								".$SQLUsuario."
								GROUP BY systypescat.typename, gltrans.trandate, gltrans.periodno, gltrans.account, chartmaster.accountname";

								

						$re = DB_query($SQLA,$db);
						while ($c = DB_fetch_array($re)) 
						{	
							$tmp = array();
							$tmp['account'] = $c['account'];
							$tmp['monto'] = $c['amount'];
							$tmp['accountname'] = $c['accountname'];
							array_push($ncu, $tmp);
						}
					}

					// PARA NOTAS DE DEVOLUCION
					$totalNotaDevolucionAnticipos = 0;

					if(in_array($_SESSION['DatabaseName'],$JIBE))
					{
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
						".$SQLUsuario."
						and debtortransmovs.tagref = '".$unidadnegocio."'
						and gltrans.tag = '".$unidadnegocio."' 
						and debtortransmovs.type = 4
					--	and gltrans.account in (".$cadenaDevolucion.")
						and gltrans.narrative not like '%cancelado%'
						and debtortrans.invtext not like '%cancela%'
					--	and gltrans.amount > 0 
						and gltrans.amount < 0 
						and gltrans.narrative not like '% IVA %'
						GROUP BY systypescat.typename, gltrans.trandate, gltrans.periodno, gltrans.account, chartmaster.accountname";
					}
					else
					{
						$SQL = "SELECT 
						systypescat.typename,
						gltrans.trandate, 
						gltrans.periodno, 
						gltrans.account,
						chartmaster.accountname, 
						gltrans.narrative,
						gltrans.typeno,
						debtortransmovs.userid,
						-- SUM(gltrans.amount) as amount
						amount
						FROM debtortransmovs
						LEFT JOIN systypescat ON systypescat.typeid = debtortransmovs.type
						LEFT JOIN gltrans ON gltrans.type = 4 and gltrans.typeno = debtortransmovs.transno
						LEFT JOIN chartmaster ON chartmaster.accountcode = gltrans.account
						LEFT JOIN debtortrans ON debtortrans.type = debtortransmovs.type and debtortrans.transno = debtortransmovs.transno
						WHERE 
						DATE_FORMAT(debtortransmovs.trandate, '%Y-%m-%d') = '".$fechacorte."'
						".$SQLUsuario."
						and debtortransmovs.tagref = '".$unidadnegocio."'
						and gltrans.tag = '".$unidadnegocio."' 
						and debtortransmovs.type = 4
						and gltrans.account in (".$cadenaDevolucion.")
						and gltrans.narrative not like '%cancelado%'
						and debtortrans.invtext not like '%cancela%'
						and gltrans.amount > 0 
						and gltrans.narrative not like '% IVA %'
						GROUP BY systypescat.typename, gltrans.trandate, gltrans.periodno, gltrans.account, chartmaster.accountname";
					}
					if ($_SESSION['UserID'] == "desarrollo") {
						//echo "<br><pre>Total de Ingresos: ".$SQL."<br>"; 
						
						//echo "<br>NOTAS DE DEVOLUCION DE ANTICIPOS: ".$SQL."<br>"; 
					}
					$TransResultDev = DB_query ( $SQL, $db, $ErrMsg );

					while ( $myrow = DB_fetch_array ( $TransResultDev) ) {
						$i = $i + 1;
						if(in_array($_SESSION['DatabaseName'],$JIBE))
						{
							for ($i=0; $i <count($ncu) ; $i++) { 
								if($ncu[$i]['account'] == $myrow['account'])
								{
									// echo "<br>".$ncu[$i]['monto'] ." + ". $myrow['amount']."  => ".($ncu[$i]['monto'] + $myrow['amount']);
									$ncu[$i]['monto']= ($ncu[$i]['monto'] + $myrow['amount']);
								}
							}
						}
						
					}

					//var_dump($ncu);


					echo "</tr>";
					echo $tableheader;
					echo "<tr class='bg-info'><td colspan=6 style='text-align:left;'><b>" . _ ( 'RECIBIDOS' ) . "</b></td></tr>";
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

						if ($CuentaEfectivo == $myrow['account']) {
                            $totalEfectivo = $totalEfectivo + $myrow ['amount'];
                        }

						$monto = $myrow ['amount'];
						//$porasignar = $monto;
						for ($i=0; $i <count($ncu) ; $i++) { 
							if($ncu[$i]['account'] == $myrow['account'])
							{
								$porasignar = $ncu[$i]['monto'];
							}
						}
						
						if (count ( $_SESSION ['MOVS'] ) > 0) {
							foreach ( $_SESSION ['MOVS'] as $Movs ) {
								$tempfromaccount = substr ( $Movs->FromAccount, 0, strpos ( $Movs->FromAccount, '-' ) );
								if ($tempfromaccount == $myrow ['account']) {
									$porasignar = $porasignar - $Movs->Amount;
								}
							}
						}
						if (abs ( $porasignar ) > 0.01 && !in_array($_SESSION['DatabaseName'], $JIBE)) {
							$asignar = 0;
						}
						
						echo "<tr>";
						echo "<td style='text-align:center;'>" . $myrow ['typename'] . "</td>";
						echo "<td style='text-align:center;'>" . $myrow ['trandate'] . "</td>";
						echo "<td style='text-align:center; display: none;'>" . $myrow ['periodno'] . "</td>";
						echo "<td style='text-align:center;'>" . $myrow ['account'] . " - " . $myrow ['accountname'] . "</td>";
						echo "<td style='text-align:center;'>" . $myrow ['paymentname'] . "</td>";
						echo "<td style='text-align:center;' class='number'>" . number_format ( $myrow ['amount'], 2 ) . "</td>";
						/*if (! isset ( $_POST ['Editar'] )) {
							echo "<td style='text-align:center' class='number'>" . number_format ( $porasignar, 2 ) . "</td>";
						}*/
						//if (! isset ( $_POST ['Editar'] ) AND in_array($_SESSION['DatabaseName'], $JIBE) == false) {
							echo "<td style='text-align:center; display: none;' class='number'>" . number_format ( $porasignar, 2 ) . "</td>";
						/*}elseif (! isset ( $_POST ['Editar'] ) AND in_array($_SESSION['DatabaseName'], $JIBE)) {
							echo "<td style='text-align:center' class='number'></td>";
						}*/

						
						echo "</tr>";
						$total = $total + $myrow ['amount'];
						$totalporasignar = $totalporasignar - $porasignar;
						$totaltotales1 = $totaltotales1 + $myrow ['amount'];
						$totaltemp1 = $totaltemp1 + $myrow ['amount'];
					}

					echo "<tr>";
					echo "<td colspan=4 style='text-align:right;'><b>" . _ ( 'Total Recibos' ) . ": &nbsp;</b></td>";
					echo "<td style='text-align:center' class='number'><b>" . number_format ( $totaltotales1, 2 ) . "</b></td>";
					/*if (! isset ( $_POST ['Editar'] )) {
						echo "<td style='text-align:center' class='number'><b>" . number_format ( $totalporasignar, 2 ) . "</b></td>";
					}*/
					//if (! isset ( $_POST ['Editar'] ) AND in_array($_SESSION['DatabaseName'], $JIBE) == false) {
					echo "<td style='text-align:center; display: none;' class='number'><b>" . number_format ( $totalporasignar, 2 ) . "</b></td>";
					/*}elseif (! isset ( $_POST ['Editar'] ) AND in_array($_SESSION['DatabaseName'], $JIBE)) {
						echo "<td style='text-align:center' class='number'><b></b></td>";
					}*/
					echo "</tr>";
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
						and g1.tag = '" . $unidadnegocio . "'
						and g1.type = 12 
						GROUP BY s.typename, g1.trandate, g1.periodno, g1.account, c.accountname";
					
					$TransResult = DB_query ( $SQL, $db, $ErrMsg );
					$totaltemp3 = 0;
					$totaltotales3 = 0;
					if (DB_num_rows ( $TransResult ) > 0) {
						echo '<table class="table table-hover">';
						echo "<tr class='bg-info'><td colspan=8 style='text-align:left;'><b>" . _ ( 'Total de Perdida Cambiaria' ) . "</b></td></tr>";
						$tableheader = "<tr>
								<td style='text-align:center'><b>" . _ ( 'Tipo' ) . "</b></td>
								<td style='text-align:center'><b>" . _ ( 'Fecha' ) . "</b></td>
								<td style='text-align:center'><b>" . _ ( 'Periodo' ) . "</b></td>
								<td style='text-align:center'><b>" . _ ( 'No. Cuenta' ) . "</b></td>
								<td style='text-align:center'><b>" . _ ( 'Cuenta' ) . "</b></td>
								<td style='text-align:center'><b>" . _ ( 'Cantidad' ) . "</b></td>";
						echo $tableheader;
						echo "<tr><td colspan=8 style='text-align:left;'><b>" . _ ( 'Perdida Cambiaria' ) . "</b></td></tr>";
						
						$i = 0;
						$k = 0;
						
						$total = 0;
						$totaltotales3 = 0;
						$totaltemp3 = 0;
						while ( $myrow = DB_fetch_array ( $TransResult ) ) {
							$i = $i + 1;
							
							echo "<tr>";
							echo "<td>" . $myrow ['typename'] . "</td>";
							echo "<td>" . $myrow ['trandate'] . "</td>";
							echo "<td>" . $myrow ['periodno'] . "</td>";
							echo "<td>" . $myrow ['account'] . "</td>";
							echo "<td>" . $myrow ['accountname'] . "</td>";
							echo "<td class='number'>" . number_format ( $myrow ['amount'], 2 ) . "</td>";
							echo "</tr>";
							// $total = $total + $myrow['amount'];
							$totaltotales3 = $totaltotales3 + $myrow ['amount'];
							$totaltemp3 = $totaltemp3 + $myrow ['amount'];
						}
						echo "<tr height=5><td colspan=8 style='text-align:left;'></td></tr>";
						echo "<tr>";
						echo "<td colspan=5 style='text-align:right;'><b>" . _ ( 'TOTAL UTILIDAD/PERDIDA CAMBIARIA' ) . ": &nbsp;</b></td>";
						echo "<td class='number'><b>" . number_format ( $totaltotales3, 2 ) . "</b></td>";
						
						echo "</tr>";
					}

					/**
					 * ********************************************************************************************************************************
					 */
					$SQLUsuario = "";
					if ($_POST['usuario'] != 'all') {
						$SQLUsuario = " and debtortransmovs.userid = '".$_POST['usuario']."' ";
					}

					$SQL = "SELECT systypescat.typename,
						gltrans.trandate,
						gltrans.periodno,
						gltrans.account,
						chartmaster.accountname,
						gltrans.narrative,
						sum(gltrans.amount) as amount
					FROM gltrans, debtortransmovs, chartmaster, systypescat
					WHERE gltrans.tag = '". $unidadnegocio."'
					$SQLUsuario
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
						echo "<tr><td colspan=8 style='text-align:left;'><b>" . _ ( 'ANTICIPOS APLICADOS' ) . "</b></td></tr>";
						$accounanterior = "";
						$total = 0;
						$i = 0;
						$k = 0;
						$totaltemp2 = 0;
						while ( $myrow = DB_fetch_array ( $TransResult ) ) {
							$i = $i + 1;
							
							echo "<tr>";
							echo "<td style='text-align:center'>" . $myrow ['typename'] . "</td>";
							echo "<td style='text-align:center'>" . $myrow ['trandate'] . "</td>";
							echo "<td style='text-align:center'>" . $myrow ['periodno'] . "</td>";
							echo "<td style='text-align:center'>" . $myrow ['account'] . "</td>";
							echo "<td style='text-align:center'>" . $myrow ['accountname'] . "</td>";
							echo "<td style='text-align:center' class='number'>" . number_format ( abs ( $myrow ['amount'] ), 2 ) . "</td>";
							/*if (! isset ( $_POST ['Editar'] )) {
								echo "<td style='text-align:center' class='number'>" . number_format ( 0, 2 ) . "</td>";
							}*/
							if (! isset ( $_POST ['Editar'] ) AND in_array($_SESSION['DatabaseName'], $JIBE) == false) {
								echo "<td style='text-align:center' class='number'>" . number_format ( 0, 2 ) . "</td>";
							}elseif (! isset ( $_POST ['Editar'] ) AND in_array($_SESSION['DatabaseName'], $JIBE)) {
								echo "<td style='text-align:center' class='number'><b></b></td>";
							}
							
							echo "</tr>";
							$total = $total + $myrow ['amount'];
							// $totalporasignar = $totalporasignar - $porasignar;
							$totaltotales2 = $totaltotales2 + $myrow ['amount'];
							$totaltemp2 = $totaltemp2 + abs ( $myrow ['amount'] );
						}
						echo "<tr>";
						echo "<td colspan=5 style='text-align:right;'><b>" . _ ( 'TOTAL ANTICIPOS APLICADOS' ) . ": &nbsp;</b></td>";
						echo "<td class='number'><b>" . number_format ( $totaltemp2, 2 ) . "</b></td>";
						if (! isset ( $_POST ['Editar'] )) {
							echo "<td class='number'><b>" . number_format ( 0, 2 ) . "</b></td>";
						}
						echo "</tr>";
					}

					/**
					* MUESTRA LOS MOVIMIENTOS DE NOTAS DE DEVOLUCION DE EFECTIVO
					* 
					*/
					$SQLUsuario = "";
					if ($_POST['usuario'] != 'all') {
						$SQLUsuario = " and debtortransmovs.userid = '".$_POST['usuario']."' ";
					}
					$totalNotaDevolucionAnticipos = 0;

					if(in_array($_SESSION['DatabaseName'],$JIBE))
					{
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
						".$SQLUsuario."
						and debtortransmovs.tagref = '".$unidadnegocio."'
						and gltrans.tag = '".$unidadnegocio."' 
						and debtortransmovs.type = 4
					--	and gltrans.account in (".$cadenaDevolucion.")
						and gltrans.narrative not like '%cancelado%'
						and debtortrans.invtext not like '%cancela%'
					--	and gltrans.amount > 0 
						and gltrans.amount < 0 
						and gltrans.narrative not like '% IVA %'
						GROUP BY systypescat.typename, gltrans.trandate, gltrans.periodno, gltrans.account, chartmaster.accountname";
					}
					else
					{
						$SQL = "SELECT 
						systypescat.typename,
						gltrans.trandate, 
						gltrans.periodno, 
						gltrans.account,
						chartmaster.accountname, 
						gltrans.narrative,
						gltrans.typeno,
						debtortransmovs.userid,
						-- SUM(gltrans.amount) as amount
						amount
						FROM debtortransmovs
						LEFT JOIN systypescat ON systypescat.typeid = debtortransmovs.type
						LEFT JOIN gltrans ON gltrans.type = 4 and gltrans.typeno = debtortransmovs.transno
						LEFT JOIN chartmaster ON chartmaster.accountcode = gltrans.account
						LEFT JOIN debtortrans ON debtortrans.type = debtortransmovs.type and debtortrans.transno = debtortransmovs.transno
						WHERE 
						DATE_FORMAT(debtortransmovs.trandate, '%Y-%m-%d') = '".$fechacorte."'
						".$SQLUsuario."
						and debtortransmovs.tagref = '".$unidadnegocio."'
						and gltrans.tag = '".$unidadnegocio."' 
						and debtortransmovs.type = 4
						and gltrans.account in (".$cadenaDevolucion.")
						and gltrans.narrative not like '%cancelado%'
						and debtortrans.invtext not like '%cancela%'
						and gltrans.amount > 0 
						and gltrans.narrative not like '% IVA %'
						GROUP BY systypescat.typename, gltrans.trandate, gltrans.periodno, gltrans.account, chartmaster.accountname";
					}
					if ($_SESSION['UserID'] == "admin") {
						//echo "<br><pre>NOTAS DE DEVOLUCION DE ANTICIPOS: ".$SQL."<br>"; 
					}
					$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
					$TransResult = DB_query ( $SQL, $db, $ErrMsg );

					/*if(in_array($_SESSION['DatabaseName'],$JIBE))// se coloca mas a rriba en el codigo para calcular el total por asignar
						{
							$ncu = array();

							$SQLA = "SELECT 
									gltrans.account,
									SUM(gltrans.amount) as amount,chartmaster.accountname
									FROM debtortransmovs
									LEFT JOIN systypescat ON systypescat.typeid = debtortransmovs.type
									LEFT JOIN gltrans ON gltrans.type = 12 and gltrans.typeno = debtortransmovs.transno
									LEFT JOIN chartmaster ON chartmaster.accountcode = gltrans.account
									WHERE 
									DATE_FORMAT(debtortransmovs.trandate, '%Y-%m-%d') = '".$fechacorte."'
									and debtortransmovs.tagref = '".$unidadnegocio."'
									and gltrans.tag = '".$unidadnegocio."' 
									and gltrans.narrative not like '%cancelado%' 
									and gltrans.account in (".$cadena.") 
									and gltrans.amount > 0 
									and gltrans.narrative not like '% IVA %'
									and (gltrans.type = 12 and systypescat.typeid = 12)
									and (debtortransmovs.reference not like '70 -%' and debtortransmovs.reference not like '10 -%')
									".$SQLUsuario."
									GROUP BY systypescat.typename, gltrans.trandate, gltrans.periodno, gltrans.account, chartmaster.accountname";
									
							// echo "<br>SQL001: <pre>";
							//print_r($SQLA);
							// echo "</pre><br>";

							$re = DB_query($SQLA,$db);
							while ($c = DB_fetch_array($re)) 
							{	
								$tmp = array();
								$tmp['account'] = $c['account'];
								$tmp['monto'] = $c['amount'];
								$tmp['accountname'] = $c['accountname'];
								array_push($ncu, $tmp);
							}
						}*/
					if (DB_num_rows ( $TransResult ) > 0) {
						$blnDevoluiconAnticipo=true;
						echo "<tr class='bg-info'><td colspan=9 style='text-align:left;'><b>" . _ ( 'NOTAS DE DEVOLUCION DE ANTICIPOS' ) . "</b></td></tr>";
						$accounanterior = "";
						$total = 0;
						$i = 0;
						$k = 0;
						$totaltemp2 = 0;
						$fechapago = "";
						/*if(in_array($_SESSION['DatabaseName'],$JIBE))
						{
							$ncu = array();

							$SQLA = "SELECT 
									gltrans.account,
									SUM(gltrans.amount) as amount,chartmaster.accountname
									FROM debtortransmovs
									LEFT JOIN systypescat ON systypescat.typeid = debtortransmovs.type
									LEFT JOIN gltrans ON gltrans.type = 12 and gltrans.typeno = debtortransmovs.transno
									LEFT JOIN chartmaster ON chartmaster.accountcode = gltrans.account
									WHERE 
									DATE_FORMAT(debtortransmovs.trandate, '%Y-%m-%d') = '".$fechacorte."'
									and debtortransmovs.tagref = '".$unidadnegocio."'
									and gltrans.tag = '".$unidadnegocio."' 
									and gltrans.narrative not like '%cancelado%' 
									and gltrans.account in (".$cadena.") 
									and gltrans.amount > 0 
									and gltrans.narrative not like '% IVA %'
									and (gltrans.type = 12 and systypescat.typeid = 12)
									and (debtortransmovs.reference not like '70 -%' and debtortransmovs.reference not like '10 -%')
									".$SQLUsuario."
									GROUP BY systypescat.typename, gltrans.trandate, gltrans.periodno, gltrans.account, chartmaster.accountname";
									
							// echo "<br>SQL001: <pre>";
								print_r($SQLA);
							// echo "</pre><br>";

							$re = DB_query($SQLA,$db);
							while ($c = DB_fetch_array($re)) 
							{	
								$tmp = array();
								$tmp['account'] = $c['account'];
								$tmp['monto'] = $c['amount'];
								$tmp['accountname'] = $c['accountname'];
								array_push($ncu, $tmp);
							}
						}*/


							// echo "<br>array: <pre>";
							// 	print_r($ncu);
							// echo "</pre><br>";

						while ( $myrow = DB_fetch_array ( $TransResult ) ) {
							$i = $i + 1;
							$fechapago = $myrow ['trandate'];
							echo "<tr>";
							echo "<td style='text-align:center'>" . $myrow ['typename'] . "</td>";
							echo "<td style='text-align:center'>" . $myrow ['trandate'] . "</td>";
							echo "<td style='text-align:center'>" . $myrow ['periodno'] . "</td>";
							echo "<td style='text-align:center'>" . $myrow ['account'] . "</td>";
							echo "<td style='text-align:center'>" . $myrow ['accountname'] . "</td>";
							echo "<td style='text-align:center' class='number'>" . number_format ( ( $myrow ['amount'] * -1 ), 2 ) . "</td>";
							if (! isset ( $_POST ['Editar'] )) {
								echo "<td style='text-align:center'></td>";
							}
							echo "</tr>";
							$totalNotaDevolucionAnticipos = $totalNotaDevolucionAnticipos + $myrow ['amount'];

							/*if(in_array($_SESSION['DatabaseName'],$JIBE))
							{
								for ($i=0; $i <count($ncu) ; $i++) { 
									if($ncu[$i]['account'] == $myrow['account'])
									{
										// echo "<br>".$ncu[$i]['monto'] ." + ". $myrow['amount']."  => ".($ncu[$i]['monto'] + $myrow['amount']);
										$ncu[$i]['monto']= ($ncu[$i]['monto'] + $myrow['amount']);
									}
								}
							}*/
							
						}

						echo "<tr>";
						echo "<td colspan=5 style='text-align:right;'><b>" . _ ( 'TOTAL NOTAS DE DEVOLUCION' ) . ": &nbsp;</b></td>";
						echo "<td style='text-align:center' class='number'><b>" . number_format ( $totalNotaDevolucionAnticipos * -1, 2 ) . "</b></td>";
						echo "</tr>";
					}
					//echo "<tr class='bg-info'><td colspan=9 style='text-align:left;'><b>" . _ ( 'NOTAS DE DEVOLUCION DE ANTICIPOS' ) . "</b></td></tr>";

					if ($totalNotaDevolucionAnticipos > 0 ) {

                        echo "<tr>";
                        echo "<td colspan=5 style='text-align:right;'><b>" . _ ( 'EFECTIVO A ENTREGAR ' ) . $CuentaEfectivo . ": &nbsp;</b></td>";
                        echo "<td style='text-align:center' class='number'><b>" . number_format ( $totalEfectivo - $totalNotaDevolucionAnticipos, 2 ) . "</b></td>";
                        echo "</tr>";
                    }
                    if (in_array($_SESSION['DatabaseName'],$JIBE)) {
                        // echo "<tr>";
                        // echo "<td colspan=5 style='text-align:right;'><b>" . _ ( 'EFECTIVO A ENTREGAR ' ) . $CuentaEfectivo . ": &nbsp;</b></td>";
                        // echo "<td style='text-align:center' class='number'><b>" . number_format ( $totalEfectivo - $myrow['amount'], 2 ) . "</b></td>";
                        // echo "</tr>";
                        echo "<tr class='bg-info'><td colspan=9 style='text-align:left;'><b>" . _ ( 'TOTALES A ENTREGAR' ) . "</b></td></tr>";
                       for ($i=0; $i <count($ncu); $i++) 
                       { 
                       	 echo "<tr>";
	                        echo "<td colspan=5 style='text-align:right;'><b>ENTREGAR " .$ncu[$i]['accountname']." ". $ncu[$i]['account'] . ": &nbsp;</b></td>";
	                        echo "<td style='text-align:center' class='number'><b>" . number_format ( $ncu[$i]['monto'], 2 ) . "</b></td>";
	                     echo "</tr>";
                       }
                    }
                    $totalIngresosCorteCaja=  ($totaltemp1 + $totaltemp2 + $totaltemp3  ) - $totalNotaDevolucionAnticipos;
                    // calculo del monto sobrante
                    $montoAgregado = 0;
					 foreach ( $_SESSION ['MOVS'] as $Movs ) {
                    	
			 			$montoAgregado = $montoAgregado  + $Movs->Amount;
					}
						//echo "TOTAL : - ".$totalIngresosCorteCaja." MOTO: ". $montoAgregado. "<br>";//impresion

						if( $montoAgregado> $totalIngresosCorteCaja){

							$totalSobrante = $montoAgregado - $totalIngresosCorteCaja;
						}

                    // agrega para ingresar movimiento de sobrante
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
						LEFT JOIN gltrans ON gltrans.type = 12 and gltrans.typeno = debtortransmovs.transno
						LEFT JOIN chartmaster ON chartmaster.accountcode = gltrans.account
						WHERE 
						DATE_FORMAT(debtortransmovs.trandate, '%Y-%m-%d') = '".$fechacorte."'
						and debtortransmovs.tagref = '".$unidadnegocio."'
						and gltrans.tag = '".$unidadnegocio."' 
						and gltrans.narrative not like '%cancelado%' 
						and gltrans.account in (".$cadena.") 
						and gltrans.amount > 0 
						and gltrans.narrative not like '% IVA %'
						and (gltrans.type = 12 and systypescat.typeid = 12)
						and (debtortransmovs.reference not like '70 -%' and debtortransmovs.reference not like '10 -%')
						".$SQLUsuario."
						GROUP BY systypescat.typename, gltrans.trandate, gltrans.periodno, gltrans.account, chartmaster.accountname";
				
					echo "<tr style='display: none;'>";
					echo "<td colspan=4 style='text-align:right;'><b>" . _ ( 'Total Ingresos' ) . ": &nbsp;</b></td>";
					if (in_array($_SESSION['DatabaseName'],$JIBE) && (count($ncu) > 0)) 
					{
						$totalx = 0;
						for ($i=0; $i<count($ncu) ; $i++) { 
							$totalx +=  $ncu[$i]['monto'];
						}
						$totalIngresosCorte2=$totalx;
						//echo "totaltx: ".$totalx." sob ".$totalSobrante."dev ".$totalNotaDevolucionAnticipos;

						$totalx = $totalx + $totalSobrante;//- abs($totalNotaDevolucionAnticipos);
						//echo "totaltx: ".$totalx." - ".$totalSobrante."- ".$totalNotaDevolucionAnticipos;
						$totalIngresosCorte = number_format ( $totalx,2 );
						echo "<td style='text-align:center' class='number'><b>" . number_format ( ($totalx), 2 ) . "</b></td>";
						//$totalIngresosCorte = number_format ( ($totaltemp1 + $totaltemp2 + $totaltemp3 + $totalSobrante ), 2 );
						//$totalDevolucionesAnticipo=(($totaltemp1 + $totaltemp2 + $totaltemp3) - $totalNotaDevolucionAnticipos);
					}
					else
					{
						$totalIngresosCorte = number_format ( ($totaltemp1 + $totaltemp2 + $totaltemp3) - $totalNotaDevolucionAnticipos, 2 );
						$totalIngresosCorte2= ($totaltemp1 + $totaltemp2 + $totaltemp3) - abs($totalNotaDevolucionAnticipos) + $totalSobrante;
						$totalx = $totalIngresosCorte2;
						//echo " SIN ANTICIPO ".$totalx." - ".$totalIngresosCorte2;

						echo "<td style='text-align:center' class='number'><b>" . number_format ( $totalx, 2 ) . "</b></td>";
						
					}
					
					//if (! isset ( $_POST ['Editar'] ) AND in_array($_SESSION['DatabaseName'], $JIBE) == false) {
					echo "<td style='text-align:center; display: none;' class='number'><b>" . number_format ( $totalporasignar, 2 ) . "</b></td>";
					/*}elseif (! isset ( $_POST ['Editar'] ) AND in_array($_SESSION['DatabaseName'], $JIBE)) {
						echo "<td style='text-align:center' class='number'><b></b></td>";
					}*/
					echo "</tr>";
				?>
			</table>
		</div>
	</div>
	<?php
}

if ((isset ( $_POST ['Procesar'] )) or (isset ( $_POST ['addmovs'] )) or (isset ( $_GET ['DeleteMov'] )) or (isset ( $_GET ['DeleteMov'] )) or (isset ( $_POST ['addsobrante'] ) )) {


	?>
	<div class="container-fluid">
		<div class="bg-danger" id="MsjAbancos" style="display: none;">No se permite agregar una cantidad mayor a la del total de los ingresos</div>
		<div class="col-md-6">
			<div class="panel panel-default">
				<!-- Default panel contents -->
					<div class="panel-heading" align="center" id='enCajas'> EN CAJAS PUENTE</div>
					<div id='enCajas2'>
					<table class="table table-hover">
						<!--tr>
							<th style='text-align:center'><b>&nbsp;</b></th>
							<th style='text-align:center'><b>&nbsp;</b></th>
							<th style='text-align:center'><b>&nbsp;</b></th>
							<th style='text-align:center'><b>&nbsp;</b></th>
						</tr-->
						<tr>
							<td><b>Referencia</b></td>
							<td><input class="form-control" type='text' name='reference' value='' size='20' maxlength='50' /></td>
						</tr>
						<tr>
							<td><b>Cuenta</b></td>
							<td>
								<select class="form-control" name='fromaccount'>
									<?php
										$SQL = "SELECT gltempcashpayment,gltempcheckpayment,gltempccpayment,gltemptransferpayment,gltempcheckpostpayment
											FROM companies";
										$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
										$TransResult = DB_query ( $SQL, $db, $ErrMsg );
										
										$cadena = "";
										while ( $myrow = DB_fetch_array ( $TransResult ) ) {
											$cadena = "'" . $myrow ['gltempcashpayment'] . "','" . $myrow ['gltempcheckpayment'] . "','" . $myrow ['gltempccpayment'] . "','" . $myrow ['gltemptransferpayment'] . "','" . $myrow ['gltempcheckpostpayment'] . "'";
										}
										
										$SQL = "SELECT gltrans.account,
											chartmaster.accountname
											FROM gltrans, chartmaster, systypescat
											WHERE gltrans.trandate = STR_TO_DATE('" . $fechacorte . "', '%Y-%m-%d')
											and gltrans.account in (" . $cadena . ")
											and gltrans.amount > 0
											and gltrans.account = chartmaster.accountcode
											and gltrans.type = systypescat.typeid
											and gltrans.tag = '" . $unidadnegocio . "'
											GROUP BY typename, trandate, periodno, account, accountname";
										
										$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
										$TransResult = DB_query ( $SQL, $db, $ErrMsg );
										
										while ( $myrow = DB_fetch_array ( $TransResult ) ) {
											echo "<option selected VALUE='" . $myrow ['account'] . "-" . $myrow ['accountname'] . "'>" . $myrow ['accountname'] . "</option>";
										}
										
										$sql2 = "SELECT h.accountname, h.accountcode
											FROM chartmaster h
											WHERE h.accountcode = '" . $_SESSION ['CompanyRecord'] ['gllink_deudoresdiversos'] . "'";
										
										$ErrMsg = _ ( 'EL SQL FALLO DEBIDO A ' );
										$Result2 = DB_query ( $sql2, $db, $ErrMsg );
										
										if ($myrow2 = DB_fetch_array ( $Result2 )) {
											echo "<option style='background-color:#FFFF99;' VALUE='" . $myrow2 ['accountcode'] . "-" . $myrow2 ['accountname'] . "'>" . $myrow2 ['accountname'] . "</option>";
										}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td><b>Cantidad</b></td>
							<td><input class="form-control" type='text' name='amount' id="amount" value='' class='number' size='15' maxlength='16' /></td>
						</tr>
						<tr>
							<td><b>Asignar a</b></td>
							<td>
							
							<select class="form-control" name="toaccount">	
									<?php
										// $result2=DB_query("SELECT accountcode,accountname FROM chartmaster, accountgroups
										// WHERE chartmaster.group_=accountgroups.groupname AND accountgroups.pandl=0
										// and chartmaster.group_ = 'CAJA Y BANCOS'
										// ORDER BY chartmaster.accountcode",$db);
										
										$asql = "SELECT bankaccountname as bankaccountname,
												bankaccounts.accountcode as accountcode,
												bankaccounts.currcode
												FROM bankaccounts, chartmaster, tagsxbankaccounts, sec_unegsxuser
												WHERE bankaccounts.accountcode=chartmaster.accountcode and
													bankaccounts.accountcode = tagsxbankaccounts.accountcode and
													tagsxbankaccounts.tagref = sec_unegsxuser.tagref and
													sec_unegsxuser.userid = '" . $_SESSION ['UserID'] . "'
													and sec_unegsxuser.tagref = '" . $unidadnegocio . "'
												GROUP BY bankaccountname,
												bankaccounts.accountcode,
												bankaccounts.currcode";
												
												
								
										$result2 = DB_query ( $asql, $db );
										while ( $myrow = DB_fetch_array ( $result2 ) ) {
											echo "<option VALUE='" . $myrow ['accountcode'] . "-" . $myrow ['bankaccountname'] . "'>" . $myrow ['bankaccountname'] . "</option>";
										}
									
										$sql2 = "SELECT h.accountname, h.accountcode
											FROM chartmaster h
											WHERE h.accountcode = '" . $_SESSION ['CompanyRecord'] ['gllink_deudoresdiversos'] . "'";
											echo "<br><pre>".$sql2;
										
										$ErrMsg = _ ( 'EL SQL FALLO DEBIDO A ' );
										$Result2 = DB_query ( $sql2, $db, $ErrMsg );
										
										if ($myrow2 = DB_fetch_array ( $Result2 )) {
											echo "<option style='background-color:#FFFF99;' VALUE='" . $myrow2 ['accountcode'] . "-" . $myrow2 ['accountname'] . "'>" . $myrow2 ['accountname'] . "</option>";
										}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td><b>Fecha Deposito</b></td>
							<td><input class="form-control" type="text" id="FechadepositoCajaFuente" name="FechadepositoCajaFuente" placeholder="Fecha de Inicio" value="<?php echo $Fechadeposito; ?>" /></td>
						</tr>
						<tr>
							<td colspan="4" style="text-align: center;">
								<input type='hidden' name='u_detallecorte' value='0'>
								<input type="hidden" name="usuario" value="<?php echo $_POST['usuario']; ?>">
								<component-button type="button" onclick="validarEliminarPago()" name='addmovs' id="addmovs"  class="glyphicon glyphicon-saved" value="AGREGAR"></component-button>
							</td>
						</tr>
					</table>
					</div>
				
					<div class="panel-title" align="center" id='sobrante'>CAPTURA DE SOBRANTE</div>
					<div id='sobrante2'>
					<table class="table table-striped table-dark">
						<tr>
							<td class="bg-success" style="text-align:right"><b>Cuenta Puente</b></td>
							<td class="bg-success">
								<select class="form-control" name='toSobrante' id='toSobrante'>									
									<?php
										$SQL = "SELECT gltempcashpayment,gltempcheckpayment,gltempccpayment,gltemptransferpayment,gltempcheckpostpayment
											FROM companies";
										$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
										$TransResult = DB_query ( $SQL, $db, $ErrMsg );
										
										$cadena = "";
										while ( $myrow = DB_fetch_array ( $TransResult ) ) {
											$cadena = "'" . $myrow ['gltempcashpayment'] . "','" . $myrow ['gltempcheckpayment'] . "','" . $myrow ['gltempccpayment'] . "','" . $myrow ['gltemptransferpayment'] . "','" . $myrow ['gltempcheckpostpayment'] . "'";
										}
										
										$SQL = "SELECT gltrans.account,
											chartmaster.accountname
											FROM gltrans, chartmaster, systypescat
											WHERE gltrans.trandate = STR_TO_DATE('" . $fechacorte . "', '%Y-%m-%d')
											and gltrans.account in (" . $cadena . ")
											and gltrans.amount > 0
											and gltrans.account = chartmaster.accountcode
											and gltrans.type = systypescat.typeid
											and gltrans.tag = " . $unidadnegocio . "
											GROUP BY typename, trandate, periodno, account, accountname";
										
										$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
										$TransResult = DB_query ( $SQL, $db, $ErrMsg );
										
										while ( $myrow = DB_fetch_array ( $TransResult ) ) {
											echo "<option selected VALUE='" . $myrow ['account'] ."'>" . $myrow ['accountname'] . "</option>";
										}
										
										$sql2 = "SELECT h.accountname, h.accountcode
											FROM chartmaster h
											WHERE h.accountcode = '" . $_SESSION ['CompanyRecord'] ['gllink_deudoresdiversos'] . "'";
										
										$ErrMsg = _ ( 'EL SQL FALLO DEBIDO A ' );
										$Result2 = DB_query ( $sql2, $db, $ErrMsg );
										
										if ($myrow2 = DB_fetch_array ( $Result2 )) {
											echo "<option style='background-color:#FFFF99;' VALUE='" . $myrow2 ['accountcode'] . "'>" . $myrow2 ['accountname'] . "</option>";
										}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td class="bg-success" style="text-align:right"><b>Cuenta</b></td>
							<td class="bg-success">
								<select class="form-control" name='fromSobrante' id='fromSobrante'>
									<?php
										$SQL = "SELECT gltempcashpayment,gltempcheckpayment,gltempccpayment,gltemptransferpayment,gltempcheckpostpayment
											FROM companies";
										$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
										$TransResult = DB_query ( $SQL, $db, $ErrMsg );
										
										$cadena = "";
										while ( $myrow = DB_fetch_array ( $TransResult ) ) {
											$cadena = "'" . $myrow ['gltempcashpayment'] . "','" . $myrow ['gltempcheckpayment'] . "','" . $myrow ['gltempccpayment'] . "','" . $myrow ['gltemptransferpayment'] . "','" . $myrow ['gltempcheckpostpayment'] . "'";
										}
										
										$SQL = "SELECT gltrans.account,
											chartmaster.accountname
											FROM gltrans, chartmaster, systypescat
											WHERE gltrans.trandate = STR_TO_DATE('" . $fechacorte . "', '%Y-%m-%d')
											and gltrans.account in (" . $cadena . ")
											and gltrans.amount > 0
											and gltrans.account = chartmaster.accountcode
											and gltrans.type = systypescat.typeid
											and gltrans.tag = " . $unidadnegocio . "
											GROUP BY typename, trandate, periodno, account, accountname";

										$SQL = "SELECT * FROM chartmaster WHERE accountcode in ('".$_SESSION ['CompanyRecord'] ['gllink_sobrantesfaltantescaja']."','".$_SESSION ['CompanyRecord'] ['gllink_faltantes']."')";
										
										$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
										$TransResult = DB_query ( $SQL, $db, $ErrMsg );
										
										while ( $myrow = DB_fetch_array ( $TransResult ) ) {
											//if($myrow ['accountcode'] == $CuentaEfectivo)
											echo "<option selected VALUE='" . $myrow ['accountcode'] . "'>" . $myrow ['accountcode']. "-" . $myrow ['accountname'] . "</option>";
										}
										
										
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td class="bg-success" style="text-align:right" ><b>Referencia</b></td>
							<td class="bg-success"><input class="form-control" type='text' name='reference2' value='' size='20' maxlength='50' value='Sobrante'/></td>
						</tr>
						<tr>
							<td class="bg-success" style="text-align:right"><b>Cantidad</b></td>
							<td class="bg-success"><input class="form-control" type='text' name='amount2' id="amount2" value='' class='number' size='15' maxlength='16' /></td>
						</tr>
						<tr>
							<td class="bg-success" style="text-align:right"><b>Fecha Deposito</b></td>
							<td class="bg-success"><input class="form-control" type="text" id="FechadepositoSobrantes" name="FechadepositoSobrantes" placeholder="Fecha de Inicio" value="<?php echo $Fechadeposito; ?>" /></td>
						</tr>
						<tr>
							<td class="bg-success" colspan="4" style="text-align: center;">
								<input type='hidden' name='u_detallecorte2' value='0'>
								<input type="hidden" name="usuario2" value="<?php echo $_POST['usuario']; ?>">
								<button class="btn btn-primary" type='button' onclick="agregarSobrante()" name='addsobrante' id="addsobrante">Registrar</button>
							</td>
						</tr>
					</table>
					</div>
			</div>
		</div>

		<script type="text/javascript">
			function validarEliminarPago(){
				var totalIngresosCorte = document.getElementById("totalIngresosCorte");
				var amount = document.getElementById("amount");
				if (Number(amount.value) <= Number(totalIngresosCorte.value)) {
					var tipoAbono = document.getElementById('tipoAbono');
					tipoAbono.value = "CajaPuente";
					var addmovs = document.getElementById('addmovs');
        			addmovs.setAttribute('type', 'submit');

					addmovs.click();
				}else{
					document.getElementById("MsjAbancos").style.display="block";
				}
			}
			function agregarSobrante(){
				var montosobrante = document.getElementById("amount2");
				var fromSobrante=document.getElementById("fromSobrante");
				var toSobrante=document.getElementById("toSobrante");
				if(Number(montosobrante.value)>0){
					var tipoAbono = document.getElementById('tipoAbono');
					tipoAbono.value = "Sobrante";
					var agregarSobrante = document.getElementById('addsobrante');
        			agregarSobrante.setAttribute('type', 'submit');
					agregarSobrante.click();

				}
			}
		</script>
		<div class="col-md-6">
			<div class="panel panel-default">
				<!-- Default panel contents -->
				<div class="panel-heading" align="center"> A BANCOS</div>
				<!--<div class="panel-body">
				</div>-->
				<!-- Table -->
				<table class="table table-hover">
					<tr>
						<th style='text-align:center'><b>Referencia</b></th>
						<th style='text-align:center'><b>Vendedor</b></th>
						<th style='text-align:center'><b>Origen</b></th>
						<th style='text-align:center'><b>Destino</b></th>
						<th style='text-align:center'><b>Cantidad</b></th>
						<th style='text-align:center'><b>Fecha Deposito</b></th>
						<th style='text-align:left;'><b><? echo '<a class="btn btn-danger" href=' . $_SERVER ['PHP_SELF'] . '?' . SID . '&DeleteMov=ALL&fechacorte=' . substr ( $fechacorte, 0, 10 ) . "&fechacortede=" . $fechacortede . "&fechacortea=" . $fechacortea . "&unidadnegocio=" . $unidadnegocio . "&u_cortecaja=" . $u_cortecaja . "&usuario=" . $_POST['usuario'] . '>' . _ ( ' X ' ) . '</a>';?></b></th>
					</tr>
					<?php
						$Total = 0;
						foreach ( $_SESSION ['MOVS'] as $Movs ) { //$Movs->vendedorid - id de usuario
							echo '<tr>
								<td>' . $Movs->Reference . '</td>
								<td>' . $Movs->vendedorname . '</td>
								<td>' . $Movs->FromAccount . "<br><i><span style='font-size:7pt;'>" . substr ( $ClientItem->gltemp, strpos ( $ClientItem->gltemp, '-' ) + 1 ) . "<br>" . $ClientItem->reference . '</span></i></td>
								<td>' . $Movs->ToAccount . '</td>
								<td class=number>' . $Movs->Amount . '</td>
								<td>' . $Movs->Fechadeposito . '</td>
								<td>
									<a class="btn btn-primary" href=' . $_SERVER ['PHP_SELF'] . '?' . SID . '&DeleteMov=' . $Movs->ID . '&fechacorte=' . substr ( $fechacorte, 0, 10 ) . "&fechacortede=" . $fechacortede . "&fechacortea=" . $fechacortea . "&unidadnegocio=" . $unidadnegocio . "&u_cortecaja=" . $u_cortecaja . "&usuario=" . $_POST['usuario'] . '>' . _ ( ' X ' ) . '</a>
								</td>
								</tr>';
							$Total = $Total + $Movs->Amount;
						}

						$totalIngreCorte=0;
						
						/*if($blnDevoluiconAnticipo){
							$asignar = 0;
							$totalIngreCorte=$totalDevolucionesAnticipo;
							//echo "<br> TOTALDEV".$totalIngreCorte." - ".$totalDevolucionesAnticipo;
							echo "Totaldev".$totalIngreCorte." - ".$totalDevolucionesAnticipo;
							
							if(number_format ($totalDevolucionesAnticipo, 2 ) == number_format ( $Total, 2 )){
								$asignar = 1;
							}
						}else{
							$totalIngreCorte=$totalIngresosCorte2;
						}*/
						//$_POST['montosobrante'] =   $totalIngreCorte -$Total;
						
						//$Total = $Total -abs($totalNotaDevolucionAnticipos);
						

						//echo "Total ".$Total." - ".$totalx." - ".$totalSobrante." /".$totalNotaDevolucionAnticipos;
						$Diferencia = abs($Total - $totalx);
						//echo "DIF = ".$Diferencia ;
						//VERIFICAR SI HAY FALTANTE
						$Result = DB_query ( $verificarSql, $db );
						//echo "<br> numm".B_num_rows($Result);
						if(DB_num_rows($Result)>0){
							$bandera= true;
						}else{
							$bandera= false;
						}
						
						while ($myrow = DB_fetch_array($Result)) {
							$cuenta = $myrow['account'];
							$SiCuenta = false;
							//var_dump($_SESSION ['MOVS']);
							foreach ( $_SESSION ['MOVS'] as $Movs ) {
								$cuentabanco = explode("-", $Movs->FromAccount);
								//echo "<br>".$cuenta."- ".$Movs->FromAccount;
								if($cuentabanco[0]== $cuenta){
									$SiCuenta = true;
								}
							}
							//echo $SiCuenta;
							if($SiCuenta == false){
								$bandera= $SiCuenta;
							}


						}
						
						if($bandera==true AND $Diferencia >.009 ){
						 prnMsg("Existe un faltante de: ".number_format ( $Diferencia, 2 ),'warn');
						}

						//$asignar = 0;
						if( $Diferencia<0.009 AND $Total<>0){
							$asignar = 1;
						}else{
							$asignar = 0;
						}
						
						echo "<tr>";
						echo "<td colspan='3' style='text-align:right;'>Total:</td><td class=number><b>" . number_format ( $Total, 2 ) . "</b></td>";
						echo "<td colspan='3'>&nbsp;</td></tr>";
						if(isset($_POST['amount2']) AND $_POST['amount2']>0 ){
							echo "<tr class='bg-info'>";
							echo "<td colspan='3' style='text-align:right;'>Sobrante:</td><td class=number><b>" . number_format ( $_POST['amount2'], 2 ) . "</b></td>";
							echo "<td><input type='hidden' value ='".$_POST['amount2']."'  name ='montosobrante' ></td> ";
							echo "<td><input type='hidden' value ='".$_POST['reference2']."'  name ='referenciasobrante'></td> ";
							echo "<td><input type='hidden' value ='".$_POST['fromSobrante']."'  name ='fromSobrante'></td> ";
							echo "<td><input type='hidden' value ='".$_POST['toSobrante']."'  name ='toSobrante'></td> ";
							echo "<td colspan='3'>&nbsp;</td></tr>";
						}
						
						echo "<td colspan='3'>&nbsp;</td></tr>";
						if ($asignar == 1) {
							echo "<tr>";
							echo "<td colspan='3' style='text-align: center;'>";
							$permiso = Havepermission ( $_SESSION ['UserID'], 200, $db );
							$permiso = 1;
							if ($permiso != 0) {
								echo "<label>Hora: </label>";
								echo "<select name='cmbHora'>";
								$sql   = "SELECT distinct hour FROM cat_Hours order by hour";
								$Meses = DB_query($sql, $db);
								while ($myrowMes = DB_fetch_array($Meses, $db)) {
								    $Mesbase = $myrowMes['hour'];
								    if (rtrim(intval($cmbHora)) == rtrim(intval($Mesbase))) {
								        echo '<option  VALUE="' . $myrowMes['hour'] . '" selected>' . $myrowMes['hour'];
								    } else {
								        echo '<option  VALUE="' . $myrowMes['hour'] . '" >' . $myrowMes['hour'];
								    }
								}
								echo "</select>";
								echo "<label>Minuto: </label>";
								echo "<select name='cmbMinuto'>";
								$sql   = "SELECT distinct minute FROM cat_Hours order by minute";
								$Meses = DB_query($sql, $db);
								while ($myrowMes = DB_fetch_array($Meses, $db)) {
								    $Mesbase = $myrowMes['minute'];
								    if (rtrim(intval($cmbMinuto)) == rtrim(intval($Mesbase))) {
								        echo '<option  VALUE="' . $myrowMes['minute'] . '" selected>' . $myrowMes['minute'];
								    } else {
								        echo '<option  VALUE="' . $myrowMes['minute'] . '" >' . $myrowMes['minute'];
								    }
								}
								echo "</select>";
								echo "<td>";
								echo "<input class='btn btn-primary' type='submit' name='Prepoliza' value='PREPOLIZA'>";
								echo "</td>";
							}
							echo "</td>";
							echo "<td colspan='2' style='text-align: center;'>";
							$permiso = Havepermission ( $_SESSION ['UserID'], 201, $db );
							$permiso = 1;
							if ($permiso != 0) {
								?>
								<component-button type="submit"  name='Aceptarprepoliza' id="Aceptarprepoliza"  class="glyphicon glyphicon-floppy-saved" value="ACEPTAR PREPOLIZA"></component-button>
								<?php
								//echo "<input class='btn btn-primary' type='submit' name='Aceptarprepoliza' value='ACEPTAR PREPOLIZA'>";
							}

							
							//echo "<div id='btnsobrante'>";
							if(in_array($_SESSION['DatabaseName'], $JIBE)){
							//echo "</td></tr>"; 
							echo "<td><input class='btn btn-danger' type='button' name='SOBRANTE' value='SOBRANTE'  onclick='preguntaSobrante()'></td>";	
							//<td class='numero_normal'><a onclick='borrar(this.href); return false;' href=\"%s&id=%s&Offset=%s&num_reg=%s&Delete=1\">" . _("Eliminar") . "</a></td>
							//echo "</div>";
							}
							echo "</td>";
							//echo "<td></td>";
							echo "</tr>";
						}
					?>
					<input type="hidden" name="totalIngresosCorte" id="totalIngresosCorte" value="<?php echo $totalIngresosCorte2; ?>">
					<input type="hidden" name="totalAbancos" id="totalAbancos" value="<?php echo $Total; ?>">
				</table>
			</div>
		</div>
	</div>


	<?php
}


if (isset ( $_POST ['Editar'] ) or isset( $_POST['btnCambiarUsuario'] )) {
	
	/* DEFINO ARRAY PARA IR ALMACENANDO MOVIMIENTOS */
	
	/* TIPO DE DOCUMENTOS
	110 - FACTURA DE CONTADO
	10 - FACTURA DE CREDITO
	21 - NOTA DE CARGO (MORATORIOS)
	90 - INGRESO DE CAJA
	80 - ANTICIPO
	*/
	
	unset ( $_SESSION ['systypes'] );
	$_SESSION ['systypes'] = array ();
	
	$SQL = "SELECT systypescat.typeid, systypescat.typename FROM systypescat";
	$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
	$TransResult = DB_query ( $SQL, $db, $ErrMsg );
	while ( $myrow = DB_fetch_array ( $TransResult ) ) {
		$_SESSION ['systypes'] [$myrow ['typeid']] = $myrow ['typename'];
	}

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
			SUBSTRING_INDEX(debtortrans.folio, '|', -1) as numFolio,
			debtortrans.type as datosfactura_Type,
			debtortrans.transno as datosfactura_Transno,
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
			and debtortrans.nu_ue = '".$unidadejecutora."'
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
			".$SQLUsuario."
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
			SUBSTRING_INDEX(datosfactura.folio, '|', -1) as numFolio,
			datosfactura.type as datosfactura_Type,
			datosfactura.transno as datosfactura_Transno,
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
			LEFT JOIN debtortrans d2 ON d2.transno = dm.transno AND d2.type = dm.type
			LEFT JOIN systypescat s ON s.typeid = d2.type
			LEFT JOIN debtorsmaster m ON m.debtorno = dm.debtorno
			LEFT JOIN www_users ON www_users.userid = d2.userid
			LEFT JOIN custallocns ON custallocns.transid_allocfrom = d2.id
			LEFT JOIN debtortrans datosfactura ON datosfactura.id = custallocns.transid_allocto
			LEFT JOIN gltrans ON gltrans.typeno = dm.transno AND gltrans.type = dm.type
			WHERE
			DATE_FORMAT(dm.trandate, '%Y-%m-%d') = '".$fechacorte."'
			and dm.tagref = '".$unidadnegocio."'
			and d2.nu_ue = '".$unidadejecutora."'
			and (
					dm.type = 12 and ( (dm.alloc <> 0 and dm.reference not in ('0','80')) or (dm.reference='80') or (dm.alloc > 0 and dm.reference='0') )
				)
			and (
					( dm.transno = d2.transno AND dm.type = d2.type AND dm.tagref = d2.tagref AND dm.reference <> '' )
					or
					( SUBSTRING(dm.reference, LOCATE(' - ', dm.reference)+3 ) = d2.transno AND SUBSTRING(dm.reference, 1, LOCATE(' - ', dm.reference)-1) = d2.type AND dm.tagref = d2.tagref AND dm.type = 80 )
				)
			".$SQLUsuario2."
			and gltrans.narrative not like '%cancelado%' 
			and gltrans.amount > 0 
			and gltrans.type in (10,110,119,12)
			and gltrans.narrative not like '% IVA %'
			and d2.nu_cortecaja = '0'
			and (dm.reference not like '70 -%' and dm.reference not like '10 -%')
			and gltrans.account in (".$cadenaCuentaPuente.") 
			GROUP BY dm.type, dm.transno
			UNION ALL
			SELECT 
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
			IFNULL(debtortrans.folio,'s/n') as folio,
			debtortrans.alloc as asignado,
			CASE WHEN debtortrans.type IN (10,110,119,125) THEN 1 ELSE 0 END AS pendiente,
			anticipo.folio as foliorecibo,
			systypescat.typename,
			debtorsmaster.name, 
			debtorsmaster.debtorno,
			debtortrans.userid as userid,
			'' as realname,
			'' as idgltrans,
			debtortrans.codesat as datosfactura_codesat,
			debtortrans.folio as datosfactura_Folio,
			SUBSTRING_INDEX(debtortrans.folio, '|', -1) as numFolio,
			debtortrans.type as datosfactura_Type,
			debtortrans.transno as datosfactura_Transno,
			debtortrans.order_ as datosfactura_Orden,
			debtortrans.ovamount as datosfactura_Ovamount,
			debtortrans.ovgst as datosfactura_Ovgst,
			'Con aplicacion de anticipo' as ordenar,
			' ' as paymentnameOrder,
			0 as montoRecibo
			FROM debtortrans
		  INNER JOIN 
		  ( SELECT sum(montoncredito ) as montonc,salesinvoiceadvance.transidinvoice aS id ,  group_concat( nc.folio) as folio
		  FROM 
		  salesinvoiceadvance
		  INNER JOIN debtortrans nc ON salesinvoiceadvance.`transidncredito` = nc.id 
		  WHERE nc.tagref = '".$unidadnegocio."' AND nc.invtext not like'%CANCELA%'
		  Group by salesinvoiceadvance.transidinvoice
		  ) as anticipo ON anticipo.id = debtortrans.id
	      INNER JOIN systypescat ON debtortrans.type = systypescat.typeid
  		  INNER JOIN debtorsmaster ON debtortrans.debtorno = debtorsmaster.debtorno
			WHERE 
			 DATE_FORMAT(debtortrans.trandate, '%Y-%m-%d') = '".$fechacorte."'
			and debtortrans.tagref = '".$unidadnegocio."'
			and debtortrans.nu_ue = '".$unidadejecutora."'
			AND abs(debtortrans.`ovamount` +debtortrans.`ovgst`) - abs(anticipo.montonc)<=0.01 ".$SQLUsuario."

			ORDER BY ordenar, paymentnameOrder, datosfactura_Folio, realname, invtext";
	if ($_SESSION['UserID'] == "desarrollo3") {
		//and gltrans.account in (".$cadenaCuentaPuente.") 
		// echo "<br><pre>Detalle Aplicacion de Ingresos: ".$SQL."<br></pre>";
	}
	$ErrMsg = _ ( 'No transactions were returned by the SQL becauses' );
	$TransResult = DB_query ( $SQL, $db, $ErrMsg );
	?>

	<div class="container-fluid">
		<div class="panel panel-default">
			<!-- Default panel contents -->
			<div class="panel-heading" align="center"> Detalle Aplicación de Ingresos</div>
			<!--<div class="panel-body">
			</div>-->
			<!-- Table -->
			<table class="table table-hover">
				<tr>
					<!-- <th style='text-align:center;'><b>Cambiar Usuario</b></th> -->
					<th style='text-align:center;'><b></b></th>
					<th style='text-align:center; display: none;'><b>Folio Factura</b></th>
					<th style='text-align:center;'><b>Folio Recibo</b></th>					
					<th style='text-align:center;'><b>Contribuyente</b></th>
					<th style='text-align:center;'><b>Usuario</b></th>
					<th style='text-align:center;'><b>Detalle</b></th>
					<th style='text-align:center;'><b>Forma de pago</b></th>
					<th style='text-align:center; display: none;'><b>Pago Recibo</b></th>
					<!--th style='text-align:center;'><b>Monto</b></th>
					<th style='text-align:center;'><b>IVA</b></th-->
					<th style='text-align:center;'><b>Monto</b></th>
				</tr>
				<?php
					//Agregar Recibos con factura
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
					$facturasAnticipo = 0;

					$cadenaRegistrosGen = "";
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
								echo "<tr>";
								echo "<td colspan='6' style='text-align: right'><b>SUBTOTAL " .  " : &nbsp;</b></td>";
								//echo "<td class='number'><b>" . number_format ( $paymentnameOrderMonto, 2 ) . "</b></td>";
								//echo "<td class='number'><b>" . number_format ( $paymentnameOrderIva, 2 ) . "</b></td>";
								echo "<td class='number'><b>" . number_format ( $paymentnameOrderTotal, 2 ) . "</b></td>";
								echo "</tr>";
								$paymentnameOrderMonto = 0;
								$paymentnameOrderIva = 0;
								$paymentnameOrderTotal = 0;
							}
						}

						// if ($myrow['typename'] != $typenameanterior)
						if ($u_tipo != $tipoant) {
							if ($i != 1) {
								echo "<tr>";
								echo "<td colspan='6' style='text-align: right'><b>TOTAL"  . " : &nbsp;</b></td>"; //strtoupper ( $_SESSION ['systypes'] [intval ( $tipoant )] ) 
								//echo "<td class='number'><b>" . number_format ( $totalamount, 2 ) . "</b></td>";
								//echo "<td class='number'><b>" . number_format ( $totaliva, 2 ) . "</b></td>";
								echo "<td class='number'><b>" . number_format ( $totaltotales2, 2 ) . "</b></td>";
								echo "</tr>";
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
							echo "<tr class=''><td colspan='7'><b>" . "" . "&nbsp;</b></td></tr>";
							echo "<tr class='bg-success'><td colspan='7'><b>" . "Facturas Sin Recibo" . "&nbsp;</b></td></tr>";
							$facturasSinReciboMsj = 1;
						}
						if ($myrow ['ordenar'] == "Con aplicacion de anticipo" and $facturasAnticipo == 0 ) {
							echo "<tr class=''><td colspan='7'><b>" . "" . "&nbsp;</b></td></tr>";
							echo "<tr class='bg-success'><td colspan='7'><b>" . "Pagadas con Anticipo" . "&nbsp;</b></td></tr>";
							$facturasAnticipo = 1;
						}

						//Mostrar le metodo de pago
						$paymentnameOrderAnt = $paymentnameOrder;
						if ($MostrarMetodoPago == 1) {
							echo "<tr class='bg-info'><td colspan='7'><b>" . ($paymentnameOrder) . "&nbsp;</b></td></tr>";
						}
						
						if ($myrow ['pendiente'] == 1) {
							echo "<tr class='danger'>";
						} else {
							echo "<tr>";
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

						if (empty($cadenaRegistrosGen)) {
							$cadenaRegistrosGen = "'".$myrow['datosfactura_Type']."_".$myrow ['datosfactura_Transno']."'";
						} else {
							$cadenaRegistrosGen .= ",'".$myrow['datosfactura_Type']."_".$myrow ['datosfactura_Transno']."'";
						}

						echo "<td style='text-align:center; display: none;'><input type='checkbox' value='".$myrow ['datosfactura_Type']."_".$myrow ['datosfactura_Transno']."' name='FoliosCambiar[]' /></td>";
						echo "<td style='text-align:center;'>".$numRegistro."</td>";
						echo "<td style='text-align:center; display: none;'>" . $myrow ['datosfactura_Folio'] . "</td>";
						echo "<td style='text-align:center;'>" . $myrow ['foliorecibo'] . "</td>";
						echo "<td style='text-align:center;'>" . $myrow ['debtorno'] . " - " . $myrow ['name'] . "</td>";
						echo "<td style='text-align:center;'>" . $myrow ['realname'] . "</td>";
						echo "<td style='text-align:center;'>" . $myrow ['invtext'] . "</td>";

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
						echo "<td style='text-align:center'>".($paymentname)."</td>";

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
						$ErrMsg = _ ( 'Error al obtener de las cuentas puente');
						$sql = "SELECT account FROM gltrans WHERE type='12' and typeno = '".$transnoRecibo."' and account in ('".$cuentasPuente."') limit 1;";
						$rowCuentas = DB_query ( $sql, $db, $ErrMsg );
						$num_pagoRecibos = "";
						$cuentasPuenteName = "";
						$cuentasPuenteNameMostrar = "";
						$mostrarTarjeta = 0;
						$sqlT = "";
						$account = "";
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
						}else{

							$sqlCom = "SELECT companies.gltempcashpayment as efectivo, companies.gltempcheckpayment as cheque, companies.gltempccpayment as credito, companies.gltempccpayment as debito, companies.gltemptransferpayment as transferencia FROM companies";
							$rowCount = DB_query ( $sqlCom, $db);
							// $counts = DB_fetch_array ( $rowCount );
							$companies = "";
							while ($coun = DB_fetch_array ( $rowCount ) ) {
								$companies = $coun['efectivo'].",".$coun['cheque'].",".$coun['credito'].",".$coun['debito'].",".$coun['transferencia'];
							}

							$sqlT = "SELECT account FROM gltrans WHERE type='12' and typeno = '".$transnoRecibo."' and account in ('".$companies."') limit 1;";
							$rows = DB_query ($sqlT, $db, $ErrMsg);
							
							while ($res = DB_fetch_array ( $rows ) ) {

								$account = $res['account'];
								
								if ($account == $cuenta_efectivo) {
									$cuentasPuenteNameMostrar = $cuentasPuenteNameMostrar."Efectivo";
								}
								if ($account == $cuenta_cheque) {
									$cuentasPuenteNameMostrar = $cuentasPuenteNameMostrar."Cheque";
								}
								if ($account == $cuenta_transferencia) {
									$cuentasPuenteNameMostrar = $cuentasPuenteNameMostrar."Transferencia";
								}
								if ($account == $cuenta_credito) {
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
							}

							$cuentasPuenteNameMostrar = $cuentasPuenteNameMostrar.",";
							$num_pagoRecibos = $num_pagoRecibos + 1;
						}

						$cuentasPuenteName = substr($cuentasPuenteName, 0, strlen($cuentasPuenteName)-1);
						$cuentasPuenteNameMostrar = substr($cuentasPuenteNameMostrar, 0, strlen($cuentasPuenteNameMostrar)-1);
						$style = "background-color: red; color: white;";
						//echo "<br>orden: ".$myrow ['datosfactura_Orden']." - paymentname: ".$paymentname." - cuentasPuenteName: ".$cuentasPuenteName;
						if ($num_pagoFactura == $num_pagoRecibos and trim(($paymentname)) == trim(($cuentasPuenteName)) and (!empty($cuentasPuenteNameMostrar))) {
							$style = "";
						}
						echo "<td style='text-align:center; display: none; ".$style."'>" . ($cuentasPuenteNameMostrar)."</td>";
						//echo "<td class='number'>" . ( (abs ( $myrow ['ovamount'] ) / $varrate), 2 ) . "</td>";
						//echo "<td class='number'>" . number_formast ( (abs ( $myrow ['ovgst'] ) / $varrate), 2 ) . "</td>";
						if($myrow['ordenar']=='Con aplicacion de anticipo'){
							echo "<td class='number'>" . number_format ( $myrow ['datosfactura_Ovamount'] + $myrow ['datosfactura_Ovgst'] /*($xtotal / $varrate)*/, 2 ) . "</td>";

						}else{
							echo "<td class='number'>" . number_format ( $myrow ['montoRecibo'] /*($xtotal / $varrate)*/, 2 ) . "</td>";
						}
						
						echo "</tr>";

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

					$SQLUsuario = "";
					if ($_POST['usuario'] != 'all') {
						$SQLUsuario = " AND debtortrans.userid = '".$_POST['usuario']."' ";
					}

					//subtotal ultimo
					echo "<tr>";
					echo "<td colspan='6' style='text-align: right'><b>Subtotal " .  " : &nbsp;</b></td>";
					//echo "<td class='number'><b>" . number_format ( $paymentnameOrderMonto, 2 ) . "</b></td>";
					//echo "<td class='number'><b>" . number_format ( $paymentnameOrderIva, 2 ) . "</b></td>";
					echo "<td class='number'><b>" . number_format ( $paymentnameOrderTotal, 2 ) . "</b></td>";
					echo "</tr>";
					//total
					echo "<tr>";
					echo "<td colspan=6 style='text-align: right'><b>" . _ ( 'Total' ) . ' ' . ": &nbsp;</b></td>"; //strtoupper ( $_SESSION ['systypes'] [intval ( $tipoant )] )
					//echo "<td class='number'><b>" . number_format ( $totalamount, 2 ) . "</b></td>";
					//echo "<td class='number'><b>" . number_format ( $totaliva, 2 ) . "</b></td>";
					echo "<td class='number'><b>" . number_format ( $totaltotales2, 2 ) . "</b></td>";
					echo "</tr>";
					echo "<tr><td colspan=7 style='text-align:left;'><b>" . strtoupper ( $myrow ['typename'] ) . "&nbsp;</b></td></tr>";
					echo "<tr>";
					echo "<td colspan=6 style='text-align: right'><b>" . _ ( 'Total Aplicación Ingresos' ) . ": &nbsp;</b></td>";
					//echo "<td class='number'><b>" . number_format ( $Ttotalamount, 2 ) . "</b></td>";
					//echo "<td class='number'><b>" . number_format ( $Ttotaliva, 2 ) . "</b></td>";
					echo "<td class='number'><b>" . number_format ( $Ttotaltotales2, 2 ) . "</b></td>";
					echo "</tr>";

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
							".$SQLUsuario;
					if ($_SESSION['UserID'] == "admin") {
						//echo "<br><pre>".$SQL;
					}
					$ErrMsg = _ ( 'No transactions were returned by the SQL becauses' );
					$TransResult = DB_query ( $SQL, $db, $ErrMsg );
					if (DB_num_rows ( $TransResult ) > 0) {
						echo "<tr class='bg-info'><td colspan='12'><b>Facturas - Nota Credito</b></td></tr>";
						$totalFacNot = 0;
						while ( $myrow = DB_fetch_array ( $TransResult ) ) {
							echo "<tr>";
							echo "<td style='text-align:center'><input type='checkbox' value='".$myrow ['type']."_".$myrow ['transno']."' name='FoliosCambiar[]' /></td>";
							echo "<td style='text-align:center'>" . $myrow ['folioFactura'] . "</td>";
							echo "<td style='text-align:center'>" . $myrow ['realname'] . "</td>";
							echo "<td style='text-align:center'>" . $myrow ['folio'] . "</td>";
							echo "<td style='text-align:center'>" . $myrow ['debtorno'] . "</td>";
							echo "<td style='text-align:center'>" . $myrow ['name'] . "</td>";
							echo "<td style='text-align:center'>" . $myrow ['invtext'] . "</td>";

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
							
							echo "<td style='text-align:center'>".($paymentname)."</td>";
							echo "<td style='text-align:center'>" . "" . "</td>";
							echo "<td class='number'>" . number_format ( $myrow ['total'], 2 ) . "</td>";
							echo "</tr>";

							$totalFacNot += $myrow ['total'];
						}

						echo "<tr>";
						echo "<td colspan='9' style='text-align: right'><b>TOTAL " .  " : &nbsp;</b></td>";
						//echo "<td class='number'><b>" . number_format ( $paymentnameOrderMonto, 2 ) . "</b></td>";
						//echo "<td class='number'><b>" . number_format ( $paymentnameOrderIva, 2 ) . "</b></td>";
						echo "<td class='number'><b>" . number_format ( $totalFacNot, 2 ) . "</b></td>";
						echo "</tr>";
					}
				?>
			</table>
		</div>
	</div>
	
	<?php
		/*
		 * INICIO MUESTRA LAS FACTURAS DE Credito SOLO POR CARACTER INFORMATIVO
		 */
		$SQLUsuario = "";
		if ($_POST['usuario'] != 'all') {
			$SQLUsuario = " AND debtortrans.userid = '".$_POST['usuario']."' ";
		}

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
			debtortrans.folio,
			debtortrans.type
			FROM debtortrans, systypescat, debtorsmaster
			WHERE debtortrans.type = systypescat.typeid
			AND day(debtortrans.origtrandate) = " . $diacorte . "
			AND month(debtortrans.origtrandate) = " . $mescorte . "
			and year(debtortrans.origtrandate) = " . $aniocorte . "
			AND debtortrans.type in (10,119)
			AND debtortrans.tagref = '" . $unidadnegocio . "'
			AND debtortrans.debtorno = debtorsmaster.debtorno
			AND debtortrans.ovamount <> 0 ".$SQLUsuario;
		if ($_SESSION['UserID'] == "admin") {
			//echo "<br><pre>Nota Informativa Facturas de Credito: ".$SQL."<br>";
		}
		$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
		$TransResult = DB_query ( $SQL, $db, $ErrMsg );
	?>
	<div class="container-fluid" style="display: none;">
		<div class="panel panel-default">
			<!-- Default panel contents -->
			<div class="panel-heading" align="center"> Nota Informativa Facturas de Credito</div>
			<!--<div class="panel-body">
			</div>-->
			<!-- Table -->
			<table class="table table-hover">
				<tr>
					<th style='text-align:center'><b>Cambiar Usuario</b></th>
					<th style='text-align:center'><b>Tipo</b></th>
					<th style='text-align:center'><b>Folio</b></th>
					<th style='text-align:center'><b>Cliente</b></th>
					<th style='text-align:center'><b>Fecha</b></th>
					<th style='text-align:center'><b>Detalle</b></th>
					<th style='text-align:center'><b>Monto</b></th>
					<th style='text-align:center'><b>IVA</b></th>
					<th style='text-align:center'><b>SubTotal</b></th>
					<th style='text-align:center'><b>Anticipo</b></th>
					<th style='text-align:center'><b>Total</b></th>
				</tr>
				<?php
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
						echo '<tr>';
						$ovamount = abs ( $myrow ['ovamount'] );
						$ovgst = abs ( $myrow ['ovgst'] );
						$alloc = abs ( $myrow ['alloc'] );
						$anticipo = ($ovamount + $ovgst) - $alloc;
						$subtotal = $alloc;
						$total = $subtotal + $anticipo;

						echo "<td style='text-align:center'><input type='checkbox' value='".$myrow ['type']."_".$myrow ['transno']."' name='FoliosCambiar[]' /></td>";
						echo "<td style='text-align:center'>" . $myrow ['typename'] . "</td>";
						echo "<td style='text-align:center'>" . $myrow ['folio'] . "</td>";
						echo "<td style='text-align:center'>" . $myrow ['name'] . "</td>";
						echo "<td style='text-align:center'>" . substr ( $myrow ['trandate'], 0, 10 ) . "</td>";
						echo "<td style='text-align:center'>" . $myrow ['invtext'] . "</td>";
						echo "<td class='number'>" . number_format ( $ovamount, 2 ) . "</td>";
						echo "<td class='number'>" . number_format ( $ovgst, 2 ) . "</td>";
						echo "<td class='number'>" . number_format ( $subtotal, 2 ) . "</td>";
						echo "<td class='number'>" . number_format ( $anticipo, 2 ) . "</td>";
						echo "<td class='number'>" . number_format ( $total, 2 ) . "</td>";
						echo "</tr>";
						
						$xtotalamount = $xtotalamount + $ovamount;
						$xtotaliva = $xtotaliva + $ovgst;
						$xtotalanticipos = $xtotalanticipos + $anticipo;
						$xtotalsubtotales = $xtotalsubtotales + $subtotal;
						$xtotaltotales = $xtotaltotales + $total;
					}
					
					echo "<tr>";
					echo "<td colspan=6 style='text-align:right;'><b>" . _ ( 'TOTALES' ) . ' ' . strtoupper ( $xtypenameanterior ) . ": &nbsp;</b></td>";
					echo "<td class='number'><b>" . number_format ( $xtotalamount, 2 ) . "</b></td>";
					echo "<td class='number'><b>" . number_format ( $xtotaliva, 2 ) . "</b></td>";
					echo "<td class='number'><b>" . number_format ( $xtotalsubtotales, 2 ) . "</b></td>";
					echo "<td class='number'><b>" . number_format ( $xtotalanticipos, 2 ) . "</b></td>";
					echo "<td class='number'><b>" . number_format ( $xtotaltotales, 2 ) . "</b></td>";
					echo "</tr>";
				?>
			</table>
		</div>
	</div>
	<?php
		/*
		 * echo '<table class="table2" cellpadding="2" width="93%">'; echo "<tr>"; echo "<td colspan=8 style='text-align:center;'>"; $diferencia = $totaltotales1 - $Ttotaltotales2; $diferencia = abs($totaltemp1 + $totaltemp2) - $Ttotaltotales2; if((abs(($totaltemp1 + $totaltemp2) - $Ttotaltotales2) < 0.9)){ echo "<input class='clsbtnimportante' type='submit' name='Procesar' value='PROCESAR'>"; }else{ prnMsg(_('No puedes realizar el corte de caja debido a que existe diferencia entre Total de Ingresos y Detalle Aplicacion de Ingresos: ' . number_format($diferencia,2)),'warn'); } echo "</td></tr></table>";
		 */
		
		/* FIN
		  MUESTRA LAS FACTURAS DE CONTADO SOLO POR CARACTER INFORMATIVO
		*/


		/* INICIO
		  MUESTRA LAS NOTAS DE CREDITO O NOTAS DE  CARGO CON ALLOC = 0;
		*/
		$SQLUsuario = "";
		if ($_POST['usuario'] != 'all') {
			$SQLUsuario = " AND debtortrans.userid = '".$_POST['usuario']."' ";
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
			debtortrans.type, 
			debtorsmaster.name,
			debtortrans.alloc as asignado
			FROM debtortrans, systypescat, debtorsmaster
			WHERE debtortrans.type = systypescat.typeid
			AND day(debtortrans.origtrandate) = " . $diacorte . "
			AND month(debtortrans.origtrandate) = " . $mescorte . "
			and year(debtortrans.origtrandate) = " . $aniocorte . "
			AND (debtortrans.type = 21 or debtortrans.type = 11)
			AND debtortrans.tagref = '" . $unidadnegocio . "'
			AND debtortrans.debtorno = debtorsmaster.debtorno
			AND debtortrans.ovamount <> 0
			AND debtortrans.alloc = 0 ".$SQLUsuario;
		if ($_SESSION['UserID'] == "admin") {
			//echo "<br><pre>Nota Informativa Notas de Cargo o Notas de Credito: ".$SQL."<br>";
		}
		$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
		$TransResult = DB_query ( $SQL, $db, $ErrMsg );
	?>
	<div class="container-fluid" style="display: none;">
		<div class="panel panel-default">
			<!-- Default panel contents -->
			<div class="panel-heading" align="center"> Nota Informativa Notas de Cargo o Notas de Credito</div>
			<!--<div class="panel-body">
			</div>-->
			<!-- Table -->
			<table class="table table-hover">
				<tr>
					<th style='text-align:center'><b>Cambiar Usuario</b></th>
					<th style='text-align:center'><b>Tipo</b></th>
					<th style='text-align:center'><b>Folio</b></th>
					<th style='text-align:center'><b>Cliente</b></th>
					<th style='text-align:center'><b>Fecha</b></th>
					<th style='text-align:center'><b>Detalle</b></th>
					<th style='text-align:center'><b>Monto</b></th>
					<th style='text-align:center'><b>IVA</b></th>
					<th style='text-align:center'><b>Total</b></th>
				</tr>
				<?php
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
						echo "<tr>";
						$ovamount = abs ( $myrow ['ovamount'] );
						$ovgst = abs ( $myrow ['ovgst'] );
						$alloc = abs ( $myrow ['alloc'] );
						$anticipo = ($ovamount + $ovgst) - $alloc;
						$subtotal = $alloc;
						$total = $subtotal + $anticipo;
						
						echo "<td style='text-align:center'><input type='checkbox' value='".$myrow ['type']."_".$myrow ['transno']."' name='FoliosCambiar[]' /></td>";
						echo "<td style='text-align:center'>" . $myrow ['typename'] . "</td>";
						echo "<td style='text-align:center'>" . $myrow ['folio'] . "</td>";
						echo "<td style='text-align:center'>" . $myrow ['name'] . "</td>";
						echo "<td style='text-align:center' nowrap>" . substr ( $myrow ['trandate'], 0, 10 ) . "</td>";
						echo "<td style='text-align:center'>" . $myrow ['reference'] . "</td>";
						echo "<td class='number'>" . number_format ( $ovamount, 2 ) . "</td>";
						echo "<td class='number'>" . number_format ( $ovgst, 2 ) . "</td>";
						// echo "<td style='font-size:8pt;' class='number'>" . number_format($subtotal,2) . "</td>";
						// echo "<td style='font-size:8pt;' class='number'>" . number_format($anticipo,2) . "</td>";
						echo "<td class='number'>" . number_format ( $total, 2 ) . "</td>";
						echo "</tr>";
						
						$xtotalamount = $xtotalamount + $ovamount;
						$xtotaliva = $xtotaliva + $ovgst;
						$xtotalanticipos = $xtotalanticipos + $anticipo;
						$xtotalsubtotales = $xtotalsubtotales + $subtotal;
						$xtotaltotales = $xtotaltotales + $total;
					}
					
					echo "<tr>";
					echo "<td colspan=6 style='text-align:right;'><b>" . _ ( 'TOTALES' ) . ' ' . strtoupper ( $xtypenameanterior ) . ": &nbsp;</b></td>";
					echo "<td class='number'><b>" . number_format ( $xtotalamount, 2 ) . "</b></td>";
					echo "<td class='number'><b>" . number_format ( $xtotaliva, 2 ) . "</b></td>";
					// echo "<td class='number'><b>" . number_format($xtotalsubtotales,2) . "</b></td>";
					// echo "<td class='number'><b>" . number_format($xtotalanticipos,2) . "</b></td>";
					echo "<td class='number'><b>" . number_format ( $xtotaltotales, 2 ) . "</b></td>";
					echo "</tr>";
				?>
			</table>
		</div>
	</div>
	<?php
		/**
		 * **************************************************************************
		 * MUESTRA LOS ANTICIPOS DEL DIA
		 * Se requiere que las aplicaciones de anticipos del d�a se reporten en las notas informativas del corte de caja, porque por el momento no aparecen en ninguna parte.
		 */
		$msjDocumentosSinAplicar = '';
		$SQLUsuario = "";
		if ($_POST['usuario'] != 'all') {
			$SQLUsuario = " AND debtortrans.userid = '".$_POST['usuario']."' ";
		}
		$fechaCorte = $aniocorte . "-" . $mescorte . "-" . $diacorte;
		// Me falta poner el filtro de unidad de negocio
		$SQL = " SELECT 
				debtortrans.debtorno,
				debtorsmaster.name, 
				debtortrans.folio as folioDoc, 
				custallocns.amt,
				debtor.folio,
				debtortrans.type,
				debtortrans.transno,
				systypescat.typename, 
				DATE_FORMAT(debtortrans.trandate, '%Y-%m-%d') as trandate,
				debtortrans.invtext, 
				debtortrans.reference
				FROM custallocns JOIN debtortrans ON custallocns.transid_allocfrom = debtortrans.id
				JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				JOIN systypescat ON systypescat.typeid = debtortrans.type
	      		LEFT JOIN debtortrans debtor ON debtor.id= custallocns.transid_allocto
				AND systypescat.typeid = debtortrans.type
				WHERE debtortrans.type in (80,13,11)
				AND custallocns.datealloc = '" . $fechaCorte . "'
				AND debtortrans.tagref = '". $unidadnegocio ."' $SQLUsuario";
		// GROUP BY debtortrans.id, se quita agrupacion, para mostrar desgloce
		if ($_SESSION['UserID'] == "admin") {
			//echo "<br><pre>Anticipo Clientes, Notas de Credito Directa, Nota de Credito: ".$SQL."<br>";
		}
		$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
		
		$TransResult = DB_query ( $SQL, $db, $ErrMsg );

	?>
	<div class="container-fluid" style="display: none;">
		<div class="panel panel-default">
			<!-- Default panel contents -->
			<div class="panel-heading" align="center"> Anticipo Clientes, Notas de Credito Directa, Nota de Credito</div>
			<!--<div class="panel-body">
			</div>-->
			<!-- Table -->
			<table class="table table-hover">
				<tr>
					<th style='text-align:center'><b>Cambiar Usuario</b></th>
					<th style='text-align:center'><b>Anticipo</b></th>
					<th style='text-align:center'><b>Folio</b></th>
					<th style='text-align:center'><b>Codigo</b></th>
					<th style='text-align:center'><b>Cliente</b></th>
					<th style='text-align:center'><b>Fecha</b></th>
					<th style='text-align:center'><b>Detalle</b></th>
					<th style='text-align:center'><b>Factura</b></th>
					<th style='text-align:center'><b>Monto</b></th>
				</tr>
				<?php
					
					$total = 0;
					while ( $myrow = DB_fetch_array ( $TransResult ) ) {
						if (trim($myrow['folio']) == '') {
							$msjDocumentosSinAplicar .= '<p class="bg-danger">Folio: '.$myrow['folioDoc'].' '.$myrow ['typename'].' no se encuentra con Aplicacion</p>';
						}
						echo "<tr>";
						echo "<td style='text-align:center'><input type='checkbox' value='".$myrow ['type']."_".$myrow ['transno']."' name='FoliosCambiar[]' /></td>";
						echo "<td style='text-align:center'>" . $myrow ['typename'] . "</td>";
						echo "<td style='text-align:center'>" . $myrow ['folioDoc'] . "</td>";
						echo "<td style='text-align:center'>" . $myrow ['debtorno'] . "</td>";
						echo "<td style='text-align:center'>" . $myrow ['name'] . "</td>";
						echo "<td style='text-align:center'>" . $myrow ['trandate'] . "</td>";
						echo "<td style='text-align:center'>" . $myrow ['reference'] . "</td>";
						echo "<td style='text-align:center'>" . $myrow ['folio'] . "</td>";
						echo "<td class='number'>" . number_format ( $myrow ['amt'], 2 ) . "</td>";
						echo "</tr>";
						
						$total = $total + $myrow ['amt'];
					}
					
					echo "<tr>";
					echo "<td colspan=8 style='text-align:right;'><b>" . _ ( 'TOTALES' ) . ": &nbsp;</b></td>";
					echo "<td class='number'><b>" . number_format ( $total, 2 ) . "</b></td>";
					echo "</tr>";
				?>
			</table>
		</div>
	</div>

	<?php
		/**
		 * **************************************************************************
		 * MUESTRA LAS DEVOLUCIONES DE LOS ANTICIPOS
		 */
		$SQLUsuario = "";
		if ($_POST['usuario'] != 'all') {
			$SQLUsuario = " and debtortransmovs.userid = '".$_POST['usuario']."' ";
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
						debtortrans.type,
						debtortrans.transno,
						debtortrans.reference
						FROM debtortransmovs
						LEFT JOIN systypescat ON systypescat.typeid = debtortransmovs.type
						LEFT JOIN gltrans ON gltrans.type = 4 and gltrans.typeno = debtortransmovs.transno
						LEFT JOIN chartmaster ON chartmaster.accountcode = gltrans.account
						LEFT JOIN debtorsmaster ON debtorsmaster.debtorno = debtortransmovs.debtorno
						LEFT JOIN debtortrans ON debtortrans.type = debtortransmovs.type and debtortrans.transno = debtortransmovs.transno
						WHERE 
						DATE_FORMAT(debtortransmovs.trandate, '%Y-%m-%d') = '".$fechaCorte."'
						".$SQLUsuario."
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

	?>

	<div class="container-fluid" style="display: none;">
		<div class="panel panel-default">
			<!-- Default panel contents -->
			<div class="panel-heading" align="center"> Notas de Devolucion de Anticipos</div>
			<!--<div class="panel-body">
			</div>-->
			<!-- Table -->
			<table class="table table-hover">
				<tr>
					<th style='text-align:center'><b>Cambiar Usuario</b></th>
					<th style='text-align:center'><b>Nota</b></th>
					<th style='text-align:center'><b>Folio</b></th>
					<th style='text-align:center'><b>Codigo</b></th>
					<th style='text-align:center'><b>Cliente</b></th>
					<th style='text-align:center'><b>Fecha</b></th>
					<th style='text-align:center'><b>Detalle</b></th>
					<!--th style='text-align:center'><b>Factura</b></th-->
					<th style='text-align:center'><b>Monto</b></th>
				</tr>
				<?php
					$total = 0;
					while ( $myrow = DB_fetch_array ( $TransResult ) ) {
						echo "<tr>";
						echo "<td style='text-align:center'><input type='checkbox' value='".$myrow ['type']."_".$myrow ['transno']."' name='FoliosCambiar[]' /></td>";
						echo "<td style='text-align:center'>" . $myrow ['typename'] . "</td>";
						echo "<td style='text-align:center'>" . $myrow ['folio'] . "</td>";
						echo "<td style='text-align:center'>" . $myrow ['debtorno'] . "</td>";
						echo "<td style='text-align:center'>" . $myrow ['name'] . "</td>";
						echo "<td style='text-align:center'>" . $myrow ['trandate'] . "</td>";
						echo "<td style='text-align:center'>" . $myrow ['reference'] . "</td>";
						//echo "<td style='text-align:center'>" . $myrow ['folio'] . "</td>";
						echo "<td class='number'>" . number_format ( $myrow ['amount'], 2 ) . "</td>";
						echo "</tr>";

						$total = $total + $myrow ['amount'];
					}

					echo "<tr>";
					echo "<td colspan=7 style='text-align:right;'><b>" . _ ( 'TOTALES' ) . ": &nbsp;</b></td>";
					echo "<td class='number'><b>" . number_format ( $total, 2 ) . "</b></td>";
					echo "</tr>";
				?>
			</table>
		</div>
	</div>

	<!--Panel de Cambios de Usuario-->
	<div class="container-fluid" style="display: none;">
		<div class="panel panel-default">
			<!-- Default panel contents -->
			<div class="panel-heading" align="center">Cambiar Usuario</div>
			<div class="panel-body">
				<div class="col-md-8">
					<div class="input-group">
						<span class="input-group-addon" id="basic-addon1">Vendedor:</span>
						<!--input type="text" class="form-control" placeholder="Username" aria-describedby="basic-addon1"-->
						<?php
							$SQL = "";
							if (empty($unidadnegocio)) {
								$SQL = "SELECT userid, realname FROM www_users WHERE active = 1 ORDER BY realname asc";
							}else{
								$SQL = "SELECT www_users.userid, www_users.realname 
										FROM www_users 
										LEFT JOIN sec_unegsxuser ON sec_unegsxuser.userid = www_users.userid
										WHERE www_users.active = 1 and sec_unegsxuser.tagref = '".$unidadnegocio."' 
										GROUP BY www_users.userid
										ORDER BY www_users.realname asc";
							}
							$ErrMsg = _ ( 'No se obtuvo informacion de los usuarios' );
							$TransResult = DB_query ( $SQL, $db, $ErrMsg );
						?>
						<select class="form-control" name='cmbCambiarUsuario' align="center">
							<option selected value='all'>Todos...</option>
							<?php
								while ( $myrow = DB_fetch_array ( $TransResult ) ) {
									if ($myrow ['userid'] == $_POST['cmbCambiarUsuario']) {
										echo "<option selected value='" . $myrow ['userid'] . "'>" . $myrow ['realname'] . "</option>";
									} else {
										echo "<option value='" . $myrow ['userid'] . "'>" . $myrow ['realname'] . "</option>";
									}
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-4">
					<input class='btn btn-primary' type='submit' name='btnCambiarUsuario' value='CAMBIAR USUARIO' />
				</div>
			</div>
		</div>
	</div>

	<?php
					
		$diferencia = $totaltotales1 - $Ttotaltotales2;
		$diferencia = abs ( $totaltemp1 + $totaltemp2 + $totaltemp3 ) - $Ttotaltotales2;
		if (trim($msjDocumentosSinAplicar) != '') {
			// Documentos sin Aplicar
			echo $msjDocumentosSinAplicar;
		} else  if ((abs ( ($totaltemp1 + $totaltemp2 + $totaltemp3) - $Ttotaltotales2 ) < 0.9)) {
			echo "<div align='center'>";
			// Procesar
			echo '<component-button type="submit" id="Aceptarprepoliza" name="Aceptarprepoliza"  class="glyphicon glyphicon-ok" value="Procesar"></component-button>';
			echo '<input type="hidden" id="txtIdsFacturas" name="txtIdsFacturas" value="'.$cadenaRegistrosGen.'" />';
			// echo '<br>cadenaRegistrosGen: '.$cadenaRegistrosGen;
			echo "</div>";
		} else {
			//prnMsg ( _ ( 'No puedes realizar el corte de caja debido a que existe diferencia entre Total de Ingresos y Detalle Aplicacion de Ingresos: ' . number_format ( $diferencia, 2 ) ), 'warn' );
			echo "<p class='bg-danger' style='background: #960404; color: #FFF; padding: 10px;'>No puedes realizar el corte de caja debido a que existe diferencia entre Total de Ingresos y Detalle Aplicacion de Ingresos: ".number_format ( $diferencia, 2 )."</p>";
		}

		/*
		 * FIN MUESTRA LAS NOTAS DE CREDITO O NOTAS DE CARGO CON ALLOC = 0;
		 */

		/*INICIO
		 Consulto la tabla de usrdetallecortecaja para obtener los movimientos de la prepoliza.
		*/
		unset ( $_SESSION ['MOVS'] );
		unset ( $_SESSION ['iMOVS'] );
		
		$_SESSION ['MOVS'] = array ();
		$_SESSION ['iMOVS'] = 0;
		
		$SQLUsuario = "";
		if ($_POST['usuario'] != 'all') {
			$SQLUsuario = " and dc.userid = '".$_POST['usuario']."' ";
		}
		$SQL = "SELECT u_detallecortecaja,
			referencia,
			cuentapuente,
			cuentacheques,
			monto,
			fechacorte,
			u_status,
			cm1.accountname as accountname1,
			cm2.accountname as accountname2,
			dc.fechadeposito,
			user.userid,
			user.realname
			FROM usrdetallecortecaja as dc, chartmaster as cm1, chartmaster as cm2, www_users as user
			WHERE dc.u_cortecaja = " . $u_cortecaja . "
			and dc.cuentapuente = cm1.accountcode
			and dc.cuentacheques = cm2.accountcode
			".$SQLUsuario."
			and user.userid = dc.userid";
		$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
		$TransResult = DB_query ( $SQL, $db, $ErrMsg );
		while ( $myrow = DB_fetch_array ( $TransResult ) ) {
			$_SESSION ['MOVS'] [$_SESSION ['iMOVS']] = new Movimiento ( $myrow ['cuentapuente'] . "-" . $myrow ['accountname1'], $myrow ['monto'], $myrow ['cuentacheques'] . "-" . $myrow ['accountname2'], $myrow ['referencia'], $myrow ['u_detallecortecaja'], $_SESSION ['iMOVS'], $myrow ['fechadeposito'], $myrow['userid'], $myrow['realname'] );
			$_SESSION ['iMOVS'] = $_SESSION ['iMOVS'] + 1;
		}
		/*
		 * FIN Consulto la tabla de usrdetallecortecaja para obtener los movimientos de la prepoliza.
		 */
}

if (isset ( $_POST ['Prepoliza'] )) {
	if (isset ( $_SESSION ['MOVS'] )) {

		$DSQL = "DELETE FROM usrdetallecortecaja
			WHERE u_cortecaja = '" . $u_cortecaja . "' and userid = '" . $_POST['usuario'] . "'";

		//echo "<br> corte ".$DSQL;
		$DbgMsg = _ ( 'El SQL fallo al eliminar los registros:' );
		$ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable' );
		$result = DB_query ( $DSQL, $db, $ErrMsg, $DbgMsg, true );
		// PARA SOBRANTE
		$SQL = "SELECT gltempcashpayment,gltempcheckpayment,gltempccpayment,gltemptransferpayment,gltempcheckpostpayment,gllink_deudoresdiversos,gllink_faltantes
		FROM companies";
		//echo $SQL;
		$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
		$TransResult = DB_query ( $SQL, $db, $ErrMsg );
		
		$cadena = "";
		$CuentaEfectivo = "";
		$CuentaSobrante = "";
		while ( $myrow = DB_fetch_array ( $TransResult ) ) {
			$cadena = "'" . $myrow ['gltempcashpayment'] . "','" . $myrow ['gltempcheckpayment'] . "','" . $myrow ['gltempccpayment'] . "','" . $myrow ['gltemptransferpayment'] . "','" . $myrow ['gltempcheckpostpayment'] . "'";
			$CuentaEfectivo = $myrow ['gltempcashpayment'];
			$CuentaSobrante =$_POST['fromSobrante'];
		}

		
		foreach ( $_SESSION ['MOVS'] as $Movs ) {
			$realizarOperacion = 1;
			//obtener si ya esta ese registo
			$ISQL = "SELECT * 
					FROM usrdetallecortecaja 
					WHERE referencia = '".$Movs->Reference."' 
					and cuentapuente = '".substr ( $Movs->FromAccount, 0, strpos ( $Movs->FromAccount, "-" ) )."' 
					and cuentacheques = '".substr ( $Movs->ToAccount, 0, strpos ( $Movs->ToAccount, "-" ) )."' 
					and u_cortecaja = '".$u_cortecaja."'
					and ROUND(monto,2) = '".number_format($Movs->Amount,2,'.','')."'
					and usrdetallecortecaja.userid = '".$Movs->vendedorid."'
					and usrdetallecortecaja.fechadeposito = '".$Movs->Fechadeposito."'";
			$DbgMsg = _ ( 'El SQL fallo al obtener datos de prepoliza:' );
			$ErrMsg = _ ( 'No se pudo obteenr la informacion' );
			$result = DB_query ( $ISQL, $db, $ErrMsg, $DbgMsg, true );
			if (DB_num_rows ( $result ) > 0) {
				$realizarOperacion = 0;
			}

			if ($realizarOperacion == 1) {
				$ISQL = "INSERT INTO usrdetallecortecaja (referencia,
						cuentapuente,
						cuentacheques,
						monto,
						fechacorte,
						u_status,
						u_cortecaja,fechadeposito, userid)
					VALUES ('" . $Movs->Reference . "',
						'" . substr ( $Movs->FromAccount, 0, strpos ( $Movs->FromAccount, "-" ) ) . "',
						'" . substr ( $Movs->ToAccount, 0, strpos ( $Movs->ToAccount, "-" ) ) . "',
						" . $Movs->Amount . ",
						'" . substr ( $fechacorte, 0, 10 ) . "',
						0,
						" . $u_cortecaja . ",'" . $Movs->Fechadeposito . " " . $horaPrepoliza . "',
						'" . $Movs->vendedorid . "')";
				$DbgMsg = _ ( 'El SQL fallo al insertar la transaccion:' );
				$ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable' );
				$result = DB_query ( $ISQL, $db, $ErrMsg, $DbgMsg, true );

				//echo "<pre/>SQL PRE 3545 ".$ISQL;
				//Guardar datos de las prepolizas de usuarios
				$ISQL = "INSERT INTO usrdetallecortecaja_prepoliza (referencia,
						cuentapuente,
						cuentacheques,
						monto,
						fechacorte,
						u_status,
						u_cortecaja,fechadeposito, userid)
					VALUES ('" . $Movs->Reference . "',
						'" . substr ( $Movs->FromAccount, 0, strpos ( $Movs->FromAccount, "-" ) ) . "',
						'" . substr ( $Movs->ToAccount, 0, strpos ( $Movs->ToAccount, "-" ) ) . "',
						" . $Movs->Amount . ",
						'" . substr ( $fechacorte, 0, 10 ) . "',
						0,
						" . $u_cortecaja . ",'" . $Movs->Fechadeposito . " " . $horaPrepoliza . "',
						'" . $Movs->vendedorid . "')";
				$DbgMsg = _ ( 'El SQL fallo al insertar la transaccion:' );
				$ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable' );
				$result = DB_query ( $ISQL, $db, $ErrMsg, $DbgMsg, true );
				$vendedorid = $Movs->vendedorid;
				//echo "<pre/>SQL PRE 3566".$ISQL;
			}
		}
if($_POST['montosobrante']>0){ // se registra el sobrante a la cuenta de Deudores Diversos

	$ISQL = "INSERT INTO usrdetallecortecaja_prepoliza (referencia,
						cuentapuente,
						cuentacheques,
						monto,
						fechacorte,
						u_status,
						u_cortecaja,fechadeposito, userid)
					VALUES ('" . $_POST['referenciasobrante'] . "',
						'".$_POST['toSobrante']."',
						'".$_POST['fromSombrante']."',
						-" . $_POST['montosobrante'] . ",
						'" . substr ( $fechacorte, 0, 10 ) . "',
						0,
						'" .$u_cortecaja . "','" . substr ( $fechacorte, 0, 10 )  . " " . $horaPrepoliza . "',
						'".$vendedorid."')";
				$DbgMsg = _ ( 'El SQL fallo al insertar la transaccion:' );
				$ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable' );
				$result = DB_query ( $ISQL, $db, $ErrMsg, $DbgMsg, true );
			//	echo "<pre/>SQL PRE 3589 ".$ISQL;

	$ISQL = "INSERT INTO usrdetallecortecaja(referencia,
						cuentapuente,
						cuentacheques,
						monto,
						fechacorte,
						u_status,
						u_cortecaja,fechadeposito, userid)
					VALUES ('" . $_POST['referenciasobrante'] . "',
						'".$_POST['toSobrante']."',
						'".$_POST['fromSobrante']."',
						-" . $_POST['montosobrante'] . ",
						'" . substr ( $fechacorte, 0, 10 ) . "',
						0,
						'" . $u_cortecaja. "','" . substr ( $fechacorte, 0, 10 )  . " " . $horaPrepoliza . "',
						'".$vendedorid."')";
				$DbgMsg = _ ( 'El SQL fallo al insertar la transaccion:' );
				$ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable' );
				$result = DB_query ( $ISQL, $db, $ErrMsg, $DbgMsg, true );
			//	echo "<pre/>SQL PRE 3609".$ISQL;
}
		

		
		$MSQL = "UPDATE usrcortecaja
			SET u_status = 1,
			userid = '" . $_SESSION ['UserID'] . "'
			WHERE u_cortecaja = " . $u_cortecaja;
		if (! $Result = DB_query ( $MSQL, $db )) {
			$ErrMsg = _ ( 'ERROR CRITICO' ) . '! ' . _ ( 'ANOTE ESTE ERROR Y BUSQUE AYUDA' ) . ': ' . _ ( 'El registro de transaccion de cliente no se pudo insertar' );
			$DbgMsg = _ ( 'El siguiente SQL fue utilizado' );
		}
		// echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/CustomerReceiptCorteCaja.php'>";
		echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir Corte Caja' ) . '" alt="">' . ' ' . '<a href="' . $rootpath . '/PrintCorteCajaV2_Detalles.php?fechacorte=' . substr ( $fechacorte, 0, 10 ) . '&unidadnegocio=' . $unidadnegocio . '&u_cortecaja=' . $u_cortecaja . '&usuario=' . $_POST['usuario'] . '" target="_blank">' . _ ( 'Imprimir Corte Caja PDF ' ) . '</a></p>';
		if(in_array($_SESSION['DatabaseName'], $JIBE)){
			echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir Resumen Corte Caja' ) . '" alt="">' . ' ' . '<a href="' . $rootpath . '/PrintCorteCajaV2_Detalles.php?fechacorte=' . substr ( $fechacorte, 0, 10 ) . '&unidadnegocio=' . $unidadnegocio . '&u_cortecaja=' . $u_cortecaja . '&usuario=' . $_POST['usuario'] . '&resumen=1" target="_blank">' . _ ( 'Imprimir Resumen Corte Caja PDF ' ) . '</a></p>';
		}
	}
}

echo '</form>';
echo "<br><br><br>";
?>

<?php
include 'includes/footer_Index.inc';
?>
	