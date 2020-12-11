<?php
/**
 * Generación del recibo desde la caja
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 13/08/2018
 * Fecha Modificación: 13/08/2018
 * Vista para la generación del recibo de pago desde la caja
 */

$funcion = 1773;
$PageSecurity = 3;
include "includes/SecurityUrl.php";
include ('includes/session.inc');
$title = traeNombreFuncion($funcion, $db);
$style = "";
$style2 = "";
$styleDisplay = "";
$pv1 = "";

if ($_GET['pv'] == 'pv1' or $_POST['txtPV'] == "pv1") {
	//quitar datos si se visualiza en el punto de venta, y centrar informacion
	$pv1 = "pv1";
	$style = "style='display: none;'";
	$style2 = "align='center'";
	$styleDisplay = "display: none;";
	$_GET['modal'] = true;
}else{
	// mostrar cuando no es en el punto de venta
	// include ('includes/header.inc');
}
include ('includes/header.inc');

include ('includes/SQL_CommonFunctions.inc');
include ('includes/SecurityFunctions.inc');
$msg = '';

include ('Numbers/Words.php');
include ('jasper/JasperTemplate.php');
include ('includes/SendInvoicingV6_0.php');
include ('XSAInvoicing2.inc');

$_SESSION["fechaGlobalArchivo"] = date('Y-m-d',strtotime($_POST ['txtFecha']));
$fechapoliza = $_SESSION["fechaGlobalArchivo"];
$permisoEdicionFecha = Havepermission($_SESSION ['UserID'], 218, $db); // Poder editar fecha
$SendInvoiceByMailFile = "SendInvoiceByMail.php";
?>
    <script type="text/javascript" src="css/printPageJQuery/jquery-1.9.1.min.js"></script>


<script>
	jQuery(document).ready(function($){
		(function( $ ){

  
		$.fn.printPage = function(options) {
		// EXTEND options for this button
		var pluginOptions = {
			attr : "href",
			url : false,
			message: "Espere por favor, estamos creando su recibo de pago" 
		};
		$.extend(pluginOptions, options);

		this.on("click", function(){  loadPrintDocument(this, pluginOptions); return false;  });
		
		/**
		 * Load & show message box, call iframe
		 * @param {jQuery} el - The button calling the plugin
		 * @param {Object} pluginOptions - options for this print button
		 */   
		function loadPrintDocument(el, pluginOptions){
			$("body").append(components.messageBox(pluginOptions.message));
			$("#printMessageBox").css("opacity", 0);
			$("#printMessageBox").animate({opacity:1}, 300, function() { addIframeToPage(el, pluginOptions); });
		}
		/**
		 * Inject iframe into document and attempt to hide, it, can't use display:none
		 * You can't print if the element is not dsplayed
		 * @param {jQuery} el - The button calling the plugin
		 * @param {Object} pluginOptions - options for this print button
		 */
		function addIframeToPage(el, pluginOptions){

			var url = (pluginOptions.url) ? pluginOptions.url : $(el).attr(pluginOptions.attr);

			if(!$('#printPage')[0]){
				$("body").append(components.iframe(url));
				$('#printPage').on("load",function() {  printit();  })
			}else{
				$('#printPage').attr("src", url);
			}
		}
		/*
		* Call the print browser functionnality, focus is needed for IE
		*/
		function printit(){
			frames["printPage"].focus();
			frames["printPage"].print();
			unloadMessage();
		}
		/*
		* Hide & Delete the message box with a small delay
		*/
		function unloadMessage(){
			$("#printMessageBox").delay(1000).animate({opacity:0}, 700, function(){
			$(this).remove();
			});
		}
		/*
		* Build html compononents for thois plugin
		*/
		var components = {
			iframe: function(url){
			return '<iframe id="printPage" name="printPage" src='+url+' style="position:absolute;top:0px; left:0px;width:0px; height:0px;border:0px;overfow:none; z-index:-1"></iframe>';
			},
			messageBox: function(message){
			return "<div id='printMessageBox' style='\
				position:fixed;\
				top:50%; left:50%;\
				text-align:center;\
				margin: -60px 0 0 -155px;\
				width:310px; height:120px; font-size:16px; padding:10px; color:#222; font-family:helvetica, arial;\
				opacity:0;\
				background:#fff url(data:image/gif;base64,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) center 40px no-repeat;\
				border: 6px solid #555;\
				border-radius:8px; -webkit-border-radius:8px; -moz-border-radius:8px;\
				box-shadow:0px 0px 10px #888; -webkit-box-shadow:0px 0px 10px #888; -moz-box-shadow:0px 0px 10px #888'>\
				"+message+"</div>";
			}
		}
		};
		})( jQuery );

		if (document.querySelector(".btnPrint")) {
		
			jQuery(".btnPrint").printPage();
		}
	});
</script>


<?php

if (isset ( $_POST ['txtFecha'] )) {
    $fecha = $_POST ['txtFecha'];
    $fecha2 = date('Y-m-d',strtotime($_POST ['txtFecha']));
} else {
    $fecha2 = date ( 'Y' ) . "/" . date ( 'm' ) . "/" . date ( 'd' );
    $fecha = date ( 'd' ) . "-" . date ( 'm' ) . "-" . date ( 'Y' );
	// $fechadesde = date('m') . "/" . "23" . "/" . date('Y');
}

// para el llamado al webservice
// require_once('lib/nusoap.php');
//ini_set('display_errors', 1);
//ini_set('log_errors', 1); 
function nombremescorto($idmes) {
	$nombremescorto = "";
	switch ($idmes) {
		case 1 :
			$nombremescorto = "ENE";
			break;
		case 2 :
			$nombremescorto = "FEB";
			break;
		case 3 :
			$nombremescorto = "MAR";
			break;
		case 4 :
			$nombremescorto = "ABR";
			break;
		case 5 :
			$nombremescorto = "MAY";
			break;
		case 6 :
			$nombremescorto = "JUN";
			break;
		case 7 :
			$nombremescorto = "JUL";
			break;
		case 8 :
			$nombremescorto = "AGO";
			break;
		case 9 :
			$nombremescorto = "SEP";
			break;
		case 10 :
			$nombremescorto = "OCT";
			break;
		case 11 :
			$nombremescorto = "NOV";
			break;
		case 12 :
			$nombremescorto = "DIC";
			break;
	}
	return $nombremescorto;
}

if ($_SERVER ['SERVER_NAME'] == "erp.servillantas.com") {
	$ambiente = "produccion";
} else {
	$ambiente = "desarrollo";
}
function insertagltrans($db, $type, $typeno, $fechapoliza, $periodo, $account, $narrative, $tag, $amount, $posted,$userID,$rate, $ln_ue, $nu_folio_ue) {
	
	$ISQL = "INSERT INTO gltrans (type,
			typeno,
			trandate,
			periodno,
			account,
			narrative,
			tag,
			amount,
			posted,
			userid,
			rate,
			ln_ue,
			nu_folio_ue
			)
		VALUES (" . $type . ",
			" . $typeno . ",
			'" . $fechapoliza. "',
			" . $periodo . ",
			'" . $account . "',
			'" . $narrative . "',
			'" . $tag . "',
			" . $amount .",
			" . $posted . ",
			'" . $userID . "',
			" . $rate . ",
			'" . $ln_ue . "',
			'" . $nu_folio_ue . "')";
	
	$DbgMsg = _ ( 'El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:' );
	$ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable para la cuenta puente de caja' );
	$result = DB_query ( $ISQL, $db, $ErrMsg, $DbgMsg, true );
	$IDgltrans = DB_Last_Insert_ID ( $db, 'gltrans', 'counterindex' );
	return $IDgltrans;
}
function insertadebtortrans($db, $trasno, $tagref, $type, $debtorno, $branchcode, $prd, $reference, $tpe, $order_, $ovamount, $ovgst, $ovfreight, $rate, $invtext, $shipvia, $consignment, $alloc, $currcode, $codesat = null, $cuenta_banco = '', $nu_ue = '') {
	$ISQL = "INSERT INTO debtortrans (
			transno,
			tagref,
			type,
			debtorno,
			branchcode,
			trandate,
			prd,
			reference,
			tpe,
			order_,
			ovamount,
			ovgst,
			ovfreight,
			rate,
			invtext,
			shipvia,
			consignment,
			alloc,
			origtrandate,
			userid,
			currcode,
			codesat,
			cuenta_banco,
			nu_ue
			)
 		VALUES (
			" . $trasno . ",
			'" . $tagref . "',
			" . $type . ",
			'" . $debtorno . "',
			'" . $branchcode . "',
			'" . date('Y-m-d',strtotime($_POST ['txtFecha'])).' '.date('H:i:s'). "',
			" . $prd . ",
			'" . $reference . "',
			'" . $tpe . "',
			'" . $order_ . "',
			" . $ovamount . ",
			" . $ovgst . ", 
			" . $ovfreight . ",
			" . $rate . ",
			'" . $invtext . "',
			'" . $shipvia . "',
			'" . $consignment . "',
			" . $alloc . ",
			Now(),
			'" . $_SESSION ['UserID'] . "',
			'" . $currcode . "',
			'" . $codesat . "',
			'" . $cuenta_banco . "',
			'" . $nu_ue . "'
		)";
	
	$ErrMsg = _ ( 'ERROR CRITICO' ) . '! ' . _ ( 'ANOTA ESTE ERROR Y BUSCA SOPORTE TECNICO' ) . ': ' . _ ( 'El registro no pudo ser insertado en la tabla debtortrans debido a ' );
	$DbgMsg = _ ( 'La siguiente  sentencia SQL fue utilizada para la transaccion..' );
	$Result = DB_query ( $ISQL, $db, $ErrMsg, $DbgMsg, true );
	//echo '->.'-$ISQL;
	$DebtorTransID = DB_Last_Insert_ID ( $db, 'debtortrans', 'id' );
	
	return $DebtorTransID;
}

if (isset ( $_GET ['transnofac'] )) {
	$_POST ['transnofac'] = $_GET ['transnofac'];
}
if (isset ( $_GET ['debtorno'] )) {
	$_POST ['debtorno'] = $_GET ['debtorno'];
}
if (isset ( $_GET ['branchcode'] )) {
	$_POST ['branchcode'] = $_GET ['branchcode'];
}
if (isset ( $_GET ['tag'] )) {
	$_POST ['tag'] = $_GET ['tag'];
}
if (isset ( $_GET ['transnorecibo'] )) {
	$_POST ['transnorecibo'] = $_GET ['transnorecibo'];
}

if (isset ( $_GET ['typeinvoice'] )) {
	$_POST ['typeinvoice'] = $_GET ['typeinvoice'];
} else {
	if (! isset ( $_POST ['typeinvoice'] )) {
		$_POST ['typeinvoice'] = "110";
	}
}

if(isset($_GET['shippinglogid'])){
    $_POST['shippinglogid'] = $_GET['shippinglogid'];
}elseif(isset($_POST['shippinglogid'])){
    $_POST['shippinglogid'] = $_POST['shippinglogid'];
}

if(isset($_GET['shippingno'])){
    $_POST['shippingno'] = $_GET['shippingno'];
}elseif(isset($_POST['shippingno'])){
    $_POST['shippingno'] = $_POST['shippingno'];
}
if (! isset ( $_POST ['txtNoOficio'] )) {
	$noOficio = $_POST['txtNoOficio'];
}

$dataJsonMeses = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

$transnofac = $_POST ['transnofac'];
$debtorno = $_POST ['debtorno'];
$branchcode = $_POST ['branchcode'];
$tag = $_POST ['tag'];
$transnorecibo = $_POST ['transnorecibo'];
$typeinvoice = $_POST ['typeinvoice'];

/*
 * INICIO *CHECO QUE EXISTA UN REGISTRO POR FECHA Y POR TAG QUE LE CORRESPONDE A CADA USUARIO EN LA TABLA DE usrCortedeCaja, *SI NO EXISTE SE INSERTA.
 */
if (!isset($_SESSION['detalleCorteGeneral']) || $_SESSION['detalleCorteGeneral'] == 0) {
	$_SESSION['detalleCorteGeneral'] = 1;
	$TSQL = "SELECT T.tagref, T.tagdescription, T.areacode, tb_cat_unidades_ejecutoras.ue
	FROM tags T, sec_unegsxuser S, tb_cat_unidades_ejecutoras, tb_sec_users_ue
	WHERE T.tagref = S.tagref
	AND tb_cat_unidades_ejecutoras.ur = T.tagref
	AND tb_sec_users_ue.tagref = T.tagref AND tb_sec_users_ue.ue = tb_cat_unidades_ejecutoras.ue
	AND S.userid = '".$_SESSION['UserID']."'
	AND tb_sec_users_ue.userid = '".$_SESSION['UserID']."'";
	
	$ErrMsg = _ ( 'La consulta no arrojo resultados' );
	$TTransResult = DB_query ( $TSQL, $db, $ErrMsg );
	while ( $tmyrow = DB_fetch_array ( $TTransResult ) ) {
		$FSQL = "SELECT u_cortecaja, fechacorte, u_status, tag
		FROM usrcortecaja
		WHERE fechacorte = STR_TO_DATE('" . date ( 'm' ) . "/" . date ( 'd' ) . "/" . date ( 'Y' ) . "','%m/%d/%Y')
		and tag = '" . $tmyrow ['tagref'] . "'
		and ln_ue = '" . $tmyrow ['ue'] . "'";
		$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
		$TransResult = DB_query ( $FSQL, $db, $ErrMsg );
		if (DB_num_rows ( $TransResult ) == 0) {
			$ISQL = "INSERT INTO usrcortecaja (fechacorte, u_status, tag, ln_ue)
				VALUES (STR_TO_DATE('" . date ( 'm' ) . "/" . date ( 'd' ) . "/" . date ( 'Y' ) . "','%m/%d/%Y'),
					0, '" . $tmyrow ['tagref'] . "', '" . $tmyrow ['ue'] . "')";
			$DbgMsg = _ ( 'El SQL fallo al insertar la transaccion:' );
			$ErrMsg = _ ( 'No se pudo insertar la Transaccion Contable' );
			$result = DB_query ( $ISQL, $db, $ErrMsg, $DbgMsg, true );
		}
	}
}
/* FIN */

$SQL = "SELECT debtortrans.id,
	debtortrans.tagref,
	debtortrans.transno,
	debtortrans.type,
	debtortrans.debtorno,
	debtortrans.ovamount,
	debtortrans.ovgst, 
	debtortrans.alloc,
	debtortrans.invtext,
	debtortrans.folio,
	debtorsmaster.name,
	debtorsmaster.typeid,
	systypescat.typename,
	debtortrans.diffonexch,
	currencies.rate,
	debtortrans.currcode,
	debtortrans.order_ ,
	debtortrans.emails,
	debtortrans.codesat,
	debtortrans.nu_ue,
	tags.typeinvoice,
	((debtortrans.ovamount + debtortrans.ovgst) - debtortrans.alloc) as saldopendiente
	FROM debtortrans, debtorsmaster, systypescat, currencies,tags 
	WHERE debtortrans.transno = '" . $transnofac . "'
	and debtortrans.type =   '" . $typeinvoice . "'
	and debtortrans.debtorno = debtorsmaster.debtorno
	and debtortrans.tagref=tags.tagref
	and systypescat.typeid = debtortrans.type
	and debtorsmaster.currcode = currencies.currabrev";
$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
$TransResult = DB_query ( $SQL, $db, $ErrMsg );
// echo "<br>" . $SQL;
$codesat = "";
if ($myrow = DB_fetch_array ( $TransResult )) {
	$cliente = $myrow ['name'];
	$nombretipo = $myrow ['typename'];
	$ovamount = $myrow ['ovamount'];
	$ovgst = $myrow ['ovgst'];
	$alloc = $myrow ['alloc'];
	$invtext = $myrow ['invtext'];
	$folio = $myrow ['folio'];
	$id = $myrow ['id'];
	$emails = $myrow ['emails'];
	$diffonexch = $myrow ['diffonexch'];
	$currcode = $myrow ['currcode'];
	$OrderNo = $myrow ['order_'];
	$tipofacturacionxtag = $myrow ['typeinvoice'];
	$tipocliente = $myrow ['typeid'];
	$saldopendiente = $myrow ['saldopendiente'];
	$codesat = $myrow ['codesat'];
	$datoUE = $myrow['nu_ue'];
} else {
	$cliente = "&nbsp;";
	$nombretipo = "&nbsp;";
	$ovamount = "&nbsp;";
	$ovgst = "&nbsp;";
	$alloc = "&nbsp;";
	$invtext = "&nbsp;";
	$folio = "&nbsp;";
	$id = "&nbsp;";
	$diffonexch = "&nbsp;";
	$currcode = "&nbsp;";
	
	$tipocliente = '0';
	$saldopendiente = 0;

	prnMsg ( _ ( 'Realizar el recibo desde la función 602 - ' . traeNombreFuncion(602, $db) ), 'info' );
	// include ('includes/footer.inc');
	exit();
}

// echo "tipocliente: " . $tipocliente;
//echo "<br> total:".$hdntotal. " total ".$total;
//

if (isset ( $_POST ['procesar'] )) {
	//var_dump($_POST);
    //exit();
	$hdnsubtotal = $_POST ['hdnsubtotal'];
	$hdniva = $_POST ['hdniva'];
	$hdntotal = $_POST ['hdntotal'];

	$cuenta_banco = "";
	
	$txtpagoefectivo = str_replace ( ",", "", $_POST ['txtpagoefectivo'] );
	$txtpagocheque = str_replace ( ",", "", $_POST ['txtpagocheque'] );
	$txtpagotdc = str_replace ( ",", "", $_POST ['txtpagotdc'] );
    $txtpagotdd = str_replace ( ",", "", $_POST ['txtpagotdd'] );
	$txtpagotransferencia = str_replace ( ",", "", $_POST ['txtpagotransferencia'] );
	$txtpagoanticipo = str_replace ( ",", "", $_POST ['txtpagoanticipo'] );
	$txtpagobancos = str_replace ( ",", "", $_POST ['txtpagobancos'] );
	$totalabono = $txtpagoefectivo + $txtpagocheque + $txtpagotdc + $txtpagotdd + $txtpagotransferencia+$txtpagoanticipo +$txtpagobancos;
	
	//echo "<br>saldos: ".$saldopendiente ." ".$totalabono;
	if (abs ( $saldopendiente - $totalabono ) > 0.01) {
		prnMsg ( _ ( 'No puedes realizar el pago debido a que el monto aplicado es mayor al saldo pendiente de la factura' ), 'warn' );
		if (Havepermission ( $_SESSION ['UserID'], 205, $db )) {
			echo "<div><p>-><a href='CustomerReceiptCancel.php'>" . _ ( 'Ir a Busqueda de pedidos' ) . "</a></p></div>";
		}
		include ('includes/footer.inc');
		exit ();
	}

	if ($txtpagotdc > 0) {
		$cuenta_banco = $_POST['cmbBancostdc'];
	}
	if ($txtpagotdd > 0) {
		$cuenta_banco = $_POST['cmbBancostdb'];
	}
	if ($txtpagotransferencia > 0) {
		$cuenta_banco = $_POST['cmbBancostrans'];
	}
	
	$total = $txtpagoefectivo + $txtpagocheque + $txtpagotdc + $txtpagotdd + $txtpagotransferencia+$txtpagoanticipo+$txtpagobancos;
	$referencia = "";

	//echo "<br><td> total:".$hdntotal. " total ".$total;

	//echo "<br> Dif: ".abs ( $hdntotal - $total );
	
	if (abs ( $hdntotal - $total ) <= 0.01) {
		$aplica = 1;
		/*+********/
		/******** */
		$BatchNo = 0;
		$infoFolios = array();
		$infoFolios2 = array();
		$infoFoliosAmpliacion = array();

		$totalOperacionReg = 0;
			
		if (($hdntotal > 0) || $hdntotal == 0) {
			$BatchNo = GetNextTransNo ( 12, $db );
        	$PeriodNo = GetPeriod ( date('d-m-Y',strtotime($_POST ['txtFecha'])), $db, $tag );

        	$hdntotal = number_format($hdntotal, 2, '.', '');

			// Poliza para Egreso
			// $transno = GetNextTransNo($type, $db);
			$sql = "SELECT realname FROM www_users WHERE userid = '".$_SESSION['UserID']."'";
			$resultUser = DB_query ( $sql, $db, $ErrMsg );
			$rsUser = DB_fetch_array($resultUser);
			$descriptionCajero = "";
			if (!empty($rsUser['realname'])) {
				$descriptionCajero = " - Cajero: ".$rsUser['realname'];
			}

			$account = $_POST ['pagoefectivo'];
			$separa = explode ( '-', $account );
			$account = $separa [0];
			$narrative = $_POST ['txtrefefectivo'];
			$amount = $txtpagoefectivo;
		
		
			$referencia = "Pase de cobro: ".$OrderNo." - Recibo de pago: ".$BatchNo.$descriptionCajero;
			// $datoUE = fnObtenerUnidadEjecutoraClave($db, $claveCreada);
			
			$cuentaEfectivo = $account;

			$sql = "SELECT salesorderdetails.stkcode, 
			salesorderdetails.unitprice,
			salesorderdetails.quantity,
			(salesorderdetails.unitprice * salesorderdetails.quantity) * salesorderdetails.discountpercent as totalDescuento,
			tb_cat_objeto_detalle.clave_presupuestal AS clavePresupuestal,
			tb_cat_objeto_detalle.cuenta_abono AS cuentaAbono,
			tb_cat_objeto_detalle.cuenta_cargo AS cuentaCargo,
			salesorderdetails.id_administracion_contratos,
			chartdetailsbudgetbytag.tagref as tagrefClave,
			tb_cat_unidades_ejecutoras.ue as ueClave,
			tb_administracion_contratos.id_contrato
			FROM salesorders
			JOIN salesorderdetails ON salesorderdetails.orderno = salesorders.orderno
			JOIN tb_cat_objeto_detalle ON tb_cat_objeto_detalle.stockid = salesorderdetails.stkcode
			LEFT JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = tb_cat_objeto_detalle.clave_presupuestal
			LEFT JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ln_aux1 = chartdetailsbudgetbytag.ln_aux1
			LEFT JOIN tb_administracion_contratos ON tb_administracion_contratos.id_administracion_contratos = salesorderdetails.id_administracion_contratos
			WHERE salesorders.orderno =  '".$OrderNo."'";
			$ErrMsg = _ ( 'No stkcode were returned by the SQL because' );
			$TransResult = DB_query ( $sql, $db, $ErrMsg );

			$numRegistros = 1;
			// echo "<br> antes tag: ".$tag." - datoUE: ".$datoUE;
			// procesamiento de la información obtenida
			while ($rs = DB_fetch_array($TransResult)) {
				$totalAmount = ($rs['unitprice'] * $rs['quantity']) - $rs['totalDescuento'];
				$claveCreada =$rs['clavePresupuestal'];

				// $totalAmount = number_format($totalAmount, 2, '.', '');
				
				$totalOperacionReg = $totalOperacionReg + $totalAmount;

				$folioPolizaUe = 0;
				$folioPolizaUe2 = 0;
				$folioPolizaUeAmpliacion = 0;

				$urPoliza = $tag;
				$uePoliza = $datoUE;
				if(!empty($rs['clavePresupuestal'])){
					$urPoliza = $rs['tagrefClave'];
					$uePoliza = $rs['ueClave'];
				}

				foreach ($infoFolios as $datosFolios) {
					// Recorrer para ver si exi
					// echo "<br>primer: tagref: ".$datosFolios['tagref']."- ue: ".$datosFolios['ue']." **** urPoliza: ".$urPoliza." - uePoliza: ".$uePoliza;
					if ($datosFolios['tagref'] == $urPoliza && $datosFolios['ue'] == $uePoliza) {
						// Si existe
						$type = $datosFolios['type'];
						$transno = $datosFolios['transno'];
						$folioPolizaUe = $datosFolios['folioPolizaUe'];
					}
				}

				if ($folioPolizaUe == 0) {
					// Si no existe folio sacar folio
					
					// Folio de la poliza por unidad ejecutora
					$folioPolizaUe = fnObtenerFolioUeGeneral($db, $urPoliza, $uePoliza, 12);
					// $folioPolizaUe2 = fnObtenerFolioUeGeneral($db, $urPoliza, $uePoliza, 12);

					$infoFolios[] = array(
						'tagref' => $urPoliza,
						'ue' => $uePoliza,
						'type' => 12,
						'transno' => $BatchNo,
						'folioPolizaUe' => $folioPolizaUe
					);
				}
				// echo "<br>infoFolios: ";
				// print_r($infoFolios);

				// Actualizar registro de contrato
				if (!empty($rs['id_administracion_contratos'])) {
					$SQL = "UPDATE tb_administracion_contratos 
					SET estatus = 'Pagado', 
					folio_recibo = '".$BatchNo."', 
					cajero = '".$_SESSION['UserID']."', 
					dt_fechadepago = NOW() 
					WHERE id_administracion_contratos = '".$rs['id_administracion_contratos']."'";
					$TransResult2 = DB_query ( $SQL, $db, $ErrMsg );

					if (!empty($rs['id_contrato'])) {
						// Validar si tiene más adeudos
						$SQL = "SELECT
						SUM(IF(tb_administracion_contratos.estatus = 'Pagado', 1, 0)) as pagado,
						SUM(IF(tb_administracion_contratos.estatus != 'Pagado', 1, 0)) as pendientes,
						tb_administracion_contratos.id_contrato
						FROM tb_administracion_contratos
						WHERE tb_administracion_contratos.id_contrato = '".$rs['id_contrato']."'
						GROUP BY tb_administracion_contratos.id_contrato
						HAVING pendientes != 0";
						$TransResult2 = DB_query ( $SQL, $db, $ErrMsg );
						if (DB_num_rows($TransResult2) == 0) {
							// Si no tiene pendientes actualizar estatus
							$SQL = "UPDATE tb_contratos 
							SET enum_status = 'Pagado'
							WHERE id_contrato = '".$rs['id_contrato']."'";
							$TransResult2 = DB_query ( $SQL, $db, $ErrMsg );
						}
					}
				}

				if(!empty($rs['clavePresupuestal'])){
					// Si existe clave presupuestal
					
					// Validar movimientos de ampliacion automatica
					$infoDatos = fnInfoPresupuestoIngreso($db, $rs['clavePresupuestal'], $PeriodNo, "", "", 1, 0, "", 12, $BatchNo, 312);

					// Disponible estimado del mes actual
					$disponibleEstimado = $infoDatos[0][$dataJsonMeses[(date('n') - 1)]];
					// echo "<br>disponibleEstimado: ".$disponibleEstimado;
					// echo "<br>totalAmount: ".$totalAmount;
					if ($totalAmount > $disponibleEstimado) {
						// Generar movimientos de ampliacion
						if ($disponibleEstimado < 0) {
							$disponibleEstimado = 0;
						}
						$difEstimado = $totalAmount - $disponibleEstimado;
						// echo "<br>difEstimado: ".$difEstimado;
						
						foreach ($infoFoliosAmpliacion as $datosFoliosAmpliacion) {
							// Recorrer para ver si exi
							// echo "<br>primer: tagref: ".$datosFoliosAmpliacion['tagref']."- ue: ".$datosFoliosAmpliacion['ue']." **** urPoliza: ".$urPoliza." - uePoliza: ".$uePoliza;
							if ($datosFoliosAmpliacion['tagref'] == $urPoliza && $datosFoliosAmpliacion['ue'] == $uePoliza) {
								// Si existe
								$type = $datosFoliosAmpliacion['type'];
								$transno = $datosFoliosAmpliacion['transno'];
								$folioPolizaUeAmpliacion = $datosFoliosAmpliacion['folioPolizaUe'];
							}
						}

						if ($folioPolizaUeAmpliacion == 0) {
							// Si no existe folio sacar folio
							
							// Folio de la poliza por unidad ejecutora
							$folioPolizaUeAmpliacion = fnObtenerFolioUeGeneral($db, $urPoliza, $uePoliza, 12);

							$infoFoliosAmpliacion[] = array(
								'tagref' => $urPoliza,
								'ue' => $uePoliza,
								'type' => 12,
								'transno' => $BatchNo,
								'folioPolizaUe' => $folioPolizaUeAmpliacion
							);
						}

						$accountAbono = 'INGRESO_EJECUTAR';
	        			$accountCargo = 'INGRESO_MODIFICADO';
						$res = GeneraMovimientoContablePresupuesto(12, $accountAbono, $accountCargo, $BatchNo, $PeriodNo, $difEstimado, $urPoliza, $fechapoliza, $claveCreada, $referencia, $db, false, '', '', $referencia, $uePoliza, 1, 0, $folioPolizaUeAmpliacion);

						$agregoLog = fnInsertPresupuestoLog($db, 12, $BatchNo, $urPoliza, $claveCreada, $PeriodNo, abs($difEstimado), 312, "", $referencia, 1, '', 0, $uePoliza); // Positivo
					}

					foreach ($infoFolios2 as $datosFolios2) {
						// Recorrer para ver si exi
						// echo "<br>primer: tagref: ".$datosFolios2['tagref']."- ue: ".$datosFolios2['ue']." **** tag: ".$tag." - datoUE: ".$datoUE;
						if ($datosFolios2['tagref'] == $urPoliza && $datosFolios2['ue'] == $uePoliza) {
							// Si existe
							$type = $datosFolios2['type'];
							$transno = $datosFolios2['transno'];
							$folioPolizaUe2 = $datosFolios2['folioPolizaUe'];
						}
					}

					if ($folioPolizaUe2 == 0) {
						// Si no existe folio sacar folio
						
						// Folio de la poliza por unidad ejecutora
						$folioPolizaUe2 = fnObtenerFolioUeGeneral($db, $urPoliza, $uePoliza, 12);

						$infoFolios2[] = array(
							'tagref' => $urPoliza,
							'ue' => $uePoliza,
							'type' => 12,
							'transno' => $BatchNo,
							'folioPolizaUe' => $folioPolizaUe2
						);
					}

					// echo "<br>infoFolios2: ";
					// print_r($infoFolios2);
					$accountAbono = 'INGRESO_DEVENGADO';
        			$accountCargo = 'INGRESO_EJECUTAR';
					$res = GeneraMovimientoContablePresupuesto(12, $accountAbono, $accountCargo, $BatchNo, $PeriodNo, $totalAmount, $urPoliza, $fechapoliza, $claveCreada, $referencia, $db, false, '', '', $referencia, $uePoliza, 1, 0, $folioPolizaUe);
					$accountAbono = 'INGRESO_RECAUDADO';
					$accountCargo = 'INGRESO_DEVENGADO';
					$res = GeneraMovimientoContablePresupuesto(12, $accountAbono, $accountCargo, $BatchNo, $PeriodNo, $totalAmount, $urPoliza, $fechapoliza, $claveCreada, $referencia, $db, false, '', '', $referencia, $uePoliza, 1, 0, $folioPolizaUe2);

					// Log presupuestal, del devengado
					$agregoLog = fnInsertPresupuestoLog($db, 12, $BatchNo, $urPoliza, $claveCreada, $PeriodNo, abs($totalAmount) * -1, 310, "", $referencia, 1, '', 0, $uePoliza); // Negativo
					$agregoLog = fnInsertPresupuestoLog($db, 12, $BatchNo, $urPoliza, $claveCreada, $PeriodNo, abs($totalAmount), 310, "", $referencia, 1, '', 0, $uePoliza); // Positivo
					// Log presupuestal, del recaudado
					$agregoLog = fnInsertPresupuestoLog($db, 12, $BatchNo, $urPoliza, $claveCreada, $PeriodNo, abs($totalAmount) * -1, 311, "", $referencia, 1, '', 0, $uePoliza); // Negativo
					
					$sql = "SELECT
						chartdetailsbudgetbytag.rtc,
						tb_matriz_conv_ingresos.stockact,
						tb_matriz_conv_ingresos.accountegreso
						FROM chartdetailsbudgetbytag 
						JOIN tb_matriz_conv_ingresos ON chartdetailsbudgetbytag.rtc = tb_matriz_conv_ingresos.categoryid
						WHERE chartdetailsbudgetbytag.accountcode = '".$rs['clavePresupuestal']."'";
					$result = DB_query($sql, $db);
					if ($myrow = DB_fetch_array ( $result )) {
						
						$x = insertagltrans ( $db, 12, $BatchNo, $fechapoliza, $PeriodNo,  $myrow ['stockact'], $referencia, $urPoliza, $totalAmount,1, $_SESSION['UserID'], 1,$uePoliza, $folioPolizaUe );
						$x = insertagltrans ( $db, 12, $BatchNo, $fechapoliza, $PeriodNo, $myrow ['accountegreso'], $referencia, $urPoliza, ($totalAmount * -1), 1, $_SESSION['UserID'], 1,$uePoliza, $folioPolizaUe);
						$x = insertagltrans ( $db, 12, $BatchNo, $fechapoliza,  $PeriodNo,  $cuentaEfectivo, $referencia, $urPoliza, $totalAmount,1, $_SESSION['UserID'], 1,$uePoliza, $folioPolizaUe2 );
						$x = insertagltrans ( $db, 12, $BatchNo, $fechapoliza, $PeriodNo,  $myrow ['stockact'], $referencia, $urPoliza, ($totalAmount * -1),1, $_SESSION['UserID'], 1,$uePoliza, $folioPolizaUe2 );
					}
				}else{
					
					$x = insertagltrans ( $db, 12, $BatchNo, $fechapoliza, $PeriodNo,  $rs ['cuentaCargo'], $referencia, $urPoliza, $totalAmount,1, $_SESSION['UserID'], 1,$uePoliza, $folioPolizaUe );
					$x = insertagltrans ( $db, 12, $BatchNo, $fechapoliza, $PeriodNo, $rs ['cuentaAbono'], $referencia, $urPoliza, ($totalAmount * -1),1, $_SESSION['UserID'], 1,$uePoliza, $folioPolizaUe );
					$x = insertagltrans ( $db, 12, $BatchNo, $fechapoliza, $PeriodNo,  $cuentaEfectivo, $referencia, $urPoliza, $totalAmount,1, $_SESSION['UserID'], 1,$uePoliza, $folioPolizaUe );
					$x = insertagltrans ( $db, 12, $BatchNo, $fechapoliza, $PeriodNo,  $rs ['cuentaCargo'], $referencia, $urPoliza, ($totalAmount * -1),1, $_SESSION['UserID'], 1,$uePoliza, $folioPolizaUe );
				}

				$numRegistros ++;
			}
		} else {
			prnMsg ( _ ( 'No es posible generar el recibo de pago, existen diferencias en las cantidades y/o ya ha sido pagado el pase de cobro' ), 'info' );
			exit();
		}
		
		// Registros por forma de pago
		if ($txtpagoefectivo > 0) {
			$idReg = fnInsertFormaPagoDeb($db, 12, $BatchNo, $txtpagoefectivo, "01");
		}
		if ($txtpagocheque > 0) {
			$idReg = fnInsertFormaPagoDeb($db, 12, $BatchNo, $txtpagocheque, "02");
		}
		if ($txtpagotdc > 0) {
			$idReg = fnInsertFormaPagoDeb($db, 12, $BatchNo, $txtpagotdc, "04");
		}
		if ($txtpagotdd > 0) {
			$idReg = fnInsertFormaPagoDeb($db, 12, $BatchNo, $txtpagotdd, "28");
		}
		if ($txtpagotransferencia > 0) {
			$idReg = fnInsertFormaPagoDeb($db, 12, $BatchNo, $txtpagotransferencia, "03");
		}

		//echo "entra:";
		//$PeriodNo = GetPeriod ( date ( "d/m/Y" ), $db, $tag );
		//$BatchNo = GetNextTransNo ( 12, $db );
		// INSERTA PAGO EN EFECTIVO
		// if ($txtpagoefectivo > 0) {
		// 	$account = $_POST ['pagoefectivo'];
		// 	$separa = explode ( '-', $account );
		// 	$account = $separa [0];
		// 	$narrative = $_POST ['txtrefefectivo'];
		// 	$amount = $txtpagoefectivo;
			
		// 	$narrativetxt = $debtorno . "-" . $BatchNo . "@" . $narrative . "|MXN|1|-" . $amount;
		// 	$x = insertagltrans ( $db, 12, $BatchNo, $PeriodNo, $account, $narrativetxt, $tag, $amount );
		// 	// insertadebtortrans($db, $BatchNo, $tag, 12, $debtorno, $branchcode, $PeriodNo, '', '', 0, $amount,0, 0,1,$narrative,'','',$amount,$currcode);
		// 	if ($narrative != "") {
		// 		if ($referencia != "") {
		// 			$referencia = $referencia . "; " . $narrative;
		// 		} else {
		// 			$referencia = $narrative;
		// 		}
		// 	}
		// }
		
		// INSERTA PAGO EN CHEQUES
		// if ($txtpagocheque > 0) {
		// 	$account = $_POST ['pagocheque'];
		// 	$separa = explode ( "-", $account );
		// 	$account = $separa [0];
		// 	$narrative = $_POST ['txtrefcheque'];
		// 	$amount = $txtpagocheque;
			
		// 	$narrativetxt = $debtorno . "-" . $BatchNo . "@" . $narrative . "|MXN|1|-" . $amount;
			
		// 	$x = insertagltrans ( $db, 12, $BatchNo, $PeriodNo, $account, $narrativetxt, $tag, $amount );
		// 	// insertadebtortrans($db, $BatchNo, $tag, 12, $debtorno, $branchcode, $PeriodNo, '', '', 0, $amount,0, 0,1,$narrative,'','',$amount,$currcode);
		// 	if ($narrative != "") {
		// 		if ($referencia != "") {
		// 			$referencia = $referencia . "; " . $narrative;
		// 		} else {
		// 			$referencia = $narrative;
		// 		}
		// 	}
		// }
		
		// INSERTA PAGO EN TDC
		// if ($txtpagotdc > 0) {
		// 	$account = $_POST ['pagotdc'];
		// 	$separa = explode ( "-", $account );
		// 	$account = $separa [0];
		// 	$narrative = $_POST ['txtreftdc'];
		// 	$amount = $txtpagotdc;
			
		// 	$narrativetxt = $debtorno . "-" . $BatchNo . "@" . $narrative . "|MXN|1|-" . $amount;
			
		// 	$x = insertagltrans ( $db, 12, $BatchNo, $PeriodNo, $account, $narrativetxt, $tag, $amount );
		// 	// insertadebtortrans($db, $BatchNo, $tag, 12, $debtorno, $branchcode, $PeriodNo, '', '', 0, $amount,0, 0,1,$narrative,'','',$amount,$currcode);
		// 	if ($narrative != "") {
		// 		if ($referencia != "") {
		// 			$referencia = $referencia . "; " . $narrative;
		// 		} else {
		// 			$referencia = $narrative;
		// 		}
		// 	}
		// }
                
        // INSERTA PAGO EN TDC
		// if ($txtpagotdd > 0) {
		// 	$account = $_POST ['pagotdd'];
		// 	$separa = explode ( "-", $account );
		// 	$account = $separa [0];
		// 	$narrative = $_POST ['txtreftdd'];
		// 	$amount = $txtpagotdd;

		// 	$narrativetxt = $debtorno . "-" . $BatchNo . "@" . $narrative . "|MXN|1|-" . $amount;

		// 	$x = insertagltrans ( $db, 12, $BatchNo, $PeriodNo, $account, $narrativetxt, $tag, $amount );
		// 	// insertadebtortrans($db, $BatchNo, $tag, 12, $debtorno, $branchcode, $PeriodNo, '', '', 0, $amount,0, 0,1,$narrative,'','',$amount,$currcode);
		// 	if ($narrative != "") {
		// 		if ($referencia != "") {
		// 			$referencia = $referencia . "; " . $narrative;
		// 		} else {
		// 			$referencia = $narrative;
		// 		}
		// 	}
		// }
		
		// INSERTA PAGO EN TRANSFERENCIA
		// if ($txtpagotransferencia > 0) {
		// 	$account = $_POST ['pagotransferencia'];
		// 	$separa = explode ( "-", $account );
		// 	$account = $separa [0];
		// 	$narrative = $_POST ['txtreftransferencia'];
		// 	$amount = $txtpagotransferencia;
			
		// 	$narrativetxt = $debtorno . "-" . $BatchNo . "@" . $narrative . "|MXN|1|-" . $amount;
			
		// 	$x = insertagltrans ( $db, 12, $BatchNo, $PeriodNo, $account, $narrativetxt, $tag, $amount );
		// 	// insertadebtortrans($db, $BatchNo, $tag, 12, $debtorno, $branchcode, $PeriodNo, '', '', 0, $amount,0, 0,1,$narrative,'','',$amount,$currcode);
		// 	if ($narrative != "") {
		// 		if ($referencia != "") {
		// 			$referencia = $referencia . "; " . $narrative;
		// 		} else {
		// 			$referencia = $narrative;
		// 		}
		// 	}
		// }

		// INSERTA PAGO EN ANTICIPO
		// if ($txtpagoanticipo > 0) {
		// 	$account = $_POST ['pagoanticipo'];
		// 	$separa = explode ( "-", $account );
		// 	$account = $separa [0];
		// 	$narrative = $_POST ['txtanticipo'];
		// 	$amount = $txtpagoanticipo;
			
		// 	$narrativetxt = $debtorno . "-" . $BatchNo . "@" . $narrative ." ".$cliente. "|MXN|1|-" . $amount;
			
		// 	$x = insertagltrans ( $db, 12, $BatchNo, $PeriodNo, $account, $narrativetxt, $tag, $amount );
		// 	// insertadebtortrans($db, $BatchNo, $tag, 12, $debtorno, $branchcode, $PeriodNo, '', '', 0, $amount,0, 0,1,$narrative,'','',$amount,$currcode);
		// 	if ($narrative != "") {
		// 		if ($referencia != "") {
		// 			$referencia = $referencia . "; " . $narrative;
		// 		} else {
		// 			$referencia = $narrative;
		// 		}
		// 	}
		// }

		// INSERTA PAGO EN BANCOS
		// if ($txtpagobancos > 0) {
		// 	$account = $_POST ['pagobancos'];
		// 	$separa = explode ( "-", $account );
		// 	$account = $separa [0];
		// 	$narrative = $_POST ['txtbancos'];
		// 	$amount = $txtpagobancos;
			
		// 	$narrativetxt = $debtorno . "-" . $BatchNo . "@" . $narrative . "|MXN|1|-" . $amount;
			
		// 	$x = insertagltrans ( $db, 12, $BatchNo, $PeriodNo, $account, $narrativetxt, $tag, $amount );
		// 	// insertadebtortrans($db, $BatchNo, $tag, 12, $debtorno, $branchcode, $PeriodNo, '', '', 0, $amount,0, 0,1,$narrative,'','',$amount,$currcode);
		// 	if ($narrative != "") {
		// 		if ($referencia != "") {
		// 			$referencia = $referencia . "; " . $narrative;
		// 		} else {
		// 			$referencia = $narrative;
		// 		}
		// 	}
		// }

		// $noaccount = $_SESSION['CompanyRecord']['gllink_Invoice'];
		/*if ($txtpagoanticipo > 0) {
			$noaccount = ClientAccount ( $tipocliente, "gl_bridgeaccountadvances", $db );
		}else{
			$noaccount = ClientAccount ( $tipocliente, "gl_accountcontado", $db );
		}*/

		// $noaccount = ClientAccount ( $tipocliente, "gl_accountcontado", $db );
		
		// $referenciatxt = 'ABONO A FACTURA DE CONTADO';
		$referenciatxt = $debtorno . "-" . $BatchNo . "@MXN|1|-" . $totalOperacionReg . "@" . $referencia;
		// $IDgltrans = insertagltrans ( $db, 12, $BatchNo, $PeriodNo, $noaccount, $referenciatxt, $tag, - $hdntotal );
		
		$IDfromtrans = insertadebtortrans ( $db, $BatchNo, $tag, 12, $debtorno, $branchcode, $PeriodNo, '', '', 0, - $totalOperacionReg, 0, 0, 1, $referencia, '', '', - $totalOperacionReg, $currcode, $codesat, $cuenta_banco, $datoUE );
		
		// *********INSERTA EN CUENTA DE IVAS**********
		// ***INSERTA EL IVA PROPORCIONAL EN CUENTA DE IVAS POR COBRAR E IVAS PAGADOS
		// ***INSERTA EN CUENTA IVAS POR COBRAR
		/* Obtiene cta de ivas pagados e ivas por cobrar */
		$SSQL = "SELECT taxglcodepaid,taxglcode
			FROM taxauthorities
			WHERE taxid = 1";
		$ErrMsg = _ ( 'Esta consulta no arrojo resultados' );
		// $STransResult = DB_query ( $SSQL, $db, $ErrMsg );
		// while ( $smyrow = DB_fetch_array ( $STransResult ) ) {
		// 	$ctaivaspagados = $smyrow ['taxglcodepaid'];
		// 	$ctaivaporcobrar = $smyrow ['taxglcode'];
		// }
		// ***INSERTA EN CUENTA IVAS POR COBRAR
		// $referencia = "ABONO IVA PROPORCIONAL IC";
		$referencia = $debtorno . "-" . $BatchNo . "@ABONO IVA PROPORCIONAL IC|MXN|1|" . $hdniva;
		// $x = insertagltrans ( $db, 12, $BatchNo, $PeriodNo, $ctaivaporcobrar, $referencia, $tag, $hdniva );
		
		// ***INSERTA EN CUENTA IVAS PAGADOS
		// $referencia = "ABONO IVA PROPORCIONAL IP";
		$referencia = $debtorno . "-" . $BatchNo . "@ABONO IVA PROPORCIONAL IP|MXN|1|-" . $hdniva;
		// $x = insertagltrans ( $db, 12, $BatchNo, $PeriodNo, $ctaivaspagados, $referencia, $tag, - $hdniva );
		
		$NewAllocTotal = $totalOperacionReg;
		$Settled = 1;
		
		$MSQL = 'UPDATE debtortrans
		SET diffonexch=' . $diffonexch . ',
		alloc = alloc +' . $NewAllocTotal . ',
		settled = ' . $Settled . '
		WHERE id = ' . $id;
		//echo "<br> SQL: ".$MSQL ;
		if (! $Result = DB_query ( $MSQL, $db )) {
			$ErrMsg = _ ( 'ERROR CRITICO' ) . '! ' . _ ( 'ANOTE ESTE ERROR Y BUSQUE AYUDA' ) . ': ' . _ ( 'El registro de transaccion de cliente no se pudo insertar' );
			$DbgMsg = _ ( 'El siguiente SQL fue utilizado' );
		} else {
			// inserta en la tabla de custallocns
			
			$rate = 1;
			$CASQL = "INSERT INTO
				custallocns (
				datealloc,
				amt,
				rate_from,
				currcode_from,
				rate_to,
				currcode_to,
				ratealloc,
				diffonexch_alloc,
				transid_allocfrom,
				transid_allocto
				) VALUES ('" . FormatDateForSQL ( date ( 'd/m/Y',strtotime($_POST ['txtFecha']) ) ) . "',
				" . $totalOperacionReg . ",
				" . $rate . ",
				'" . $currcode . "',
				" . $rate . ",
				'" . $currcode . "',
				" . $rate . ",
			    " . $diffonexch . ",
			    " . $IDfromtrans . ",
				" . $id . ")";
			if (! $Result = DB_query ( $CASQL, $db )) {
				$ErrMsg = _ ( 'ERROR CRITICO' ) . '! ' . _ ( 'ANOTE ESTE ERROR Y BUSQUE AYUDA' ) . ': ' . _ ( 'NO PUDO CAMBIAR EL REGISTRO DE CUSTALLOCS' );
				$DbgMsg = _ ( 'El siguiente SQL fue utilizado' );
			}
			$DebtorTransID = $IDfromtrans;

			//******************** Aqui se genera documento SALDO ANTICIPOS ************************//
			//Validar si es anticipo la factura que se esta pagando
			$sqlfac = "Select  currcode,rate , ovamount, ovgst from debtortrans where id=" . $id;
			$resultfac = DB_query ( $sqlfac, $db );
			$myrowcust = DB_fetch_array ( $resultfac );
			$subtotalcargo = $myrowcust ['ovamount'];
			$ivacargo = $myrowcust ['ovgst'];

			// $SQL = "SELECT  stockmaster.categoryid, stockmaster.flagadvance 
			// FROM debtortrans 
			// INNER JOIN stockmoves ON stockmoves.type = debtortrans.type AND stockmoves.transno = debtortrans.transno
			// INNER JOIN stockmaster ON stockmaster.stockid = stockmoves.stockid
			// WHERE
			// debtortrans.id='".$id."'
			// /*HAVING count(stockmaster.stockid) = 1;*/";
			// $Resultado = DB_query($SQL,$db);
			// if(DB_num_rows($Resultado)>0){
			// 	$esAnticipo = 1;
			// 	while ( $myRowA = DB_fetch_array($Resultado)) {
			// 		if( $myRowA['flagadvance'] <>'1' ){
			// 			$esAnticipo = 0;
			// 		}
			// 	}
			// 	if($esAnticipo == '1'){
			// 		$TransType = 130;
			// 		$transnoAnticipo = GetNextTransNo($TransType, $db);
			// 		if( abs($hdntotal - ($subtotalcargo +$ivacargo))<.02 ){
			// 			$SQL = "INSERT INTO debtortrans (tagref, transno, type, debtorno, branchcode, origtrandate, trandate, prd, tpe, order_, reference,  rate, ovamount,ovgst,  shipvia, consignment, ref1, currcode, duedate,nocuenta, paymentname, userid, lasttrandate, flagsendfiscal, flagfiscal)
			// 			SELECT tagref, ".$transnoAnticipo.", 130, debtorno, branchcode, NOW(), NOW(), prd, tpe, order_,'".$BatchNo."', rate, ovamount *-1,ovgst*-1, shipvia, consignment, '".$id."', currcode, NOW(),nocuenta, paymentname, 'desarrollo', NOW(), 0, 0
			// 			FROM 
			// 			debtortrans WHERE id='".$id."'";
			// 		}else{
			// 			// en caso de que sea una parcialidad
			// 			if($ivacargo>0){
			// 				$subtotal = round($hdntotal/1.16);
			// 				$IVAdoc =  round($hdntotal - $subtotal,2);
			// 			}else{
			// 				$subtotal = round($hdntotal,2);
			// 				$IVAdoc = 0;
			// 			}

			// 			$SQL = "INSERT INTO debtortrans (tagref, transno, type, debtorno, branchcode, origtrandate, trandate, prd, tpe, order_, reference,  rate, ovamount,ovgst,  shipvia, consignment, ref1, currcode, duedate,nocuenta, paymentname, userid, lasttrandate, flagsendfiscal, flagfiscal)
			// 			SELECT tagref, ".$transnoAnticipo.", 130, debtorno, branchcode, NOW(), NOW(), prd, tpe, order_,'".$BatchNo."', rate, ".$subtotal." *-1,".$IVAdoc."*-1, shipvia, consignment, '".$id."', currcode, NOW(),nocuenta, paymentname, 'desarrollo', NOW(), 0, 0
			// 			FROM 
			// 			debtortrans WHERE id='".$id."'";
			// 		}
			// 		if(!$resultado = DB_query($SQL,$db)){
			// 			$error = 'Ocurrió un error al generar documento de saldo anticipo';
			// 		}
			// 	}
			// }

			$detalleabono = $totalOperacionReg;
			$detalleabonoiva = $hdniva;
			//$IDgltrans . ",
			if ($detalleabono != 0) {
				$ISQL = "INSERT INTO debtortransmovs (
					transno,
					type,
					debtorno,
					branchcode,
					trandate,
					prd,
					reference,
					tpe,
					order_,
					ovamount,
					ovgst,
					ovfreight,
					rate,
					invtext,
					shipvia,
					consignment,
					alloc,
					tagref,
					origtrandate,
					idgltrans,
					userid,
					currcode
					)
				VALUES (
					" . $BatchNo . ",
					" . "12" . ", 
					'" . $debtorno . "',
					'" . $branchcode . "',  
					'" . date('Y-m-d',strtotime($_POST ['txtFecha'])).' '.date('H:i:s'). "',
					" . $PeriodNo . ",
					'" . $typeinvoice . " - " . $transnofac . "',
					'',
					0,
					" . - $detalleabono . ",
					" . - $detalleabonoiva . ",
					0,
					" . 1 . ",
					'AFECTO DOCUMENTO " . $transnofac . " (" . str_replace ( "|", "-", $folio ) . ")" . "',
					'',
					'',
					" . - $NewAllocTotal . ",
					'" . $tag . "',
					Now(),
					0,
					'" . $_SESSION ['UserID'] . "',
					'" . $currcode . "'
				)";
				
				$ErrMsg = _ ( 'ERROR CRITICO' ) . '! ' . _ ( 'ANOTE ESTE ERROR Y BUSQUE AYUDA' ) . ': ' . _ ( 'El registro de transaccion de cliente no se pudo insertar' );
				$DbgMsg = _ ( 'El siguiente SQL fue utilizado' );
				$Result = DB_query ( $ISQL, $db, $ErrMsg, $DbgMsg, true );
			}
			
			// Consulta el rfc y clave de facturacion electronica
			$SQL = " SELECT l.taxid,l.address5,t.tagname,t.areacode,l.legalid,t.typeinvoice,legalname
				FROM legalbusinessunit l, tags t
				WHERE l.legalid=t.legalid AND tagref='" . $tag . "'";
			$Result = DB_query ( $SQL, $db );
			if (DB_num_rows ( $Result ) == 1) {
				$myrowtags = DB_fetch_array ( $Result );
				$rfc = trim ( $myrowtags ['taxid'] );
				$keyfact = $myrowtags ['address5'];
				$nombre = $myrowtags ['tagname'];
				$area = $myrowtags ['areacode'];
				$legaid = $myrowtags ['legalid'];
				$tipofacturacionxtag = $myrowtags ['typeinvoice'];
				$legalname = $myrowtags ['legalname'];
			}

			$tipodefacturacion = 12;
			
			// if (1 == 2) {
			// 	// No realizar proceso para generacion de xml, afecta proceso y no es necesario para el proceso
			// 	if ($_SESSION ['DocumentNextByType'] == 1) {
			// 		$InvoiceNoTAG = DocumentNextByType ( 12, $tag, $area, $legaid, $db );
			// 	} else {
			// 		$InvoiceNoTAG = DocumentNext ( 12, $tag, $area, $legaid, $db );
			// 	}
			// 	$separa = explode ( '|', $InvoiceNoTAG );
				
			// 	$serie = $separa [1];
			// 	$folio = $separa [0];
				
			// 	$tipodefacturacion = 12;
			// 	$OrderNo = 0;
			// 	$compelectronico = XSAInvoicingRecibo ( $BatchNo, $OrderNo, '10080_32', $tipodefacturacion, $tag, $serie, $folio, $db );
				
			// 	// Envia los datos al archivooooo
			// 	$mitxt = "txt_NoteDebit/CompIng" . $tipodefacturacion . '_' . $tag . '_' . $BatchNo . ".txt"; // Ponele el nombre que quieras al archivo

			// 	$myfile = $mitxt; // Ponele el nombre que quieras al archivo
			// 	$compelectronico = utf8_encode ( $compelectronico );
				
			// 	$empresa = utf8_encode ( $keyfact . '-' . $rfc );
			// 	$nombre = utf8_encode ( $nombre );
			// 	if ($ambiente == "desarrollo") {
			// 		$tipo = utf8_encode ( 'NddPortalito' );
			// 	} else {
			// 		$tipo = utf8_encode ( 'Comprobante de Ingresos' );
			// 	}
			// 	$myfile = utf8_encode ( $myfile );
			// 	$compelectronico = utf8_encode ( $compelectronico );
				
			// 	if ($tipofacturacionxtag == 1) {
			// 		$param = array (
			// 				'in0' => $empresa,
			// 				'in1' => $nombre,
			// 				'in2' => $tipo,
			// 				'in3' => $myfile,
			// 				'in4' => $compelectronico 
			// 		);
			// 		try {
			// 			if ($ambiente == "desarrollo") {
			// 				$client = new SoapClient ( "http://demo.xsa.com.mx/xsamanager/services/FileReceiverService?wsdl" );
			// 			} else {
			// 				$client = new SoapClient ( $_SESSION ['XSA'] . "xsamanager/services/FileReceiverService?wsdl" );
			// 			}
			// 			$codigo = $client->guardarDocumento ( $param );
			// 		} catch ( SoapFault $exception ) {
			// 			$errorMessage = $exception->getMessage ();
			// 		}
					
			// 		if ($ambiente == "desarrollo") {
			// 			$ligarecibo = "http://demo.xsa.com.mx/xsamanager/downloadCfdWebView?serie=" . $serie . "&folio=" . $folio . "&tipo=PDF&rfc=" . $rfc . "&key=" . $keyfact;
			// 		} else {
			// 			$ligarecibo = $_SESSION ['XSA'] . "xsamanager/downloadCfdWebView?serie=" . $serie . "&folio=" . $folio . "&tipo=PDF&rfc=" . $rfc . "&key=" . $keyfact;
			// 		}
			// 	} elseif ($tipofacturacionxtag == 2) {
			// 		$XMLElectronico = generaXML ( $compelectronico, 'ingreso', $tag, $serie, $folio, $DebtorTransID, 'Recibo', $OrderNo, $db );
			// 		$PrintDispatchNote = $rootpath . '/PDFReceipt.php?OrderNo=' . $OrderNo . '&TransNo=' . $BatchNo;
			// 		echo '<p class="page_title_text">';
			// 		echo '<img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir Recibo ' ) . '" alt="">' . ' ';
			// 		echo '<a href="' . $PrintDispatchNote . '" target="_blank">';
			// 		echo _ ( 'Imprimir Recibo PDF' ) . '</a></p>';
			// 	} elseif ($tipofacturacionxtag == 3) {
			// 		$PrintDispatchNote = $rootpath . '/PDFReceiptTemplate.php?OrderNo=' . $OrderNo . '&TransNo=' . $BatchNo;
			// 		echo '<p class="page_title_text">';
			// 		echo '<img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir Recibo ' ) . '" alt="">' . ' ';
			// 		echo '<a href="' . $PrintDispatchNote . '" target="_blank">';
			// 		echo _ ( 'Imprimir Recibo PDF ' ) . '</a></p>';
			// 	} elseif ($tipofacturacionxtag == 4) {
					
			// 		$success = false;
			// 		$config = $_SESSION;
			// 		$arrayGeneracion = generaXMLCFDI ( $compelectronico, 'ingreso', $tag, $serie, $folio, $DebtorTransID, 'Recibo', $OrderNo, $db );
			// 		$XMLElectronico = $arrayGeneracion ["xml"];
			// 		$XMLElectronico = str_replace ( '<?xml version="1.0" encoding="UTF-8">', '', $XMLElectronico );
			// 		// echo '<pre>fact:<br>'.$compelectronico;
			// 		// echo '<pre>fact:<br>'.htmlentities($XMLElectronico);
			// 		if ($_SESSION ['SendCI'] == 1) {
			// 			include_once 'timbradores/TimbradorFactory.php';
			// 			$timbrador = TimbradorFactory::getTimbrador ( $config );
			// 			if ($timbrador != null) {
			// 				$timbrador->setRfcEmisor ( $rfc );
			// 				$timbrador->setDb ( $db );
			// 				$cfdi = $timbrador->timbrarDocumento ( $XMLElectronico );
			// 				$success = ($timbrador->tieneErrores () == false);
			// 				foreach ( $timbrador->getErrores () as $error ) {
			// 					prnMsg ( $error, 'error' );
			// 				}
			// 			} else {
			// 				prnMsg ( _ ( 'No hay un timbrador configurado en el sistema' ), 'error' );
			// 			}
			// 		}
			// 		if ($success) {
			// 			// echo 'entra';
			// 			// leemos la informacion del cfdi en un arreglo
			// 			$DatosCFDI = TraeTimbreCFDI ( $cfdi );
			// 			if (strlen ( $DatosCFDI ['FechaTimbrado'] ) > 0) {
			// 				$cadenatimbre = '||1.0|' . $DatosCFDI ['UUID'] . '|' . $DatosCFDI ['FechaTimbrado'] . '|' . $DatosCFDI ['selloCFD'] . '|' . $DatosCFDI ['noCertificadoSAT'] . '||';
							
			// 				// guardamos el timbre fiscal en la base de datos para efectos de impresion de datos
			// 				$sql = "UPDATE debtortrans
			// 				     SET fechatimbrado='" . $DatosCFDI ['FechaTimbrado'] . "',
			// 					uuid='" . $DatosCFDI ['UUID'] . "',
			// 					timbre='" . $DatosCFDI ['selloSAT'] . "',
			// 					cadenatimbre='" . $cadenatimbre . "'
			// 					where id=" . $DebtorTransID;
			// 				$ErrMsg = _ ( 'El Sql que fallo fue' );
			// 				$DbgMsg = _ ( 'No se pudo actualizar el sello y cadena del documento' );
			// 				$Result = DB_query ( $sql, $db, $ErrMsg, $DbgMsg, true );
							
			// 				$XMLElectronico = $cfdi;
							
			// 				// Guardamos el XML una vez que se agrego el timbre fiscal
			// 				$carpeta = 'Recibo';
			// 				$dir = "/var/www/html/" . dirname ( $_SERVER ['PHP_SELF'] ) . "/companies/" . $_SESSION ['DatabaseName'] . "/SAT/" . str_replace ( '.', '', str_replace ( ' ', '', $legalname ) ) . "/XML/" . $carpeta . "/";
			// 				$nufa = $serie . $folio;
			// 				$mitxt = $dir . $nufa . ".xml";
			// 				unlink ( $mitxt );
							
			// 				$fp = fopen ( $mitxt, "w" );
			// 				fwrite ( $fp, $XMLElectronico );
			// 				fclose ( $fp );
							
			// 				$fp = fopen ( $mitxt . '.COPIA', "w" );
			// 				fwrite ( $fp, $XMLElectronico );
			// 				fclose ( $fp );
							
			// 				// Se agrega la generacion de xml_intermedio
			// 				$array = generaXMLIntermedio ( $compelectronico, $XMLElectronico, $arrayGeneracion ["cadenaOriginal"], utf8_encode ( $arrayGeneracion ["cantidadLetra"] ), $DebtorTransID, $db, 13, $tag, $tipodefacturacion, $BatchNo );
			// 				$xmlImpresion = utf8_decode ( $array ["xmlImpresion"] );
			// 				$rfcEmisor = $array ["rfcEmisor"];
			// 				$fechaEmision = $array ["fechaEmision"];
			// 				// Almacenar XML
			// 				// $flagsendfiscal=1;
			// 				$tipodefacturacion = 12;
			// 				$flagsendfiscal = 1;
			// 				$query = "INSERT INTO Xmls(transNo,type,rfcEmisor,fechaEmision,xmlSat,xmlImpresion,fiscal)
			// 							VALUES(" . $BatchNo . "," . $tipodefacturacion . ",'" . $rfcEmisor . "','" . $fechaEmision . "','" . $XMLElectronico . "','" . $xmlImpresion . "'," . $flagsendfiscal . ");";
			// 				$Result = DB_query ( $query, $db, $ErrMsg, $DbgMsg, true );
							
			// 				$liga = "PDFInvoice.php?&clave=chequepoliza_sefia";
			// 				$liga = '<p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir' ) . '" alt="">' . ' ' . '<a target="_blank" target="_blank" href="' . $rootpath . '/PDFInvoice.php?OrderNo=' . $OrderNo . '&TransNo=' . $BatchNo . '&Type=' . $tipodefacturacion . '&Tagref=' . $tag . '">' . _ ( 'Imprimir Recibo' ) . ' (' . _ ( 'Laser' ) . ')' . '</a>';
			// 			} else {
			// 				prnMsg ( _ ( 'No fue posible realizar el timbrado del documento, verifique con el administrador; el numero de error es:' ) . $cfdi, 'error' );
			// 				// exit;
			// 			}
			// 		}
			// 		if ($_SESSION ['SendCI'] == 0) {
			// 			//echo 'entra aquiiiii...';
			// 			// Se agrega la generacion de xml_intermedio
			// 			$array = generaXMLIntermedio ( $compelectronico, $XMLElectronico, $arrayGeneracion ["cadenaOriginal"], utf8_encode ( $arrayGeneracion ["cantidadLetra"] ), $DebtorTransID, $db, 13, $tag, $tipodefacturacion, $BatchNo );
			// 			$xmlImpresion = utf8_decode ( $array ["xmlImpresion"] );
			// 			$rfcEmisor = $array ["rfcEmisor"];
			// 			$fechaEmision = $array ["fechaEmision"];
			// 			// Almacenar XML
			// 			// $flagsendfiscal=1;
			// 			$tipodefacturacion = 12;
			// 			$flagsendfiscal = 0;
			// 			$query = "INSERT INTO Xmls(transNo,type,rfcEmisor,fechaEmision,xmlSat,xmlImpresion,fiscal)
			// 							VALUES(" . $BatchNo . "," . $tipodefacturacion . ",'" . $rfcEmisor . "','" . $fechaEmision . "','" . $XMLElectronico . "','" . $xmlImpresion . "'," . $flagsendfiscal . ");";
			// 			$Result = DB_query ( $query, $db, $ErrMsg, $DbgMsg, true );
						
			// 			$liga = "PDFInvoice.php?&clave=chequepoliza_sefia";
			// 			$liga = '<p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir' ) . '" alt="">' . ' ' . '<a target="_blank" target="_blank" href="' . $rootpath . '/PDFInvoice.php?OrderNo=' . $OrderNo . '&TransNo=' . $BatchNo . '&Type=' . $tipodefacturacion . '&Tagref=' . $tag . '">' . _ ( 'Imprimir Recibo' ) . ' (' . _ ( 'Laser' ) . ')' . '</a>';
			// 		}
			// 	} else {
					
			// 		$liga = GetUrlToPrint ( $tag, 12, $db );
			// 		$PrintDispatchNote = $rootpath . '/' . $liga . '&' . SID . 'PrintPDF=Yes&InvoiceNo=' . $BatchNo . '&Tagref=' . $tag;
			// 		/*
			// 		 * FIN ************************** Recibo Electronico
			// 		 */
			//                               if ($_SESSION['ImpTiketPV']==1){
			//                                   $ligaticket = "PDFReceiptTicket_v2.php";
			//                                   $PrintDispatchNoteTicket = $rootpath . '/' . $ligaticket . '?OrderNo=' . $OrderNo . '&TransNo=' . $BatchNo . '&Type=' . $tipodefacturacion;
			//                                   echo '<a href="' . $PrintDispatchNote . '" target="_blank">';
			//                                   echo _ ( 'Imprimir Ticket Recibo ' ) . '</a></p>';
			//                               }
			//                               else{
			//                                   echo '<a href="' . $PrintDispatchNote . '" target="_blank">';
			//                                   echo _ ( 'Imprimir Recibo PDF ' ) . '</a></p>';
			//                               }
			// 	}
			// }

			$MSQL = "UPDATE debtortrans
				SET folio = '" . $InvoiceNoTAG . "' ,
					flagsendfiscal = '" . $flagsendfiscal . "'
				WHERE type = " . $tipodefacturacion . "
				and transno = " . $BatchNo . "
				and tagref = '" . $tag."'";
			if (! $Result = DB_query ( $MSQL, $db )) {
				$ErrMsg = _ ( 'ERROR CRITICO' ) . '! ' . _ ( 'ANOTE ESTE ERROR Y BUSQUE AYUDA' ) . ': ' . _ ( 'El registro de transaccion de cliente no se pudo insertar' );
				$DbgMsg = _ ( 'El siguiente SQL fue utilizado' );
			}

			$pv = "";
			if ($_POST['txtPV'] == 'pv1') {
				$pv = "&pv=pv1";
			}

			echo "<div align='center'>
			<a class='btnPrint' href='PDFInvoice.php?&OrderNo=0&TransNo=".$BatchNo."&Type=".$tipodefacturacion."&Tagref=".$tag."'>Imprimir Recibo PDF</a>
			</div>";
			// echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/CustomerReceiptFacturaContado.php?imprimir=yes&transnofac=' . $transnofac . '&debtorno=' . $debtorno . '&branchcode=' . $branchcode . '&tag=' . $tag . '&foliorecibo=' . $InvoiceNoTAG . '&transnorecibo=' . $BatchNo . '&typeinvoice=' . $typeinvoice . '&shippinglogid='.$_POST['shippinglogid'].'&shippingno='.$_POST['shippingno'].$pv.'">';
			if ($_POST['txtPV'] != 'pv1') {
				include ('includes/footer.inc');
			}
			exit ();
		}
	} else {
		$aplica = 0;
	}
}

/*
 * INICIO FORMA DONDE SE MUESTRAN LOS DATOS A CAPTURA
 */
if ($tipofacturacionxtag == 1) {
	$separa = explode ( '|', $folio );
	$serie = $separa [0];
	$folio = $separa [1];
	// Consulta el rfc y clave de facturacion electronica
	$SQL = " SELECT l.taxid,l.address5,t.tagdescription,t.typeinvoice
	       FROM legalbusinessunit l, tags t
	       WHERE l.legalid=t.legalid AND tagref='" . $tag . "'";
	$Result = DB_query ( $SQL, $db );
	if (DB_num_rows ( $Result ) == 1) {
		$myrowtags = DB_fetch_array ( $Result );
		
		if ($ambiente == "desarrollo") {
			$rfc = "AAA010101AAA";
			$keyfact = "4aeae69501cd620d15760ff2431b1e8f";
			$nombre = "ACME CORPORATION SA DE CV";
		} else {
			$rfc = trim ( $myrowtags ['taxid'] );
			$keyfact = $myrowtags ['address5'];
			$nombre = $myrowtags ['tagdescription'];
			$tipofacturacionxtag = $myrowtags ['typeinvoice'];
		}
	}
	
	if ($ambiente == "desarrollo") {
		$liga = "http://demo.xsa.com.mx/xsamanager/downloadCfdWebView?serie=" . $serie . "&folio=" . $folio . "&tipo=PDF&rfc=" . $rfc . "&key=" . $keyfact;
	} else {
		$liga = $_SESSION ['XSA'] . "xsamanager/downloadCfdWebView?serie=" . $serie . "&folio=" . $folio . "&tipo=PDF&rfc=" . $rfc . "&key=" . $keyfact;
	}
}

if (isset ( $_GET ['imprimir'] )) {
	
	if ($tipofacturacionxtag == 1) {
		$foliorecibo = $_GET ['foliorecibo'];
		$separa = explode ( '|', $foliorecibo );
		if ($ambiente == "desarrollo") {
			$serie = "ACACI";
		} else {
			$serie = $separa [1];
		}
		$folio = $separa [0];
		// Consulta el rfc y clave de facturacion electronica
		$SQL = " SELECT l.taxid,l.address5,t.tagdescription
		       FROM legalbusinessunit l, tags t
		       WHERE l.legalid=t.legalid AND tagref='" . $tag . "'";
		$Result = DB_query ( $SQL, $db );
		if (DB_num_rows ( $Result ) == 1) {
			$myrowtags = DB_fetch_array ( $Result );
			if ($ambiente == "desarrollo") {
				$rfc = "AAA010101AAA";
				$keyfact = "4aeae69501cd620d15760ff2431b1e8f";
				$nombre = "ACME CORPORATION SA DE CV";
			} else {
				$rfc = trim ( $myrowtags ['taxid'] );
				$keyfact = $myrowtags ['address5'];
				$nombre = $myrowtags ['tagdescription'];
			}
		}
		if ($ambiente == "desarrollo") {
			$ligarecibo = "http://demo.xsa.com.mx/xsamanager/downloadCfdWebView?serie=" . $serie . "&folio=" . $folio . "&tipo=PDF&rfc=" . $rfc . "&key=" . $keyfact;
		} else {
			$ligarecibo = $_SESSION ['XSA'] . "xsamanager/downloadCfdWebView?serie=" . $serie . "&folio=" . $folio . "&tipo=PDF&rfc=" . $rfc . "&key=" . $keyfact;
		}
	}
	
	if ($tipofacturacionxtag == 2) {
		$ligaTicket = "PDFReceiptTicket.php";
		$ligaTicket = $ligaTicket . "<a href='" . $rootpath . "/" . $liga . "?TransNo=" . $myrow ['transno'] . "' target='_blank'>";
		$ligaTicket = $ligaTicket . "<img src='" . $rootpath . "/css/" . $theme . "/images/printer.png' title='" . _ ( 'Imprimir Ticket' ) . "' alt=''>";
		$ligaTicket = $ligaTicket . "</a>";
	}
}

?>

<!-- No habilitar librerias afecta las cargadas en el header y footer -->
<!-- <script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script> -->

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" >
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">

<?php
echo '<form action="' . $_SERVER ['PHP_SELF'] .'" method=post name=form1>';

// echo '<p class="page_title_text" '.$style.' '.$style2.'>' . ' ' . _ ( 'PAGAR FACTURAS DE CONTADO' ) . '</p>';

// $stylerenglon = "style='border:1px solid; border-color:silver;'";
$stylerenglon = "style='border: 0px;'";

echo '<div class="row"></div>';

echo '<div class="col-xs-12 col-md-3">';
echo '</div>';

echo '<div class="col-xs-12 col-md-6">'; // Div Tabla

echo "<table class='table table-striped' cellpadding='0' cellspacing='0' width='50%' style='border:0px solid; ".$styleDisplay."' ".$style2.">";
echo "<tr><td width='25%' $stylerenglon><b>" . _ ( 'Contribuyente' ) . ":</b></td>";
echo "<td colspan='3' $stylerenglon>" . $debtorno . " - " . $cliente . "</td></tr>";
echo "<tr><td $stylerenglon><b>" . _ ( 'Pase de Cobro' ) . ":</b></td>";
echo "<td  width='40%' $stylerenglon>" . $OrderNo . "</td>";
echo "<td $stylerenglon><b>" . _ ( '' ) . "</b></td>";
// echo "<td $stylerenglon>" . $transnofac . "</td>";
echo "<td $stylerenglon></td>";
echo "</tr>";

echo "<tr style='display: none;'><td colspan='2' $stylerenglon>&nbsp;</td>";
echo "<td $stylerenglon><b>" . _ ( 'Subtotal' ) . ":</b></td>";
echo "<td style='text-align:right; border: 0px;'>" . number_format ( $ovamount, 2 );
echo "<input type='hidden' name='hdnsubtotal' value='" . $ovamount . "'>";
echo "</td></tr>";

echo "<tr style='display: none;'><td colspan='2' $stylerenglon>&nbsp;</td>";
echo "<td $stylerenglon><b>" . _ ( 'IVA' ) . ":</b></td>";
echo "<td style='text-align:right; border: 0px;'>" . number_format ( $ovgst, 2 );
if($ovgst >0 AND $alloc>0){
	$ovgst2 = $ovamount + $ovgst - $alloc;
	//echo "<br>"
	$ovgst2 = (($ovgst2 / 1.16)) * 0.16;
}else{
	$ovgst2= $ovgst;
}
echo "<input  type='hidden' name='hdniva' value='" . $ovgst2 . "'>";
echo "</td></tr>";

echo "<tr><td colspan='2' $stylerenglon>&nbsp;</td>";
echo "<td $stylerenglon><b>" . _ ( 'Total' ) . ":</b></td>";
echo "<td style='text-align:right; border: 0px;'>" . number_format ( ($ovamount + $ovgst), 2 );
echo "<input type='hidden' name='hdntotal' id='hdntotal' value='" . number_format(($ovamount + $ovgst - $alloc), 2, '.', '') . "'>";
echo "<input type='hidden' name=shippinglogid value=" . $_POST['shippinglogid'] . ">";
echo "<input type='hidden' name=shippingno value=" . $_POST['shippingno'] . ">";

echo "</td></tr>";

//***********************
// Impresion y Envio por Correo
//***********************
echo "<tr style='display: none;'>";
echo "<td colspan='2' $stylerenglon>";
if ($tipofacturacionxtag == 1) {
	echo "<a href='" . $liga . "' target='_blank' /><br>Imprimir Factura</a>";
} elseif ($tipofacturacionxtag == 2) {
	// $PrintDispatchNote = $rootpath . '/PrintCustTransPortrait.php?' . SID . 'PrintPDF=Yes&InvOrCredit=Invoicet&FromTransNo='.$transnofac.'&Tagref='.$tag;
	// echo "<a target='_blank' href='" . $PrintDispatchNote . "'>Imprimir Factura</a>";
	$liga = '<p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir Factura' ) . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/PDFInvoice.php?OrderNo=' . $OrderNo . '&TransNo=' . $transnofac . '&Type=' . $typeinvoice . '&Tagref=' . $tag . '">' . _ ( 'Imprimir Factura' ) . ' (' . _ ( 'Laser' ) . ')' . '</a></p>';
	echo $liga;
	
	$liga = '<p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir Factura' ) . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/PDFInvoice.php?printdoc=yes&OrderNo=' . $OrderNo . '&TransNo=' . $transnofac . '&Type=' . $typeinvoice . '&Tagref=' . $tag . '">' . _ ( 'Imprimir Factura' ) . ' (' . _ ( 'Ticket' ) . ')' . '</a></p>';
	echo $liga;
} elseif ($tipofacturacionxtag == 3) {
	if ($typeinvoice != 119) {
		$typeinvoicex = 10;
	} else {
		$typeinvoicex = 119;
	}
	$liga = GetUrlToPrint ( $tag, $typeinvoicex, $db );
	
	$liga = '<p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir Factura' ) . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/' . $liga . '?OrderNo=' . $OrderNo . '&TransNo=' . $transnofac . '&Type=' . $typeinvoice . '&Tagref=' . $tag . '">' . _ ( 'Imprimir Factura' ) . ' (' . _ ( 'Laser' ) . ')' . '</a></p>';
	echo $liga;
} else {
	// $liga = GetUrlToPrint($tag,10,$db);
    if(!$_SESSION ['DatabaseName']=="erpmservice_DIST" OR !$_SESSION ['DatabaseName']=="erpmservice_DES" OR !$_SESSION ['DatabaseName']=="erpmservice"){
	$liga = 'PDFInvoice.php?';
	$PrintDispatchNote = $rootpath . '/' . $liga . '&' . SID . 'PrintPDF=Yes&OrderNo=' . $OrderNo . '&Tagref=' . $tag . '&TransNo=' . $transnofac . '&Type=' . $typeinvoice;
	echo "<a target='_blank' href='" . $PrintDispatchNote . "'>Imprimir Factura</a>";
    }else{ 
    	if ($_SESSION['ImpTiketPV']==1){
            //
    		$liga = 'PDFSalesTicket.php?';
    		$PrintDispatchNote = $rootpath . '/' . $liga . '&' . SID . 'PrintPDF=Yes&OrderNo=' . $OrderNo . '&Tagref=' . $tag . '&TransNo=' . $transnofac . '&Type=' . $typeinvoice;
    		echo "<a target='_blank' href='" . $PrintDispatchNote . "'>"._("Imprimir Ticket")."</a>";
    	}else{
       // $liga = 'PDFRemisionTemplate.php?';
            
            $liga = 'PDFInvoice.php?';
            $PrintDispatchNote = $rootpath . '/' . $liga . '&' . SID . 'PrintPDF=Yes&OrderNo=' . $OrderNo . '&Tagref=' . $tag . '&TransNo=' . $transnofac . '&Type=' . $typeinvoice;
            echo "<a target='_blank' href='" . $PrintDispatchNote . "'>Imprimir Factura</a>";

            if(Havepermission ( $_SESSION ['UserID'], 1667, $db ) == 1){
               
                    //prnMsg("Se creo el embarque globla de mantera exitosa", "info");
                $sqlpdfembarque = "SELECT SUM(shippingorderdetails.qty - shippingorderdetails.qty_sent) as entregado 
                                            FROM shippingorderdetails
                                            WHERE shippingorderdetails.shippingno = '".$_POST['shippingno']."'
                                            GROUP BY shippingorderdetails.shippingno ";
                        $respdfembarque = DB_query($sqlpdfembarque, $db);
                        $myrowpdfemb = DB_fetch_array($respdfembarque);
                        $cantentr = $myrowpdfemb['entregado'];
                        if($cantentr > 0){
                            $PDFInvoiceTemplateFile='PDFPrevioOrderTicket.php';
                                    $liga = '<p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir' ) . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/' . $PDFInvoiceTemplateFile . '?&shippingno='.$_POST['shippingno']. '">' . _ ( 'Imprimir Embarque Previo' ) . '</a>';
                                    echo $liga;
                        }else{
                            $PDFInvoiceTemplateFile='PDFDeliveryOrderTicket_V2_0.php';
                            $liga = '<p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir' ) . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/' . $PDFInvoiceTemplateFile . '?&shippinglogid='.$_POST['shippinglogid']. '">' . _ ( 'Imprimir Embarque' ) . '</a>';
                            echo $liga;
                        }
                    
                

            }
        }
        
        
        
    }
}
echo "</td>";
if(Havepermission ( $_SESSION ['UserID'], 1760, $db ) == 1){
	$liga = '<p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir Ticket Fiscal' ) . '" alt="">' . ' ' . '<a  target="_blank" href="PDFInvoiceticketV2_0.php?PrintPDF=Yes&OrderNo=' . $OrderNo . '&TransNo=' . $transnofac . '&Type=' . $typeinvoice . '&Tagref=' . $tag . '">' . _ ( 'Ticket Fiscal' ) . ' (' . _ ( 'Termico' ) . ')' . '</a>';
	echo '<td>'.$liga.'</td>';
	echo '<tr class="header-verde"><td><a href="CustomerAllocations_V6_0.php?NewAplication=yes&DebtorNo='.$debtorno.'" target="_blank" >Agrega anticipos</a></td></tr>';
}

echo "<td colspan='1' $stylerenglon>";
$ligaImpresion = "";
if (isset ( $_GET ['imprimir'] )) {
	if ($tipofacturacionxtag == 1) {
		echo "<a href='" . $ligarecibo . "' target='_blank' /><br>Imprimir Recibo</a>";
	} elseif ($tipofacturacionxtag == 2) {
		/*
		 * echo '<a href="' . $rootpath . '/PrintRecibo.php?BatchNo=' . $transnorecibo . '" target="_blank">'; echo _('Imprimir Recibo PDF Local') . '</a>';
		 */
		$OrderNo = 0;
		$PrintDispatchNote = $rootpath . '/PDFReceipt.php?OrderNo=' . $OrderNo . '&TransNo=' . $transnorecibo;
		echo '<p class="page_title_text">';
		echo '<img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir Recibo Local' ) . '" alt="">' . ' ';
		echo '<a href="' . $PrintDispatchNote . '" target="_blank">';
		echo _ ( 'Imprimir Recibo PDF ' ) . '</a></p>';
		
		$ligaTicket = "PDFReceiptTicket.php";
		$ligaTicket = "<a href='" . $rootpath . "/" . $ligaTicket . "?TransNo=" . $transnorecibo . "' target='_blank'>";
		$ligaTicket = $ligaTicket . "<img src='" . $rootpath . "/css/" . $theme . "/images/printer.png' title='" . _ ( 'Imprimir Ticket' ) . "' alt=''>";
		$ligaTicket = $ligaTicket . "</a>";
		
		echo '<br>' . $ligaTicket;
	} elseif ($tipofacturacionxtag == 3) {
		$OrderNo = 0;
		$PrintDispatchNote = $rootpath . '/PDFReceiptTemplate.php?OrderNo=' . $OrderNo . '&TransNo=' . $transnorecibo;
		echo '<p class="page_title_text">';
		echo '<img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir Recibo Local' ) . '" alt="">' . ' ';
		echo '<a href="' . $PrintDispatchNote . '" target="_blank">';
		echo _ ( 'Imprimir Recibo PDF ' ) . '</a></p>';
	} else {
		// $liga = GetUrlToPrint($tag,12,$db);
            if ($_SESSION['ImpTiketPV']==1){
            //
    		$liga = 'PDFReceiptTicket_v2.php?';
    		$PrintDispatchNote = $rootpath . '/' . $liga . '&' . SID . 'PrintPDF=Yes&OrderNo=' . $OrderNo . '&Tagref=' . $tag . '&TransNo=' . $transnorecibo . '&Type=' . $typeinvoice;
    		echo "<a target='_blank' href='" . $PrintDispatchNote . "'>"._("Imprimir Recibo Ticket")."</a>";
            }
            else{
		$liga = 'PDFInvoice.php?';
		$PrintDispatchNote = $rootpath . '/' . $liga . '&' . SID . 'PrintPDF=Yes&Type=12&TransNo=' . $transnorecibo . '&Tagref=' . $tag;
		// $ligaImpresion = "<a target='_blank' href='" . $PrintDispatchNote . "'>Imprimir Recibo PDF</a>";
		$ligaImpresion = "<a class='btnPrint' href='" . $PrintDispatchNote . "'>Imprimir Recibo PDF</a>";
		
		echo $ligaImpresion;
            
                /*if(Havepermission ( $_SESSION ['UserID'], 1667, $db ) == 1){
                    $PDFInvoiceTemplateFile='PDFDeliveryOrderTicket.php';
                    $liga = '<p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir' ) . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/' . $PDFInvoiceTemplateFile . '?&shippinglogid='.$_POST['shippinglogid']. '">' . _ ( 'Imprimir Embarque2' ) . '</a>';
                    echo $liga;
                }*/
            }
            
	}
	echo "</td>";
	echo "<td nowrap $stylerenglon>";
	// poner link para enviar email con xml
	$EnvioXML = $rootpath . '/SendInvoicebyMail.php?id=' . $id;
	$EnvioXML = "&nbsp;&nbsp;&nbsp;<a TARGET='_blank' href=" . $EnvioXML . "><img src='part_pics/Mail-Forward.png' alt='Reenviar SAT' border=0>" . _ ( 'Enviar Factura' ) . "</a>";
	if (strlen ( $emails ) > 0) {
		echo '<div style="text-align:center">';
		// echo $EnvioXML;
		echo '</div>';
	}
	echo "</td>";
} else {
	echo "&nbsp;";
}
echo "</td>";
echo "</tr>";
echo "</table>";

echo '</div>'; // Div Tabla

echo '<div class="col-xs-12 col-md-3">';
echo '</div>';

echo '<div class="row"></div>';

if (isset ( $_GET ['imprimir'] ) && $pv1 = 'pv1') {
	// Solo mostrar PDF Impresion
	echo "<div align='center'>";
	echo $ligaImpresion;
	echo "</div>";
}


if (isset ( $_GET ['imprimir'] )) {
	exit ();
}

// if (isset ( $_POST ['txtpagoefectivo'] )) {
// 	$pagoefectivo = str_replace ( ",", "", $_POST ['txtpagoefectivo'] );
// } else {
// 	$pagoefectivo = ($ovamount + $ovgst) -$alloc;
// }

$ocultarGenEfectivo = 'style="display: none;"';
$ocultarGenCheque = 'style="display: none;"';
$ocultarGenCredito = 'style="display: none;"';
$ocultarGenDebito = 'style="display: none;"';
$ocultarGenTransferencia = 'style="display: none;"';
$sql = "SELECT paymentid, paymentname 
		FROM paymentmethodssat WHERE paymentid in (".$codesat.") ";
	
$Result = DB_query ( $sql, $db );
$numRegistros2 = 0;
$myrow2 = "";

if (DB_num_rows ( $Result ) == 1) {
	$numRegistros2 = 1;
	$myrow2 = DB_fetch_array ( $Result );
	if ($myrow2['paymentid'] == 01) {
		//efectivo
		$pagoefectivo = ($ovamount + $ovgst) - $alloc;
	}elseif ($myrow2['paymentid'] == 02) {
		//cheque
		$txtpagocheque = ($ovamount + $ovgst) - $alloc;
	}elseif ($myrow2['paymentid'] == 04) {
		//tarjeta de credito
		$ocultarGenCredito = '';
		$txtpagotdc = ($ovamount + $ovgst) - $alloc;
	}elseif ($myrow2['paymentid'] == 28) {
		//tarejta de debito
		$ocultarGenDebito = '';
		$txtpagotdd = ($ovamount + $ovgst) - $alloc;
	}elseif ($myrow2['paymentid'] == 03) {
		//tarnsferecia
		$ocultarGenTransferencia = '';
		$txtpagotransferencia = ($ovamount + $ovgst) - $alloc;
	}
} else {

	$Result = DB_query ( $sql, $db );
	while ($myrow2 = DB_fetch_array ( $Result )) {
		if ($myrow2['paymentid'] == 01) {
			//efectivo
			$ocultarGenEfectivo = "";
		}elseif ($myrow2['paymentid'] == 02) {
			//cheque
			$ocultarGenCheque = '';
		}elseif ($myrow2['paymentid'] == 04) {
			//tarjeta de credito
			$ocultarGenCredito = '';
		}elseif ($myrow2['paymentid'] == 28) {
			//tarejta de debito
			$ocultarGenDebito = '';
		}elseif ($myrow2['paymentid'] == 03) {
			//tarnsferecia
			$ocultarGenTransferencia = '';
		}
	}
}

$EnvioXML = $rootpath . '/' . $SendInvoiceByMailFile . '?id=' . $_GET['id'];
$EnvioXML = "&nbsp;&nbsp;&nbsp;<a href=" . $EnvioXML . "  target='_blank'><img src='part_pics/Mail-Forward.png' alt='Reenviar SAT' border=0>" . _ ( 'Enviar Factura via Mail' ) . "</a>";
// echo "<br>";
echo "<div align='center' ".$style.">";
// echo "<table>";
// echo "<tr>";
// echo "<td colspan='3 style='text-align:center' >";
// echo $EnvioXML;
// echo "</td>";
// echo "</tr>";
// echo "</table>";
// echo "<br>";
echo "</div>";

echo '<div class="row"></div>';

$stylerenglon = "style='border: 0px;'";
echo "<div style='color: red;' id='mensajeError' align='center'></div>";
echo "<div style='color: blue;' id='mensajeProcesando' align='center'>Realizando el proceso, espera...</div>";

echo '<div class="row"></div>';

echo '<div class="col-xs-12 col-md-12">'; // Div Tabla

echo "<table class='table table-striped' cellpadding='0' cellspacing='0' style='border:0px solid;' ".$style2.">";
if (($aplica == 0) and (isset ( $_POST ['procesar'] ))) {
	echo "<tr><td colspan='5' style='color:red; border: 0px;'>" . _ ( 'No se asigno bien el monto...' ) . "</td></tr>";
}

//Solo Aqui
$FacturaVersion =$_SESSION['FacturaVersion'];
$styleOcultarServillantas = "";
if($_SESSION['DatabaseName'] == "gruposervillantas" or $_SESSION['DatabaseName'] =="gruposervillantas_CAPA" or $_SESSION['DatabaseName'] =="gruposervillantas_DES"){
	$FacturaVersion ="";
	$styleOcultarServillantas = "style='display: none;'";
}

// if($FacturaVersion == "3.3"){
	echo "<tr style='display: none;'><td colspan='5'><b>" . _ ( 'DATOS DE PAGO' ) . "</b></td></tr>";

	//***********************
	// Anticipo
	//***********************
	echo "<tr style='display: none;'>";
	// echo "<td " . $stylerenglon . "><b>" . _ ( 'Referencia Anticipo' ) . ":</b></td>";
	
	echo "<td " . $stylerenglon . "><b>" . _ ( 'Pago Anticipo' ) . ":</b></td>";
	echo "<td " . $stylerenglon . "><select class='form-control' name='pagoanticipo'>";

	$result2 = DB_query ( 'SELECT accountcode,accountname FROM chartmaster WHERE digitoagrupador IN ("102.01" , "102.02");', $db );

	while ( $myrow = DB_fetch_array ( $result2 ) ) {

		if ($_POST ['pagoanticipo'] == $myrow ['accountcode'] . "-" . $myrow ['accountname']) {
			echo "<option selected VALUE='" . $myrow ['accountcode'] . "-" . $myrow ['accountname'] . "'>" . $myrow ['accountname'] . "</option>";
		} else {
			echo "<option VALUE='" . $myrow ['accountcode'] . "-" . $myrow ['accountname'] . "'>" . $myrow ['accountname'] . "</option>";
		}
	}
	echo "</select></td>";
	echo "<td " . $stylerenglon . "><b></b></td>";
	echo "<td " . $stylerenglon . "><input class='form-control' type='text' name='txtanticipo' value='" . $_POST ['txtanticipo'] . "' size='35' maxlength='50' placeholder='Referencia Anticipo' title='Referencia Anticipo'></td>";
	echo "<td " . $stylerenglon . "><input class='form-control number' type='text' name='txtpagoanticipo' id='txtpagoanticipo' value='" . number_format ( $txtpagoanticipo, 2 ) . "' size='12' maxlength='12'></td>";
	echo "</tr>";

	//***********************
	//  Bancos
	//***********************
	echo "<tr style='display: none;'>";
	// echo "<td " . $stylerenglon . "><b>" . _ ( 'Referencia Bancos' ) . ":</b></td>";
	echo "<td " . $stylerenglon . "><b>" . _ ( 'Pago Bancos' ) . ":</b></td>";
	echo "<td " . $stylerenglon . "><select class='form-control' name='pagobancos'>";
	$result2 = DB_query ( 'SELECT accountcode,accountname FROM chartmaster WHERE digitoagrupador IN ("102.01" , "102.02");', $db );
	while ( $myrow = DB_fetch_array ( $result2 ) ) {

		if ($_POST ['pagobancos'] == $myrow ['accountcode'] . "-" . $myrow ['accountname']) {
			echo "<option selected VALUE='" . $myrow ['accountcode'] . "-" . $myrow ['accountname'] . "'>" . $myrow ['accountname'] . "</option>";
		} else {
			echo "<option VALUE='" . $myrow ['accountcode'] . "-" . $myrow ['accountname'] . "'>" . $myrow ['accountname'] . "</option>";
		}
	}
	echo "</select></td>";
	echo "<td " . $stylerenglon . "><b></b></td>";
	echo "<td " . $stylerenglon . "><input class='form-control' type='text' name='txtbancos' value='" . $_POST ['txtbancos'] . "' size='35' maxlength='50' placeholder='Referencia Bancos' title='Referencia Bancos'></td>";
	echo "<td " . $stylerenglon . "><input class='form-control number' type='text' name='txtpagobancos' id='txtpagobancos' value='" . number_format ( $txtpagobancos, 2 ) . "' size='12' maxlength='12'></td>";
	echo "</tr>";
// }

// echo "<tr ".$style."><td colspan='5'><b>" . _ ( 'CORTE DE CAJA' ) . "</b></td></tr>";

//***********************
//  Efectivo
//***********************
?>



<?php
if ($permisoEdicionFecha == 1){
echo "<tr>";
echo "<td " . $stylerenglon . "><component-date-label label='Fecha: ' type='date' name='txtFecha' value='<?php echo $fecha ;?>' size='10' ></component-date-label></td>";
echo "</tr>";
}else{
	echo "<tr style='display: none;' >";
	echo "<td " . $stylerenglon . "><component-date-label label='Fecha: ' type='date' name='txtFecha' value='<?php echo $fecha ;?>' size='10' ></component-date-label></td>";
	echo "</tr>";
}
echo "<tr ".$ocultarGenEfectivo.">";
// echo "<td " . $stylerenglon . "><b>" . _ ( 'Referencia Efectivo' ) . ":</b></td>";
echo "<td " . $stylerenglon . "><b>" . _ ( 'Pago Efectivo' ) . ":</b></td>";
echo "<td " . $stylerenglon . "><select class='form-control' name='pagoefectivo' id='pagoefectivo'>";
$result2 = DB_query ( 'SELECT accountcode,accountname
			  FROM chartmaster, accountgroups,companies 
				WHERE chartmaster.group_=accountgroups.groupname AND accountgroups.pandl=0
				and (chartmaster.accountcode = companies.gltempcashpayment)
				ORDER BY chartmaster.accountcode', $db );
while ( $myrow = DB_fetch_array ( $result2 ) ) {
	if ($_POST ['pagoefectivo'] == $myrow ['accountcode'] . "-" . $myrow ['accountname']) {
		echo "<option selected VALUE='" . $myrow ['accountcode'] . "-" . $myrow ['accountname'] . "'>" . $myrow ['accountname'] . "</option>";
	} else {
		echo "<option VALUE='" . $myrow ['accountcode'] . "-" . $myrow ['accountname'] . "'>" . $myrow ['accountname'] . "</option>";
	}
}
echo "</select>";
echo "</td>";
echo "<td " . $stylerenglon . "><b></b></td>";
echo "<td " . $stylerenglon . "><input class='form-control' type='text' name='txtrefefectivo' value='" . $_POST ['txtrefefectivo'] . "' size='35' maxlength='50' placeholder='Referencia Efectivo' title='Referencia Efectivo'></td>";
echo "<td " . $stylerenglon . "><input class='form-control number' type='text' name='txtpagoefectivo' id='txtpagoefectivo'  value='" . number_format ( $pagoefectivo, 2, '.', '' ) . "' size='12' maxlength='12'></td>";
echo "</tr>";
?>

<script>

$('#txtpagoefectivo').on('change', function() {

	if (this.value == 0.00){
		var datos = '0.00';
		$("#txtpagoefectivo").val(""+datos);
	}else{
		console.log(this.value);
	}
  
});


// var pagoEfec=document.getElementById("txtpagoefectivo").value;

// if (pagoEfec == 0.00) {
// 	var datos = 0.00;
// 	$("html").click(function() {
// 		$("#txtpagoefectivo").val(""+datos);
// 	});
// }else{
// 	console.log(pagoEfec);
// }
</script>

<?php
//***********************
//  Cheque
//***********************
echo "<tr ".$ocultarGenCheque.">";
// echo "<td " . $stylerenglon . "><b>" . _ ( 'Referencia Cheque' ) . ":</b></td>";
echo "<td " . $stylerenglon . "><b>" . _ ( 'Pago Cheque' ) . ":</b></td>";
echo "<td " . $stylerenglon . "><select class='form-control' name='pagocheque' id='pagocheque'>";
$result2 = DB_query ( 'SELECT accountcode,accountname
			  FROM chartmaster, accountgroups,companies 
				WHERE chartmaster.group_=accountgroups.groupname AND accountgroups.pandl=0
				and (chartmaster.accountcode = companies.gltempcheckpayment)
				ORDER BY chartmaster.accountcode', $db );
while ( $myrow = DB_fetch_array ( $result2 ) ) {
	if ($_POST ['pagocheque'] == $myrow ['accountcode'] . "-" . $myrow ['accountname']) {
		echo "<option selected VALUE='" . $myrow ['accountcode'] . "-" . $myrow ['accountname'] . "'>" . $myrow ['accountname'] . "</option>";
	} else {
		echo "<option VALUE='" . $myrow ['accountcode'] . "-" . $myrow ['accountname'] . "'>" . $myrow ['accountname'] . "</option>";
	}
}
echo "</select></td>";
echo "<td " . $stylerenglon . "><b></b></td>";
echo "<td " . $stylerenglon . "><input class='form-control' type='text' name='txtrefcheque' value='" . $_POST ['txtrefcheque'] . "' size='35' maxlength='50' placeholder='Referencia Cheque' title='Referencia Cheque'></td>";
echo "<td " . $stylerenglon . "><input class='form-control number' type='text' name='txtpagocheque' id='txtpagocheque' value='" . number_format ( $txtpagocheque, 2, '.', '' ) . "' size='12' maxlength='12'></td>";
echo "</tr>";

?>

<script>

$('#txtpagocheque').on('change', function() {

if (this.value == 0.00){
	var datos = '0.00';
	$("#txtpagocheque").val(""+datos);
}else{
	console.log(this.value);
}

});

</script>

<?php
//***********************
//  TD Credito
//***********************
$SQLBancos = "SELECT DISTINCT accountcode, CONCAT(bankaccountnumber,' - ',bankaccountname) as bankaccountname 
FROM bankaccounts 
WHERE nu_activo = 1
ORDER BY nu_orden, bankaccountnumber, bankaccountname ASC";
$resultBancos = DB_query ($SQLBancos,$db);
		$cmbBancostdc = "<option value='-1' selected>Seleccionar...</option>";
		$cmbBancostdb = "<option value='-1' selected>Seleccionar...</option>";
		$cmbBancostrans = "<option value='-1' selected>Seleccionar...</option>";
while ( $myrow = DB_fetch_array ( $resultBancos ) ) {

	$ListCount=DB_num_rows($resultBancos);
	if($ListCount == 1){
		$cmbBancostdc .= "<option value='".$myrow['accountcode']."' selected>".$myrow['bankaccountname']."</option>";
		$cmbBancostdb .= "<option value='".$myrow['accountcode']."' selected>".$myrow['bankaccountname']."</option>";
		$cmbBancostrans .= "<option value='".$myrow['accountcode']."' selected>".$myrow['bankaccountname']."</option>";
	}

	if($ListCount > 1){
		$cmbBancostdc .= "<option value='".$myrow['accountcode']."' >".$myrow['bankaccountname']."</option>";
		$cmbBancostdb .= "<option value='".$myrow['accountcode']."' >".$myrow['bankaccountname']."</option>";
		$cmbBancostrans .= "<option value='".$myrow['accountcode']."' >".$myrow['bankaccountname']."</option>";
	}

	
}

echo "<tr ".$ocultarGenCredito.">";
// echo "<td " . $stylerenglon . "><b>" . _ ( 'Referencia TDC' ) . ":</b></td>";
echo "<td " . $stylerenglon . "><b>" . _ ( 'Pago TD Crédito' ) . ":</b></td>";
echo "<td " . $stylerenglon . "><select class='form-control' name='pagotdc' id='pagotdc'>";
$result2 = DB_query ( 'SELECT accountcode,accountname
			  FROM chartmaster, accountgroups,companies 
				WHERE chartmaster.group_=accountgroups.groupname AND accountgroups.pandl=0
				and (chartmaster.accountcode = companies.gltempccpayment)
				ORDER BY chartmaster.accountcode', $db );
while ( $myrow = DB_fetch_array ( $result2 ) ) {
	if ($_POST ['pagocheque'] == $myrow ['accountcode'] . "-" . $myrow ['accountname']) {
		echo "<option selected VALUE='" . $myrow ['accountcode'] . "-" . $myrow ['accountname'] . "'>" . $myrow ['accountname'] . "</option>";
	} else {
		echo "<option VALUE='" . $myrow ['accountcode'] . "-" . $myrow ['accountname'] . "'>" . $myrow ['accountname'] . "</option>";
	}
}
echo "</select></td>";
// select de bancos
echo "<td " . $stylerenglon . "><select class='form-control' name='cmbBancostdc' id='cmbBancostdc'>";
echo $cmbBancostdc;
echo "</select></td>";
echo "<td " . $stylerenglon . "><input class='form-control' type='text' name='txtreftdc' value='" . $_POST ['txtreftdc'] . "' size='35' maxlength='50' placeholder='Referencia TD Crédito' title='Referencia TD Crédito'></td>";
echo "<td " . $stylerenglon . "><input class='form-control number' type='text' name='txtpagotdc' id='txtpagotdc' value='" . number_format ( $txtpagotdc, 2, '.', '' ) . "' size='12' maxlength='12'></td>";
echo "</tr>";

?>
<script>

$('#txtpagotdc').on('change', function() {

if (this.value == 0.00){
	var datos = '0.00';
	$("#txtpagotdc").val(""+datos);
}else{
	console.log(this.value);
}

});
</script>

<?php
//***********************
//  TD Debito
//***********************


echo "<tr ".$ocultarGenDebito.">";
// echo "<td " . $stylerenglon . "><b>" . _ ( 'Referencia TDD' ) . ":</b></td>";
echo "<td " . $stylerenglon . "><b>" . _ ( 'Pago TD Débito' ) . ":</b></td>";
echo "<td " . $stylerenglon . "><select class='form-control' name='pagotdd' id='pagotdd'>";
$result2 = DB_query ( 'SELECT accountcode,accountname
                        FROM chartmaster, accountgroups,companies 
                        WHERE chartmaster.group_=accountgroups.groupname AND accountgroups.pandl=0
                        and (chartmaster.accountcode = companies.gltempccpayment)
                        ORDER BY chartmaster.accountcode', $db );

while ( $myrow = DB_fetch_array ( $result2 ) ) {
    if ($_POST ['pagocheque'] == $myrow ['accountcode'] . "-" . $myrow ['accountname']) {
        echo "<option selected VALUE='" . $myrow ['accountcode'] . "-" . $myrow ['accountname'] . "'>" . $myrow ['accountname'] . "</option>";
    } else {
        echo "<option VALUE='" . $myrow ['accountcode'] . "-" . $myrow ['accountname'] . "'>" . $myrow ['accountname'] . "</option>";
    }
}
echo "</select></td>";
// select de bancos
echo "<td " . $stylerenglon . "><select class='form-control' name='cmbBancostdb' id='cmbBancostdb'>";
echo $cmbBancostdb;
echo "</select></td>";
echo "<td " . $stylerenglon . "><input class='form-control' type='text' name='txtreftdd' value='" . $_POST ['txtreftdd'] . "' size='35' maxlength='50' placeholder='Referencia TD Débito' title='Referencia TD Débito'></td>";
echo "<td " . $stylerenglon . "><input class='form-control number' type='text' name='txtpagotdd' id='txtpagotdd' value='" . number_format ( $txtpagotdd, 2, '.', '' ) . "' size='12' maxlength='12'></td>";
echo "</tr>";

?>
<script>

$('#txtpagotdd').on('change', function() {

if (this.value == 0.00){
	var datos = '0.00';
	$("#txtpagotdd").val(""+datos);
}else{
	console.log(this.value);
}

});

</script>

<?php
//***********************
//  Transferencia
//***********************
echo "<tr ".$ocultarGenTransferencia.">";
// echo "<td " . $stylerenglon . "><b>" . _ ( 'Referencia Transferencia' ) . ":</b></td>";
echo "<td " . $stylerenglon . "><b>" . _ ( 'Pago Transferencia' ) . ":</b></td>";
echo "<td " . $stylerenglon . "><select class='form-control' name='pagotransferencia' id='pagotransferencia'>";
$result2 = DB_query ( 'SELECT accountcode,accountname
			  FROM chartmaster, accountgroups,companies 
				WHERE chartmaster.group_=accountgroups.groupname AND accountgroups.pandl=0
				and (chartmaster.accountcode = companies.gltemptransferpayment)
				ORDER BY chartmaster.accountcode', $db );
while ( $myrow = DB_fetch_array ( $result2 ) ) {
	if ($_POST ['pagocheque'] == $myrow ['accountcode'] . "-" . $myrow ['accountname']) {
		echo "<option selected VALUE='" . $myrow ['accountcode'] . "-" . $myrow ['accountname'] . "'>" . $myrow ['accountname'] . "</option>";
	} else {
		echo "<option VALUE='" . $myrow ['accountcode'] . "-" . $myrow ['accountname'] . "'>" . $myrow ['accountname'] . "</option>";
	}
}
echo "</select></td>";
// select de bancos
echo "<td " . $stylerenglon . "><select class='form-control' name='cmbBancostrans' id='cmbBancostrans'>";
echo $cmbBancostrans;
echo "</select></td>";
echo "<td " . $stylerenglon . "><input class='form-control' type='text' name='txtreftransferencia' value='" . $_POST ['txtreftransferencia'] . "' size='35' maxlength='50' placeholder='Referencia Transferencia' title='Referencia Transferencia'></td>";
echo "<td " . $stylerenglon . "><input class='form-control number' type='text' name='txtpagotransferencia' id='txtpagotransferencia' value='" . number_format ( $txtpagotransferencia, 2, '.', '' ) . "' size='12' maxlength='12'></td>";
echo "</tr>";
?>
<script>

$('#txtpagotransferencia').on('change', function() {

if (this.value == 0.00){
	var datos = '0.00';
	$("#txtpagotransferencia").val(""+datos);
}else{
	console.log(this.value);
}

});

</script>

<?php

echo "</table>";

echo '</div>'; // Div Tabla

echo "<div align='center'>";
$styleBoton = "";
if ($numRegistros2 == 1) {
	$styleBoton = " style='display: none;' ";
}
echo "<input type='button' name='procesar' id='procesar' value='PROCESAR' onclick='validador()' class='btn btn-primary btn-lg' ".$styleBoton." />";
if (Havepermission ( $_SESSION ['UserID'], 2129, $db )) {
	// echo "&nbsp&nbsp&nbsp<a class='btn' href='".$rootpath."/SelectOrderItemsV6_0.php'>OMITIR RECIBO</a>";
	echo "&nbsp&nbsp&nbsp&nbsp&nbsp<input type='button' onclick=location.href='".$rootpath."/SelectOrderItemsV6_0.php' value='SALIR DEL PEDIDO'>";
}
echo "<input type='hidden' name='transnofac' value='" . $transnofac . "'>";
echo "<input type='hidden' name='debtorno' value='" . $debtorno . "'>";
echo "<input type='hidden' name='branchcode' value='" . $branchcode . "'>";
echo "<input type='hidden' name='tag' value='" . $tag . "'>";
echo "<input type='hidden' name=shippinglogid value=" . $_POST['shippinglogid'] . ">";
echo "<input type='hidden' name=shippingno value=" . $_POST['shippingno'] . ">";
echo "<input type='hidden' name='typeinvoice' value='" . $typeinvoice . "'>";
//input saber si esta en el punto de venta
echo "<input type='hidden' name=txtPV value='" . $pv1 . "'>";
echo "</div>";
echo "<br><br>";
/*
 * FIN FORMA DONDE SE MUESTRAN LOS DATOS A CAPTURA
 */

echo '</form>';

if ($_GET['pv'] == 'pv1' or $_POST['txtPV'] == "pv1") {
	//quitar datos si se visualiza en el punto de venta, y centrar informacion
	$pv1 = "pv1";
	$style = "style='display: none;'";
	$style2 = "align='center'";
	$styleDisplay = "display: none;";
	$_GET['modal'] = true;
}else{
	// mostrar cuando no es en el punto de venta
	// include 'includes/footer_Index.inc';
}
include 'includes/footer_Index.inc';
?>

<script type="text/javascript">
	var errorGenRecibo = 0;
	$( document ).ready(function() {
		fnFormatoSelectGeneral("#pagoefectivo");
		fnFormatoSelectGeneral("#pagocheque");
		fnFormatoSelectGeneral("#pagotdc");
		fnFormatoSelectGeneral("#pagotdd");
		fnFormatoSelectGeneral("#pagotransferencia");

		fnFormatoSelectGeneral("#cmbBancostdc");
		fnFormatoSelectGeneral("#cmbBancostdb");
		fnFormatoSelectGeneral("#cmbBancostrans");
	});
	function validador(){
		var hdntotal = document.getElementById("hdntotal");

		var txtpagoefectivo = document.getElementById("txtpagoefectivo");
		var txtpagocheque = document.getElementById("txtpagocheque");
		var txtpagotdc = document.getElementById("txtpagotdc");
		var txtpagotdd = document.getElementById("txtpagotdd");
		var txtpagotransferencia = document.getElementById("txtpagotransferencia");

		var txtpagoanticipo = document.getElementById("txtpagoanticipo");
		var txtpagobancos = document.getElementById("txtpagobancos");

		var cmbBancostdc = document.getElementById("cmbBancostdc");
		var cmbBancostdb = document.getElementById("cmbBancostdb");
		var cmbBancostrans = document.getElementById("cmbBancostrans");

		var total = parseFloat(hdntotal.value.replace(",", ""));
		console.log(Number(total).toFixed(2));
		total = Number(total).toFixed(2);
		var totalIngresado = parseFloat(txtpagoefectivo.value.replace(",", ""))+parseFloat(txtpagocheque.value.replace(",", ""))+parseFloat(txtpagotdc.value.replace(",", ""))+parseFloat(txtpagotdd.value.replace(",", ""))+parseFloat(txtpagotransferencia.value.replace(",", "")) + parseFloat(txtpagoanticipo.value.replace(",", "")) + parseFloat(txtpagobancos.value.replace(",", ""));
		totalIngresado = totalIngresado.toFixed(2);
		console.log(totalIngresado);

		// console.log("txtpagotdc: "+parseFloat(txtpagotdc.value.replace(",", "")));
		// console.log("txtpagotdd: "+parseFloat(txtpagotdd.value.replace(",", "")));
		// console.log("txtpagotransferencia: "+parseFloat(txtpagotransferencia.value.replace(",", "")));

		if ((parseFloat(txtpagotdc.value.replace(",", "")) > Number(0)) && cmbBancostdc.value == '-1') {
			document.getElementById("mensajeError").innerHTML = "Seleccionar cuenta para Tarjeta de Crédito";
			return false;
		}

		if ((parseFloat(txtpagotdd.value.replace(",", "")) > Number(0)) && cmbBancostdb.value == '-1') {
			document.getElementById("mensajeError").innerHTML = "Seleccionar cuenta para Tarjeta de Débito";
			return false;
		}

		if ((parseFloat(txtpagotransferencia.value.replace(",", "")) > Number(0)) && cmbBancostrans.value == '-1') {
			document.getElementById("mensajeError").innerHTML = "Seleccionar cuenta para Transferencia";
			return false;
		}

		document.getElementById("mensajeProcesando").innerHTML = "";

		console.log("total: "+Number(total)+" - totalIngresado: "+Number(totalIngresado));
		// return true;

		if (Number(total) == Number(totalIngresado)) {
			//mensajes
			document.getElementById("mensajeError").innerHTML = "";
			document.getElementById("mensajeProcesando").innerHTML = "Realizando el proceso, espera...";
			//genera recibos
			var btn = document.getElementById("procesar");
			document.getElementById("procesar").type="submit";
			btn.click();
		}else{
			//cantidad erronea
			document.getElementById("mensajeError").innerHTML = "La cantidad ingresada no es igual al total, verificar la cantidad ";
		}
	}

	$( document ).ready(function() {
		var numRegistros2 = "<?php echo $numRegistros2; ?>";
		var val = "<?php echo $myrow2['paymentid']; ?>";
		// setTimeout(function (){
			// Esperar 5 seg, para procesar
			if(numRegistros2 == 1 && (val == 01 || val == 02)) {
				$("#procesar").click();
			} else {
				document.getElementById("mensajeProcesando").innerHTML = "";
				 $("#procesar").css("display", "inline"); 
			}
			// if(numRegistros2 == 1 && val == 02){
			// 	$("#procesar").click();
			// }
		// }, 5000);
	});
</script>