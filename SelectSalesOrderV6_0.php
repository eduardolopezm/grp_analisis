<?php
$XSAInvoicing = "XSAInvoicing.inc";
$SendInvoicing = "SendInvoicingV6_0.php";
$PageSecurity = 5;
$funcion = 602;
include "includes/SecurityUrl.php";
include ('includes/session.inc');
//Variable para desplegar todoas las notas de admin

$debug=false;
$title = traeNombreFuncion($funcion, $db);

include ('includes/header.inc');
include ('includes/SecurityFunctions.inc');
include ('includes/SQL_CommonFunctions.inc');


include('javascripts/libreriasGrid.inc');

// Subir a prod. 
/*
 * CGM 12/09/2013 Alta de version con redise�o de manejo de status, fechas de actualizacion,
 * se agrega formato impreso con uso de xml e interpretacion de jasper
 *
 */

if($_SESSION['UserID'] == "desarrollo"){
	//ini_set('display_errors', 1);
	//ini_set('log_errors', 1);
	//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
	//error_reporting(E_ALL);
}

/**
 * **************************************
 */
// Archivos utilizados en esta pagina
/**
 * **************************************
 */

include ('Numbers/Words.php');
// para cuentas referenciadas
include ('includes/Functions.inc');
include ('includes/' . $XSAInvoicing);
include ('includes/' . $SendInvoicing);
// regeneracion de XML
// Incluye la funci�n emailNotifier
require_once ('includes/DeliveryEmailNotifier.inc');

//------- Envio Correo Factura
include 'includes/FreightCalculation.inc';
include 'includes/GetSalesTransGLCodes.inc';
include 'XSAInvoicing3.inc';
include_once 'phpqrcode/qrlib.php';
// para el llamado al webservice
//include_once 'lib/nusoap.php';
$PaperSize = 'letter';
// para validar correo
include 'includes/MiscFunctions.inc';
//------- Envio Correo Factura

/**
 * ***********************************
 */ 
// Permisos utilizados en esta pagina
/**
 * ***********************************
 */
 
// se envía a dace
// Se hizo correción para poder cancelar una factura de remisión
// Cambios para el nuevo esquema de cancelación 

$altapedido = Havepermission ( $_SESSION ['UserID'], 171, $db );
$paginapedidos = HavepermissionURL ( $_SESSION ['UserID'], 4, $db );

$paginabusquedapedidos = HavepermissionURL ( $_SESSION ['UserID'], 602, $db );
$oportunidadprospectoPermiso = Havepermission ( $_SESSION ['UserID'], 2210, $db );
$oportunidadprospecto = HavepermissionURL ( $_SESSION ['UserID'], 2210, $db );
$permisouser = Havepermission ( $_SESSION ['UserID'], 860, $db );
$permisoremision = Havepermission ( $_SESSION ['UserID'], 224, $db );
$permisocancelar = Havepermission ( $_SESSION ['UserID'], 196, $db );
$enviaventaperdida = Havepermission ( $_SESSION ['UserID'], 203, $db );
$cancelarfactura = Havepermission ( $_SESSION ['UserID'], 177, $db );
$modificartrabajadores = Havepermission ( $_SESSION ['UserID'], 302, $db );
$modificarvendedores = Havepermission ( $_SESSION ['UserID'], 985, $db );
$permisoaperturacerrado = Havepermission ( $_SESSION ['UserID'], 217, $db );
$add_datosfactura = Havepermission ( $_SESSION ['UserID'], 3014, $db );
$mod_datosfactura = Havepermission ( $_SESSION ['UserID'], 3014, $db );
$permisoimprimeservicio = Havepermission ( $_SESSION ['UserID'], 546, $db );
$Exportaexcel = Havepermission ( $_SESSION ['UserID'], 1290, $db );
$permisoCambiarUsuario = Havepermission ( $_SESSION ['UserID'], 1263, $db );
$permisodesbloqueapedido = Havepermission ( $_SESSION ['UserID'], 1396, $db );
$permisoCambiarVehicle = Havepermission ( $_SESSION ['UserID'], 1282, $db ); // agregar
$permiso_ticket_fiscal = Havepermission ( $_SESSION ['UserID'], 224, $db ); // agregar


$permisovertodasfacturas = Havepermission ( $_SESSION ['UserID'], 1920, $db ); // permite ver todas las facturas que se generan de un solo pedido.

//mostrar bombeo y proveedor
$PerMosBombeoProveedor = Havepermission ( $_SESSION ['UserID'], 1963, $db );
$permisoLogPartida = Havepermission($_SESSION ['UserID'], 1998, $db); // Ver log pedido
$permisoEnviarCorreo = Havepermission($_SESSION ['UserID'], 2003, $db); // Enviar todas la facturas seleccionadas
$permisoSustitucion = Havepermission($_SESSION ['UserID'], 2126, $db); // Enviar todas la facturas seleccionadas
$permisoEdicion = Havepermission($_SESSION ['UserID'], 2523, $db); // Poder editar comentario
/**
 * ***********************************
 */
$noSustitucion = 0; 
if ($altapedido == 1) {
	echo '<p><a href="' . $rootpath . '/' . $paginapedidos . '?' . SID . '&NewOrder=Yes"><font size=2><b>' . _ ( 'Nuevo Pedido de Venta:' ) . '</a>';
}

unset ( $listaloccxbussines );
/* verifica sus unidades de negocio */
$sql = "SELECT t.tagref, t.tagdescription
		FROM tags t, sec_unegsxuser uxu
		WHERE t.tagref=uxu.tagref
		AND uxu.userid='" . $_SESSION ['UserID'] . "'
	   ORDER BY tagdescription";
$resultTags = DB_query ( $sql, $db, '', '' );
$listaloccxbussines = array ();
$counter_bussines = 0;
while ( $myrow_bussines = DB_fetch_array ( $resultTags ) ) {
	$listaloccxbussines [$counter_bussines] = $myrow_bussines ['tagdescription'];
	$counter_bussines = $counter_bussines + 1;
}



/*
 * INICIO *RECUPERO LOS VALORES QUE VIENEN EN EL URL, ESTO SE APLICA CUANDO SE LLAMA * LA OPCION DE ELIMINAR ALGUN RECIBO
 */

	if (isset ( $_GET ['txtfechadesde'] )) {
		$_POST ['txtfechadesde'] = $_GET ['txtfechadesde'];
	}
	if (isset ( $_GET ['txtfechahasta'] )) {
		$_POST ['txtfechahasta'] = $_GET ['txtfechahasta'];
	}

/* FIN */

if (isset ( $_POST ['txtfechadesde'] )) {
	$fechadesde = $_POST ['txtfechadesde'];
	$fechadesde2 = date('Y/m/d',strtotime($_POST ['txtfechadesde']));
} else {
	$fechadesde2 = date ( 'Y' ) . "/" . date ( 'm' ) . "/" . date ( 'd' );
	$fechadesde = date ( 'd' ) . "-" . date ( 'm' ) . "-" . date ( 'Y' );
	// $fechadesde = date('m') . "/" . "23" . "/" . date('Y');
}


if (isset ( $_POST ['txtfechahasta'] )) {
    $fechahasta = $_POST ['txtfechahasta'];
    $fechahasta2 = date('Y/m/d',strtotime($_POST ['txtfechahasta']));
} else {
    $fechahasta2 = date ( 'Y' ) . "/" . date ( 'm' ) . "/" . date ( 'd' );
	$fechahasta = date ( 'd' ) . "-" . date ( 'm' ) . "-" . date ( 'Y' );
	// $fechahasta = date('m') . "/" . "23" . "/" . date('Y');
}


// echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/sales.png" title="' . _('Pedidos de Ventas') . '" alt="">' . ' ' . _('BUSQUEDA DE PEDIDOS DE VENTA') . '</p> ';
// Tabla para el titulo de la pagina

if (isset ( $_POST ['FromYear'] )) {
    $FromYear = $_POST ['FromYear'];
} elseif (isset ( $_GET ['FromYear'] )) {
    $FromYear = $_GET ['FromYear'];
} else {
    $FromYear = date ( 'Y' );
}
if (isset ( $_POST ['FromMes'] )) {
	$FromMes = $_POST ['FromMes'];
} elseif (isset ( $_GET ['FromMes'] )) {
	$FromMes = $_GET ['FromMes'];
} else {
	$FromMes = date ( 'm' );
}
if (isset ( $_GET ['FromDia'] )) {
	$FromDia = $_GET ['FromDia'];
} elseif (isset ( $_POST ['FromDia'] )) {
	$FromDia = $_POST ['FromDia'];
} else {
	$FromDia = date ( 'd' );
}
if (isset ( $_POST ['ToYear'] )) {
	$ToYear = $_POST ['ToYear'];
} elseif (isset ( $_GET ['ToYear'] )) {
	$ToYear = $_GET ['ToYear'];
} else {
	$ToYear = date ( 'Y' );
}
if (isset ( $_POST ['ToMes'] )) {
	$ToMes = $_POST ['ToMes'];
} elseif (isset ( $_GET ['ToMes'] )) {
	$ToMes = $_GET ['ToMes'];
} else {
	$ToMes = date ( 'm' );
}
if (isset ( $_GET ['ToDia'] )) {
	$ToDia = $_GET ['ToDia'];
} elseif (isset ( $_POST ['ToDia'] )) {
	$ToDia = $_POST ['ToDia'];
} else {
	$ToDia = date ( 'd' );
}
if (isset ( $_POST ['UnidNeg'] )) {
	$UnidNeg = $_POST ['UnidNeg'];
} elseif (isset ( $_GET ['UnidNeg'] )) {
	$UnidNeg = $_GET ['UnidNeg'];
}
else
{
	$UnidNeg = "";
}
if (isset ( $_POST ['SalesMan'] )) {
	$SalesMan = $_POST ['SalesMan'];
} elseif (isset ( $_GET ['SalesMan'] )) {
	$SalesMan = $_GET ['SalesMan'];
}
else
{
	$SalesMan = "";
}
if (isset ( $_POST ['UserName'] )) {
	$UserName = $_POST ['UserName'];
} elseif (isset ( $_GET ['UserName'] )) {
	$UserName = $_GET ['UserName'];
}

if (isset ( $_POST ['OrderNumber'] )) {
	$orderNumber = $_POST ['OrderNumber'];
} elseif (isset ( $_GET ['OrderNumber'] )) {
	$orderNumber = $_GET ['OrderNumber'];
}else{
	$orderNumber = "";
}

if ($_POST ['Quotations'][0]  == '-1') {
	
	$Quotations = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13);
	
} else {
	$Quotations = $_POST ['Quotations'];

}
// var_dump($Quotations);
if(isset($_GET['fgvitalizado'])){
    $fgvitalizado=$_GET['fgvitalizado'];
}else{
    $fgvitalizado=0;
}

//-- Clasificación de cotizaciones solo para servillantas
if ($_SESSION ['DatabaseName'] == "gruposervillantas_CAPA" || $_SESSION ['DatabaseName'] == "gruposervillantas_DES" || $_SESSION ['DatabaseName'] == "gruposervillantas"){

	if (isset ( $_POST ['txtCasificacionCotizacion'] )) {
		//-- Si se actualizó un solo registro en campo de la clase de cotización
		
		$xArray = explode('|', $_POST ['txtCasificacionCotizacion']); //-- Parto todos los pair de orden@clasificacion
		//echo "<br/><pre> array: ";
		//print_r($xArray);

		foreach($xArray as $valores){
		//echo "<br/> Valores: "; 
			//print_r($valores);
    		if (trim($valores) != "") {
	    		$ordenTipo = explode('@', trim($valores)); 
	    		$SQL = "UPDATE salesorders
					SET extratext = '" . $ordenTipo[1] . "'
					WHERE orderno= " . $ordenTipo[0] ;
		//		echo "<br/> SQL:" . $SQL;
				$ErrMsg = _ ( 'ERROR CRITICO' ) . ' ' . _ ( 'ANOTE EL ERROR' ) . ': ' . _ ( 'No se pudo guardar tipo de cotizacion.' );
				$DbgMsg = _ ( 'El siguiente SQL se utilizo para actualizar el pedido de venta' );
				$Result = DB_query ( $SQL, $db, $ErrMsg, $DbgMsg, true );
			}
	    }
	}
}

if (isset ( $_POST ['action'] )) {
	$action = $_POST ['action'];
} elseif (isset ( $_GET ['action'] )) {
	$action = $_GET ['action'];
}

$fechaini = $fechadesde2;
$fechafin = $fechahasta2 . ' 23:59:59';
$fechainic = mktime ( 0, 0, 0, $fechadesde2);
$fechafinc = mktime ( 0, 0, 0, $fechahasta2);
$InputError = 0;


if ($fechainic > $fechafinc) {
	$InputError = 1;
	prnMsg ( _ ( 'La fecha inicial no puede ser mayor a la fecha final! Por favor seleccionar nuevamente los periodos de fecha' ), 'error' );
} else {
	$InputError = 0;
}

if(!isset($_GET ['orderno'])){ $orden 		=""; 	}else{ $orden 		= $_GET ['orderno']; }
if(!isset($_GET ['tagrefen'])){ $tagrefen 	=""; 	}else{ $tagrefen 	= $_GET ['tagrefen']; }
if(!isset($_GET ['folio'])){ $folio 		="";
}else{ $folio 		= $_GET ['folio']; }
if(!isset($_GET ['serie'])){ $serie 		=""; 	}else{ $serie 		= $_GET ['serie']; }
if(!isset($_GET ['debtorno'])){ $debtorno 	=""; 	}else{ $debtorno 	= $_GET ['debtorno']; }
if(!isset($_GET ['tipo'])){ 	$tipo 		=""; 	}else{ $tipo 		= $_GET ['tipo']; }
if(!isset($_GET ['transno'])){ $transno =""	; 	}else{ $transno 	= $_GET ['transno']; }
if(!isset($_GET ['iddocto'])){ $iddocto 	; 	}else{ $iddocto 	= $_GET ['iddocto']; }
if(!isset($_GET ['ventaperdida'])){ $ventaperdida ; }else{ $ventaperdida = $_GET ['ventaperdida']; }

/**
 * **************************************
 */
echo '<form id="frmBusqueda" action=' . $_SERVER ['PHP_SELF'] . '?' . SID . ' method=post>';
echo '<input type=hidden name="fechaini" id="fechaini" VALUE="' . $fechaini . '">
		<input type=hidden name="fechafin" id="fechafin" VALUE="' . $fechafin . '">
		<input type="hidden" id="txtCasificacionCotizacion" name="txtCasificacionCotizacion">';
echo '<p><div class="centre">';
/**
 * **************************************
 */
// Operaciones en base de datos
/**
 * **************************************
 */

// Cancelacion de pedido de venta
//echo 'Orden: ' . $orden . ' Folio: ' . $folio . '<br />';
// echo 'afuera';
if (isset ( $orden ) and empty( $folio )) {

	$SQL = "SELECT *
			FROM salesorders
			WHERE salesorders.orderno = '" . $orden . "'
			AND salesorders.quotation in (0,1,2,7)";
	
	$result = DB_query ( $SQL, $db );
	if (DB_num_rows ( $result ) > 0) {
		// Inicializo transaccion
		
		$Result = DB_Txn_Begin ( $db );
		$myrow = DB_fetch_array ( $result );
		$qry = "SELECT wo, costissued FROM workorders WHERE orderno = '" . $orden . "'";
		$rsw = DB_query ( $qry, $db );

		if (Db_num_rows ( $rsw ) > 0) {
			$resultw = DB_Txn_Begin ( $db );
			while ( $regs = DB_fetch_array ( $rsw ) ) {
				if ($regs["costissued"] > 0.01) {
					prnMsg (_("El pedido no se puede cancelar porque tiene la OT: ".$regs["wo"]." con productos Emitidos."), 'error');
					break;
				}
				$sql = "SELECT stockmaster.description as producto, qtyreqd, qtyrecd
						FROM woitems 
						INNER JOIN stockmaster ON woitems.stockid = stockmaster.stockid
						WHERE wo = " . $regs [0] . " and (qtyrecd = 0 or qtyrecd is null)";

				$rsx = DB_query ( $sql, $db );
				
				if (DB_num_rows ( $rsx ) > 0) {
					$sqlcalendar = "SELECT *
							FROM mrp_calendar_AM_detail
							WHERE mrp_calendar_AM_detail.serie = '" . $regs [0] . "'";
					$resultcalendar = DB_query ( $sqlcalendar, $db );
					if (DB_num_rows ( $resultcalendar ) > 0) {
						while ( $myrowesquema = DB_fetch_array ( $resultcalendar ) ) {
							$sqlesquematizado = "DELETE
									FROM mrp_calendar_AM_detail
									WHERE mrp_calendar_AM_detail.serie = '" . $myrowesquema ['serie'] . "'";
							$Result = DB_query ( $sqlesquematizado, $db );
						}
					}
                    
                    //Vitallantas mph
                    if (strpos("@".$_SESSION["DatabaseName"], "servillantas") AND $fgvitalizado==1){
                        $qry1 = "UPDATE purchorderdetails
                                INNER JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno 
                                SET quantityord=0
                                WHERE requisitionno= " . $orden;
                        $Result = DB_query ( $qry1, $db );
                        $qry1 = "UPDATE purchorders SET status='Cancelled' WHERE requisitionno= " . $orden;
                        $Result = DB_query ( $qry1, $db );
                        $qry1 = "UPDATE workorders SET idstatus=14 WHERE wo = " . $regs [0];
                        $Result = DB_query ( $qry1, $db );
                        $qry1 = "UPDATE worequirements SET  qtypu=0, stdcost=0 WHERE wo = " . $regs [0];
                        $Result = DB_query ( $qry1, $db );
                    }else{
                        $qry1 = "DELETE FROM workorders WHERE wo = " . $regs [0];
                        $Result = DB_query ( $qry1, $db );
                        $qry1 = "DELETE FROM woitems WHERE wo = " . $regs [0];
                        $Result = DB_query ( $qry1, $db );
                        $qry1 = "DELETE FROM worequirements WHERE wo = " . $regs [0];
                        $Result = DB_query ( $qry1, $db );
                    }
                    $resultw = DB_Txn_Commit ( $db );
					

					// Actualiza estatus de pedido
					$SQL = "UPDATE salesorders
							SET quotation =3
							WHERE orderno= " . $orden;
					$ErrMsg = _ ( 'ERROR CRITICO' ) . ' ' . _ ( 'ANOTE EL ERROR' ) . ': ' . _ ( 'El pedido no se pudo cancelar' );
					$DbgMsg = _ ( 'El siguiente SQL se utilizo para actualizar el pedido de venta' );
					$Result = DB_query ( $SQL, $db, $ErrMsg, $DbgMsg, true );
					$SQL = "UPDATE salesdate
							SET fecha_cancelado = NOW(),
								usercancelado = '" . $_SESSION ['UserID'] . "'
							WHERE orderno= " . $orden;
					$ErrMsg = _ ( 'ERROR CRITICO' ) . ' ' . _ ( 'ANOTE EL ERROR' ) . ': ' . _ ( 'El pedido no se pudo cancelar' );
					$DbgMsg = _ ( 'El siguiente SQL se utilizo para actualizar el pedido de venta' );
					$Result = DB_query ( $SQL, $db, $ErrMsg, $DbgMsg, true );
					
					$Result = DB_Txn_Commit ( $db );
					
					prnMsg ( _ ( 'El Numero de Pedido' ) . ' ' . $orden . ' ' . _ ( 'se ha cancelado' ), 'success' );
					if (strlen ( $_SESSION ['FactoryManagerEmail'] ) > 0) {
						emailNotifier ( 'Cancelar Pedido', $orden, $db );
					}
				}
			}
		} else {
			//
			// $SQL = "UPDATE salesorders
			// 		SET quotation =3
			// 		WHERE orderno= " . $orden;
			
			// $ErrMsg = _ ( 'ERROR CRITICO' ) . ' ' . _ ( 'ANOTE EL ERROR' ) . ': ' . _ ( 'El pedido no se pudo cancelar' );
			// $DbgMsg = _ ( 'El siguiente SQL se utilizo para actualizar el pedido de venta' );
			// $Result = DB_query ( $SQL, $db, $ErrMsg, $DbgMsg );
			
			// $SQL = "UPDATE salesdate
			// 				SET fecha_cancelado = NOW(),
			// 					usercancelado = '" . $_SESSION ['UserID'] . "'
			// 				WHERE orderno= " . $orden;
			
			// $ErrMsg = _ ( 'ERROR CRITICO' ) . ' ' . _ ( 'ANOTE EL ERROR' ) . ': ' . _ ( 'El pedido no se pudo cancelar' );
			// $DbgMsg = _ ( 'El siguiente SQL se utilizo para actualizar el pedido de venta' );
			// $Result = DB_query ( $SQL, $db, $ErrMsg, $DbgMsg );
			
			// $Result = DB_Txn_Commit ( $db );
			
			// prnMsg ( _ ( 'El Numero de Pedido' ) . ' ' . $orden . ' ' . _ ( 'se ha cancelado' ), 'success' );
			// if (strlen ( $_SESSION ['FactoryManagerEmail'] ) > 0) {
			// 	emailNotifier ( 'Cancelar Pedido', $orden, $db );
			// }
		}
		
		// prnMsg(_('El Numero de Pedido') . ' ' . $orden . ' ' . _('se ha convertido a facturado y la cantidad restante de los productos se considerara como venta perdida'),'success');
		// Redirecciono a pagina de pedidos
		
		// echo '<meta http-equiv="Refresh" content="2; url=' . $rootpath . '/' . $paginabusquedapedidos . '?OrderNumber=' . $orden . '&UnidNeg=' . $tagrefen . '&FromDia=' . $FromDia . '&FromMes=' . $FromMes . '&FromYear=' . $FromYear . '&ToDia=' . $ToDia . '&ToMes=' . $ToMes . '&ToYear=' . $ToYear . '&Quotations=3' . '">';
	}
} elseif (isset($orden) and isset($folio) and isset($iddocto)) {
    if ($_SESSION ['UserID'] == "admin" ) {
     //   echo 'entro'.$folio.'<----';
    }
	$SQL = "SELECT debtortrans.*, YEAR(origtrandate) AS aniodocto, systypescat.EnvioFiscal, typeinvoice
			FROM debtortrans 
			INNER JOIN systypescat ON systypescat.typeid=debtortrans.type
			INNER JOIN tags ON tags.tagref=debtortrans.tagref
			WHERE id=" . $iddocto;
	if ($_SESSION ['UserID'] == "admin" and $debug) { // //
		                                                   //echo $SQL;
		                                            // echo ".orden=".$orden;
		                                            // echo ".folio=".$folio;
		                                            // echo ".iddocto=".$iddocto;
	}
	if ($_SESSION ['UserID'] == 'desarrollo') {
	  //echo 'sql->'.$SQL;
		//	die('Cadena_original: <pre><b>'.$cadena_original.'<b><br><br><br>') ;
	
		// echo 'Cadena_original: <pre><b>'.$cadena_original;
	
	}

	
	$ResultDatos = DB_query ( $SQL, $db, $ErrMsg, '', false, true );



	while ( $MyrowDatos = DB_fetch_array ( $ResultDatos ) ) {

		$version ='version="3';
		$SQLFacturaVersion="SELECT substring(xmlImpresion,locate( '".$version."',`xmlImpresion`)+9,3)  as version
	                        FROM Xmls  
	                        WHERE transno ='".$MyrowDatos ['transno']."' AND type ='".$MyrowDatos ['type']."';";
	    $rsFacturaVersion=DB_query($SQLFacturaVersion,$db);
	    if(DB_num_rows($rsFacturaVersion)>0){
	        $myRow=DB_fetch_array($rsFacturaVersion);
	        $FacturaVersion = $myRow['version'];
	    }else{
	    	$version ='Version="3';
			$SQLFacturaVersion="SELECT substring(xmlImpresion,locate( '".$version."',`xmlImpresion`)+9,3)  as version
		                        FROM Xmls  
		                        WHERE transno ='".$MyrowDatos ['transno']."' AND type ='".$MyrowDatos ['type']."';";
		    $rsFacturaVersion=DB_query($SQLFacturaVersion,$db);
		    if(DB_num_rows($rsFacturaVersion)>0){
		        $myRow=DB_fetch_array($rsFacturaVersion);
		        $FacturaVersion = $myRow['version'];
		    }

	    }
		
		//$FacturaVersion="";
	    if($FacturaVersion ==""){
	    	if($_SESSION['FacturaVersion'] == "3.3"){
	    		$FacturaVersion='3.3';
	    	}else{
	    		$FacturaVersion='3.2';
	    	}
	    	
	    }



		$InvoiceNo = $MyrowDatos ['transno'];
		$OrderNox = $MyrowDatos ['order_'];
		$DebtorNo = $MyrowDatos ['debtorno'];
		$tipodefacturacion = $MyrowDatos ['type'];
		$Tagref = $MyrowDatos ['tagref'];
		$InvoiceNoTAG = $MyrowDatos ['folio'];
		$separa = explode ( '|', $InvoiceNoTAG );
		$DebtorTransID = $MyrowDatos ['id'];
		$serie = $separa [0];
		$folio = $separa [1];

		if($_SESSION['UserID'] == "admin" and $debug or $_SESSION['UserID'] == "desarrollo"){
			echo '<pre><br>$tipodefacturacion: '.$tipodefacturacion;
		}
		// echo 'entro';
		$factelectronica = XSAInvoicing ( $InvoiceNo, $OrderNox, $DebtorNo, $tipodefacturacion, $Tagref, $serie, $folio, $db );
		
		$cadenaorginal = $factelectronica;

		if (strpos("@".$_SESSION["DatabaseName"], "servillantas")) {
			$factelectronica = $factelectronica;
		}else{
			$factelectronica = utf8_encode ( $factelectronica );
		}
		
		
		$enviofiscal = $MyrowDatos ['EnvioFiscal'];
		
		$aniodocto = $MyrowDatos ['aniodocto'];
		$SQL = " SELECT l.taxid,l.address5,t.tagname,t.areacode,l.legalid,t.typeinvoice,l.keysend,l.legalname
				FROM legalbusinessunit l, tags t
				WHERE l.legalid=t.legalid AND tagref='" . $Tagref . "'";
		
		$Result = DB_query ( $SQL, $db, $ErrMsg, $DbgMsg, true );
		if (DB_num_rows ( $Result ) == 1) {
			$myrowtags = DB_fetch_array ( $Result );
			$rfc = trim ( $myrowtags ['taxid'] );
			$keyfact = $myrowtags ['keysend'];
			$nombre = $myrowtags ['tagname'];
			$area = $myrowtags ['areacode'];
			$legaid = $myrowtags ['legalid'];
			$tipofacturacionxtag = $myrowtags ['typeinvoice'];
			$legalname = utf8_encode($myrowtags ['legalname']);
		}

		$param = array (
				'in0' => $empresa,
				'in1' => $nombre,
				'in2' => $tipo,
				'in3' => $myfile,
				'in4' => $factelectronica 
		);
		
		// echo " <br> tipofacturacionxtag 33 : ".$tipofacturacionxtag;
		// if($tipofacturacionxtag !=4){
		// 	$tipofacturacionxtag=4;
		// }
		if ($tipofacturacionxtag == 1) {
			//echo "<br>tipofacturacionxtag: 1<br>";
			try {
				$client = new SoapClient ( $_SESSION ['XSA'] . "xsamanager/services/FileReceiverService?wsdl" );
				$codigo = $client->guardarDocumento ( $param );
				$paramx = array (
						'in0' => $rfc,
						'in1' => $myfile,
						'in2' => $keyfact 
				);
				while ( $mensajeestatus == 'ENPROCESO' ) {
					sleep ( 5 );
					$codigofact = $client->obtenerEstadoDocumento ( $paramx );
					$mensajeestatus = $codigofact->out;
					$cuentastatus = $cuentastatus + 1;
				}
			} catch ( SoapFault $exception ) {
				$errorMessage = $exception->getMessage ();
				//echo 'error:' . $errorMessage;
			}
			// exit;
		}
		
		if ($tipofacturacionxtag == 2) {
			// echo "<br>tipofacturacionxtag: 2<br>";
			// echo "Generando XMLS...";
			$arrayGeneracion = generaXML ( $factelectronica, 'ingreso', $Tagref, $serie, $folio, $DebtorTransID, 'Facturas', $OrderNox, $db,$tipodefacturacion );
			$XMLElectronico = $arrayGeneracion ["xml"];
			// Se agrega la generacion de xml_intermedio
			$array = generaXMLIntermedio ( $factelectronica, $XMLElectronico, $arrayGeneracion ["cadenaOriginal"], utf8_encode($arrayGeneracion ["cantidadLetra"]), $OrderNox, $db, $tipofacturacionxtag, $Tagref, $tipodefacturacion, $transno );
			$xmlImpresion = $array ["xmlImpresion"];
			$rfcEmisor = $array ["rfcEmisor"];
			$fechaEmision = $array ["fechaEmision"];

			//Quitar caracteres raros, los regresa despues de timbrar
            $XMLElectronico = caracteresEspecialesFactura($XMLElectronico);
            $xmlImpresion = caracteresEspecialesFactura($xmlImpresion);

			// Inicializo transaccion
			$Result = DB_Txn_Begin ( $db );
			
			if ($transno == null || empty ( $transno )) {
				$transno = $InvoiceNo;
			}
            //echo '<pre>';
			//htmlentities($XMLElectronico);
			$query = "SELECT idXml from Xmls  where transNo=" . $transno . " and type=" . $tipodefacturacion;

			$result = DB_query ( $query, $db, $ErrMsg, $DbgMsg, true );
			if (DB_num_rows ( $result ) > 0) {
				$query = "UPDATE Xmls SET xmlImpresion='" . utf8_decode(addslashes($xmlImpresion)). "' where transNo=" . $transno . " and type=" . $tipodefacturacion;
			} else {
				$query = "INSERT INTO Xmls(transNo,type,rfcEmisor,fechaEmision,xmlSat,xmlImpresion,fiscal) VALUES(" . $transno . "," . $tipodefacturacion . ",'" . $rfcEmisor . "','" . $fechaEmision . "','" . utf8_decode(addslashes($XMLElectronico)) . "','" . utf8_decode( addslashes($xmlImpresion) ) . "','" . $enviofiscal . "');";
				
			}
			
			$Result = DB_query ( $query, $db, $ErrMsg, $DbgMsg, true );
			// Finalizo transaccion
			$Result = DB_Txn_Commit ( $db );
		}
		
		if ($tipofacturacionxtag == 4) {
			// echo "<br>tipofacturacionxtag: 4<br>";
			if($_SESSION['UserID'] == "admin" and $debug){
				echo '<pre><br>Tipo de Facturacion 4';
			}

			$success = false;
			$config = $_SESSION;
			
			// echo '<pre><br>xmluno :<br>'.htmlentities($XMLElectronico);
			$sql3 = "SELECT IFNULL(debtortrans.uuid,'') AS uuid, IFNULL(cadenatimbre, '') AS cadenatimbre, transno, type
					 FROM debtortrans
					WHERE debtortrans.id = '" . $DebtorTransID . "'";
			 
			$result3 = DB_query ( $sql3, $db );
			$tieneUUID = 0;

			if (DB_num_rows ( $result3 ) > 0) {
				
				$myrow3 = DB_fetch_array ( $result3 );
				$transno = $myrow3 ['transno'];
				$tipodefacturacion = $myrow3 ['type'];

				if (strlen ( $myrow3 ['uuid'] ) > 0) {
					$tieneUUID = 1;
				}
				// Reenvio el XML para reimpresion//
				if ($_SESSION ['UserID'] == "admin" and $debug) {
					// echo '<br>uuid'.$myrow3['uuid'].'<br>';
					// echo '<br>envio fiscal'.$enviofiscal.'<br>';
					// echo '<pre>'.$sql3;
				}
				
				if (strlen ( $myrow3 ['uuid'] ) > 0 or $enviofiscal == 0) {
					//echo "<br>if uuid o enviofiscal<br>";
					if ($_SESSION ['UserID'] == "admin" and $debug) {
						// echo 'entraaaaaaa';
					}
					
					$query = "SELECT idXml,xmlSat,xmlSat from Xmls  where transNo=" . $myrow3 ['transno'] . " and type=" . $tipodefacturacion;
					
					if (($_SESSION ['UserID'] == "admin" and $debug) OR $_SESSION ['UserID'] == "desarrollo") {
						echo '<pre>'.$query;
					}
					$resultxml = DB_query ( $query, $db, $ErrMsg, $DbgMsg, true );
					
					if (DB_num_rows ( $resultxml ) > 0) {
						//echo "<br>if resultxml<br>";
						$myrowxml = DB_fetch_array ( $resultxml );

						$arraycadena = explode ( '@%', $factelectronica );
						$linea = $arraycadena [0];
						$datos = explode ( '|', $linea );
						$cantidadLetra = $datos [11];
						
						if ($enviofiscal == 0) {
							//
							//echo "<br>if enviofiscal<br>";
							// if ($FacturaVersion == "3.3") {
							// 	$arrayGeneracion = generaXMLCFDI3_3 ( $factelectronica, 'ingreso', $Tagref, $serie, $folio, $DebtorTransID, 'Facturas', $OrderNox, $db );
							// } else {
							// 	$arrayGeneracion = generaXMLCFDI ( $factelectronica, 'ingreso', $Tagref, $serie, $folio, $DebtorTransID, 'Facturas', $OrderNox, $db );
							// }
							//ANTES DEL 3.3
							//$arrayGeneracion = generaXML ( $factelectronica, 'ingreso', $Tagref, $serie, $folio, $DebtorTransID, 'Facturas', $OrderNox, $db,$tipodefacturacion );
							// Agrego generacion de XML para impresion de documentos
					 		//$XMLElectronico = $arrayGeneracion ["xml"];
					 		$XMLElectronico = $myrowxml['xmlSat'];
					 		$XMLElectronico = utf8_encode($XMLElectronico);

					 		if(empty($XMLElectronico) or $XMLElectronico==""){
								if ($FacturaVersion == "3.3") {
									$arrayGeneracion = generaXMLCFDI3_3 ( $factelectronica, 'ingreso', $Tagref, $serie, $folio, $DebtorTransID, 'Facturas', $OrderNox, $db );
									$XMLElectronico = $arrayGeneracion ["xml"];
								} else {
									$arrayGeneracion = generaXMLCFDI ( $factelectronica, 'ingreso', $Tagref, $serie, $folio, $DebtorTransID, 'Facturas', $OrderNox, $db );
									$XMLElectronico = $arrayGeneracion ["xml"];
								}
					 		}

					 		if($tipodefacturacion=='119'){
					 			$tieneUUID = 1;
					 		}

							// Se agrega la generacion de xml_intermedio
							
							$XMLElectronico_Impresion = "";

		                    if ($FacturaVersion == "3.3") {
		                        //Agregar nodos de domicilio (Emisor y Receptor)
		                        //echo "entra para lo del 33 generaXMLCFDI_Impresion,";
		                        $XMLElectronico_Impresion = generaXMLCFDI_Impresion($factelectronica, $XMLElectronico, $Tagref, $db);
		                    }
		                    //echo "entra para lo del 33 generaXMLCFDI_Impresion,despues,";
		                    if (!empty($XMLElectronico_Impresion)) {
		                        $array = generaXMLIntermedio(utf8_encode($factelectronica), $XMLElectronico_Impresion, $arrayGeneracion ["cadenaOriginal"], utf8_encode($arrayGeneracion["cantidadLetra"]), $OrderNox, $db, $tipofacturacionxtag, $Tagref, $tipodefacturacion, $transno);
		                    }else{
		                        $array = generaXMLIntermedio($factelectronica, $XMLElectronico, $arrayGeneracion ["cadenaOriginal"], utf8_encode($arrayGeneracion["cantidadLetra"]), $OrderNox, $db, $tipofacturacionxtag, $Tagref, $tipodefacturacion, $transno);
		                    }
							// $array = generaXMLIntermedio ( $factelectronica, $XMLElectronico, $arrayGeneracion ["cadenaOriginal"], utf8_encode ( $arrayGeneracion ["cantidadLetra"] ), $OrderNox, $db, 1, $Tagref, $tipodefacturacion, $transno );
							$xmlImpresion =  ( $array ["xmlImpresion"] );
							$rfcEmisor = $array ["rfcEmisor"];
							$fechaEmision = $array ["fechaEmision"];
							

							//Quitar caracteres raros, los regresa despues de timbrar
	                        $XMLElectronico = caracteresEspecialesFactura($XMLElectronico);
	                        $xmlImpresion = caracteresEspecialesFactura($xmlImpresion);

	                        if ($_SESSION ['UserID'] == 'desarrollo' ) {
								//echo htmlentities(utf8_encode($myrowxml ['xmlSat']));
								echo "<br> SAT: ".htmlentities(utf8_encode($XMLElectronico));
							}

	                        if ($_SESSION ['UserID'] == 'desarrollo' ) {
								//echo htmlentities(utf8_encode($myrowxml ['xmlSat']));
								echo "<br> Impresion: ".htmlentities(utf8_encode($xmlImpresion));
							}

						} else {
							//echo "<br>if else enviofiscal<br>";
							if ($_SESSION ['UserID'] == 'desarrollo' and $debug) {
								echo htmlentities(utf8_encode($myrowxml ['xmlSat']));
							}	
							if ($_SESSION ['UserID'] == 'admin' and $debug) {
								echo htmlentities(utf8_encode($myrowxml ['xmlSat']));
							}	
							
							$XMLElectronicoAdd = AgregaAddendaXML ( utf8_encode($myrowxml['xmlSat']), $DebtorNo, $DebtorTransID, $db );

							if ($_SESSION ['UserID'] == 'desarrollo' and $debug) {
								//echo htmlentities(utf8_encode($myrowxml ['xmlSat']));
								echo "<br> Addenda: ".htmlentities(utf8_encode($XMLElectronicoAdd));
							}

							if ($_SESSION ['UserID'] == 'desarrollo' ) {
								//echo htmlentities(utf8_encode($myrowxml ['xmlSat']));
								echo "<br> Impresion: ".htmlentities(utf8_encode($xmlImpresion));
							}
							
							
							if ($XMLElectronicoAdd != $XMLElectronico) {
								//echo "<br>if XMLElectronicoAdd XMLElectronico<br>";
								//Quitar caracteres raros, los regresa despues de timbrar
		                        $XMLElectronicoAdd = caracteresEspecialesFactura($XMLElectronicoAdd);
		                        $xmlImpresion = caracteresEspecialesFactura($xmlImpresion);




								$query = "SELECT idXml from Xmls  where transNo=" . $transno . " and type=" . $tipodefacturacion;
								$result = DB_query ( $query, $db, $ErrMsg, $DbgMsg, true );
								if (DB_num_rows ( $result ) > 0) {
                                    $XMLElectronicoAdd = str_replace("<?xml version='1.0' encoding='UTF-8'?>", '<?xml version="1.0" encoding="UTF-8"?>', $XMLElectronicoAdd);
									$query = "UPDATE Xmls SET xmlSat='" . utf8_decode( addslashes($XMLElectronicoAdd) )  . "' where transNo=" . $transno . " and type=" . $tipodefacturacion;
								} else {
									$query = "INSERT INTO Xmls(transNo,type,rfcEmisor,fechaEmision,xmlSat,xmlImpresion,fiscal) VALUES(" . $transno . "," . $tipodefacturacion . ",'" . $rfcEmisor . "','" . $fechaEmision . "','" . utf8_decode(addslashes($XMLElectronicoAdd))  . "','" . utf8_decode( addslashes($xmlImpresion) )  . "','" . $enviofiscal . "');";
								}
								$Result = DB_query ( $query, $db, $ErrMsg, $DbgMsg, true );
								
								$carpeta = 'Facturas';
								$dir = "/var/www/html" . dirname ( $_SERVER ['PHP_SELF'] ) . "/companies/" . $_SESSION ['DatabaseName'] . "/SAT/" . str_replace ( '.', '', str_replace ( ' ', '', $legalname ) ) . "/XML/" . $carpeta . "/";
								$nufa = $serie . $folio;
								$mitxt = $dir . $nufa . ".xml";
								// unlink($mitxt);
								//echo "<br> ruta: ".$mitxt;
								//$mitxt = utf8_decode($mitxt);
								//$mitxt = str_replace("ñ", "n", $mitxt);
								/*if($_SESSION['DatabaseName']=='erpmatelpuente' || $_SESSION['DatabaseName']=='erpmatelpuente_CAPA' || $_SESSION['DatabaseName']=='erpmatelpuente_DES' ){
						        if($_SESSION['UserID']== 'desarrollo'){
						            echo "<br> legal: ".$legaid;
						        }
						        $legalname = str_replace("&ntilde;", "n", $legalname);
						        $legalname = str_replace("Ã±", "n", $legalname);
						        if($legaid == 4){
						           $legalname = "JuanaOrdunaAguilar";
						        }
						        //$legalname = str_replace("Ñ", "n", $legalname);
						        if($_SESSION['UserID']== 'desarrollo'){
						            echo "<br> prueba: ".$legalname;
						        }
						    }*/
								//echo "<br> ruta: ".$mitxt;
								$fp = fopen ( $mitxt, "w" );
								fwrite ( $fp, $XMLElectronicoAdd );
								fclose ( $fp );
								
								$fp = fopen ( $mitxt . '.COPIA2', "w" );
								fwrite ( $fp, $XMLElectronico );
								fclose ( $fp );
							}
							

							$XMLElectronico_Impresion = "";
		                    if ($FacturaVersion == "3.3") {
		                        //Agregar nodos de domicilio (Emisor y Receptor)
		                        $XMLElectronico_Impresion = generaXMLCFDI_Impresion($factelectronica, utf8_encode($myrowxml ['xmlSat']), $Tagref, $db);
		                    }

		                    if (!empty($XMLElectronico_Impresion)) {
		                        $array = generaXMLIntermedio(utf8_encode($factelectronica), $XMLElectronico_Impresion, $cadenaorginal, utf8_encode($cantidadLetra), $OrderNox, $db, $tipofacturacionxtag, $Tagref, $tipodefacturacion, $transno);
		                    }else{
		                        $array = generaXMLIntermedio($factelectronica, utf8_encode($myrowxml ['xmlSat']), $cadenaorginal, utf8_encode($cantidadLetra), $OrderNox, $db, $tipofacturacionxtag, $Tagref, $tipodefacturacion, $transno);
		                    }
							
							// $array = generaXMLIntermedio ( $factelectronica, utf8_encode($myrowxml ['xmlSat']), $cadenaorginal, utf8_encode($cantidadLetra), $OrderNox, $db, 1, $Tagref, $tipodefacturacion, $transno );
							if ($_SESSION ['UserID'] == 'admin' and $debug) {
								echo '<pre><br>array'; echo print_r($array);
							}
                                            
							$xmlImpresion = ($array ["xmlImpresion"]);				
							$rfcEmisor = $array ["rfcEmisor"];
							$fechaEmision = $array ["fechaEmision"];
							
							//Quitar caracteres raros, los regresa despues de timbrar
	                        $XMLElectronico = caracteresEspecialesFactura($XMLElectronico);
	                        $xmlImpresion = caracteresEspecialesFactura($xmlImpresion);
						}
                                               
						// Inicializo transaccion
						$Result = DB_Txn_Begin ( $db );
						if ($transno == null || empty ( $transno )) {
							$transno = $InvoiceNo;
						}

						//Quitar caracteres raros, los regresa despues de timbrar
                        $XMLElectronico = caracteresEspecialesFactura($XMLElectronico);
                        $xmlImpresion = caracteresEspecialesFactura($xmlImpresion);
                        $xmlImpresion = str_replace("&amp;", "&", $xmlImpresion);
					
						if($_SESSION ['UserID'] == 'desarrollo' ){
							echo '<pre>IMPRESION: Parametros: XML_1_(pre): <br>'.htmlentities($xmlImpresion).'<br>orderno: '.$OrderNox.'<pre>tipofac: '.$tipodefacturacion;
						}
							
						if(1!=1){
							echo '<pre>Parametros: XML(pos):'.htmlentities($xmlImpresion);
						}
						
						$query = "SELECT idXml from Xmls  where transNo=" . $transno . " and type=" . $tipodefacturacion;
						$result = DB_query ( $query, $db, $ErrMsg, $DbgMsg, true );
						//echo ".idXml=".$query;
						if (DB_num_rows ( $result ) > 0) {
                            //admin
							$query = "UPDATE Xmls SET xmlImpresion='" . ( addslashes($xmlImpresion) ) . "' where transNo=" . $transno . " and type=" . $tipodefacturacion;
						} else {
							$query = "INSERT INTO Xmls(transNo,type,rfcEmisor,fechaEmision,xmlSat,xmlImpresion,fiscal) VALUES(" . $transno . "," . $tipodefacturacion . ",'" . $rfcEmisor . "','" . $fechaEmision . "','" . (addslashes($XMLElectronico)) . "','" . ( addslashes($xmlImpresion) )  . "','" . $enviofiscal . "');";
							
						}
						
                        $ErrMsg = _("no se guardo el xml");
                        $DbgMsg = _("se guardo el xml");
						$Result = DB_query ( $query, $db, $ErrMsg, $DbgMsg, true );
	                    //$Result = DB_query ( $query, $db );
	                    //exit;
						$Result = DB_Txn_Commit ( $db );
						// Finalizo transaccion
						prnMsg ( _ ( 'Se ha regenerado de manera exitosa el formato de impresion de la factura solicitada' ), 'sucess' );
						// exit;

					} else {
						$tieneUUID = 0;
					}
				}
			}

			if ($tieneUUID == 0) {
				
				if ($FacturaVersion == "3.3") {
					$arrayGeneracion = generaXMLCFDI3_3 ( $factelectronica, 'ingreso', $Tagref, $serie, $folio, $DebtorTransID, 'Facturas', $OrderNox, $db );
				} else {
					$arrayGeneracion = generaXMLCFDI ( $factelectronica, 'ingreso', $Tagref, $serie, $folio, $DebtorTransID, 'Facturas', $OrderNox, $db );
				}
				
				
				$XMLElectronico = $arrayGeneracion ['xml'];
				
				if (($_SESSION ['UserID'] == 'saplicaciones' ) or ($_SESSION ['UserID'] == 'admin' )){
					 //echo '<pre><br><br> xmltimbrrar'.htmlentities($XMLElectronico);
				} //

				
				$XMLElectronico = str_replace ( '<?xml version="1.0" encoding="UTF-8"?>', '', $XMLElectronico );
                $XMLElectronico = str_replace ( "<?xml version='1.0' encoding='utf-8'?>", '', $XMLElectronico );
                
				include_once 'timbradores/TimbradorFactory.php';
				
				$timbrador = TimbradorFactory::getTimbrador ( $config );
				if($_SESSION ['UserID'] == 'desarrollo'){
				    //echo "<br>Timnbrador".var_dump($timbrador);

				}
				if ($timbrador != null) {

					$timbrador->setRfcEmisor ( $rfc );
					$timbrador->setDb ( $db );
					$cfdi = $timbrador->timbrarDocumento ( $XMLElectronico );
					$success = ($timbrador->tieneErrores () == false);
					foreach ( $timbrador->getErrores () as $error ) {
						
						if (($_SESSION ['UserID'] == 'desarrollo') or ($_SESSION ['UserID'] == 'admin')){
							echo "<div align='center' style='width: 600px;'>";

							 echo '<br><br>Error:'.$error;
							 echo '<br><br><br><br>factelectronica<br>'.$factelectronica;
							 echo '<br><br><br><pre><br>'.htmlentities($XMLElectronico);
							
							$cadena = $arrayGeneracion['cadenaOriginal'];
							$cadena = str_replace("&#xF1;", "ñ", $cadena);
            				$cadena = str_replace("&#xD1;", "Ñ", $cadena);

            				$cadena = str_replace("&#xE1;", "á", $cadena);
				            $cadena = str_replace("&#xE9;", "é", $cadena);
				            $cadena = str_replace("&#xED;", "í", $cadena);
				            $cadena = str_replace("&#xF3;", "ó", $cadena);
				            //$xmlImpresion = str_replace("", "ú", $xmlImpresion);
							 echo '<br><br><br>CADENA ORIGINAL AA<BR><BR>'.utf8_decode( str_replace("&", "&amp;", ($cadena)) );

							 echo "</div>";
							// exit;
						}
						// exit;
					}
				} else {
					prnMsg ( _ ( 'No hay un timbrador configurado en el sistema' ), 'error' );
				} //
			}
			
			if ($success) {
				
				$DatosCFDI = TraeTimbreCFDI ( $cfdi );
				
				if (strlen ( $DatosCFDI ['FechaTimbrado'] ) > 0) {
					if ($tieneUUID == 0) {
						$cadenatimbre = '||1.1|' . $DatosCFDI ['UUID'] . '|' . $DatosCFDI ['FechaTimbrado'] .'|' . $DatosCFDI ['RfcProvCertif']. '||' . $DatosCFDI ['SelloCFD'] . '|' . $DatosCFDI ['NoCertificadoSAT'] . '||';
						// guardamos el timbre fiscal en la base de datos para efectos de impresion de datos
						// Inicializo transaccion
						$Result = DB_Txn_Begin ( $db );
						$sql = "UPDATE debtortrans
							SET fechatimbrado='" . $DatosCFDI ['FechaTimbrado'] . "',
							  uuid='" . $DatosCFDI ['UUID'] . "',
							  		timbre='" . $DatosCFDI ['SelloSAT'] . "',
							  				cadenatimbre='" . $cadenatimbre . "'
							  						WHERE id=" . $DebtorTransID;
						$ErrMsg = _ ( 'El Sql que fallo fue' );
						$DbgMsg = _ ( 'No se pudo actualizar el sello y cadena del documento' );
						$Result = DB_query ( $sql, $db, $ErrMsg, $DbgMsg, true );
						// Finalizo transaccion
						$Result = DB_Txn_Commit ( $db );
						$XMLElectronico = $cfdi;
						// echo '<pre><br>xmluno :<br>'.htmlentities($XMLElectronico);*****
					}

					$XMLElectronico_Impresion = "";
                    if ($FacturaVersion == "3.3") {
                        //Agregar nodos de domicilio (Emisor y Receptor)
                        $XMLElectronico_Impresion = generaXMLCFDI_Impresion($factelectronica, $XMLElectronico, $Tagref, $db);
                    }

                    if (!empty($XMLElectronico_Impresion)) {
                        $array = generaXMLIntermedio(utf8_encode($factelectronica), $XMLElectronico_Impresion, $cadenatimbre, utf8_encode($arrayGeneracion["cantidadLetra"]), $OrderNox, $db, $tipofacturacionxtag, $Tagref, $tipodefacturacion, $transno);
                    }else{
                        $array = generaXMLIntermedio($factelectronica, $XMLElectronico, $cadenatimbre, utf8_encode($arrayGeneracion["cantidadLetra"]), $OrderNox, $db, $tipofacturacionxtag, $Tagref, $tipodefacturacion, $transno);
                    }
					
					// $array = generaXMLIntermedio ( $factelectronica, $XMLElectronico, $cadenatimbre, utf8_encode($arrayGeneracion ["cantidadLetra"]), $OrderNox, $db, 1, $Tagref, $tipodefacturacion, $transno );
					$xmlImpresion = $array ["xmlImpresion"];
					$xmlImpresion = str_replace('ine:', '', $xmlImpresion);
					$rfcEmisor = $array ["rfcEmisor"];
					$fechaEmision = $array ["fechaEmision"];

					//Quitar caracteres raros, los regresa despues de timbrar
                    $XMLElectronico = caracteresEspecialesFactura($XMLElectronico);
                    $xmlImpresion = caracteresEspecialesFactura($xmlImpresion);

                    $xmlImpresion = str_replace("&amp;", "&", $xmlImpresion);


                    if ($_SESSION ['UserID'] == 'desarrollo' ) {
						//echo htmlentities(utf8_encode($myrowxml ['xmlSat']));
						echo "<br> impresion: ".htmlentities(utf8_encode($xmlImpresion));
					}

					// Inicializo transaccion
					$Result = DB_Txn_Begin ( $db );
					if ($transno == null || empty ( $transno )) {
						$transno = $InvoiceNo;
					}
					$query = "SELECT idXml from Xmls  where transNo=" . $transno . " and type=" . $tipodefacturacion;
					$result = DB_query ( $query, $db, $ErrMsg, $DbgMsg, true );
										
					if (DB_num_rows ( $result ) > 0) {
						$XMLElectronico = str_replace("<?xml version='1.0' encoding='UTF-8'?>", '<?xml version="1.0" encoding="UTF-8"?>', $XMLElectronico);
						//Se agrego update del xmlsat por lo de las facturas de sustitucion
						$query = "UPDATE Xmls SET xmlSat='".utf8_decode(addslashes($XMLElectronico))."' ,xmlImpresion='" . utf8_decode( addslashes($xmlImpresion) ) . "' where transNo=" . $transno . " and type=" . $tipodefacturacion;
						prnMsg ( _ ( 'Se ha actualizado la impresion' ), 'info' );
					} else {
						$XMLElectronico = str_replace("<?xml version='1.0' encoding='UTF-8'?>", '<?xml version="1.0" encoding="UTF-8"?>', $XMLElectronico);
						$query = "INSERT INTO Xmls(transNo,type,rfcEmisor,fechaEmision,xmlSat,xmlImpresion,fiscal) VALUES(" . $transno . "," . $tipodefacturacion . ",'" . $rfcEmisor . "','" . $fechaEmision . "','" . utf8_decode(addslashes($XMLElectronico)) . "','" . utf8_decode( addslashes($xmlImpresion) ) . "','" . $enviofiscal . "')";
						
					}
					$Result = DB_query ( $query, $db, $ErrMsg, $DbgMsg, true );
					// Finalizo transaccion
					$Result = DB_Txn_Commit ( $db );
					// echo '<pre><br>xml:<br>'.htmlentities($xmlImpresion);
					// echo ".actualizar Xmls=".$xmlImpresion;
					// echo ".query=".$query;
					// exit;
					
					// Guardamos el XML una vez que se agrego el timbre fiscal
					$carpeta = 'Facturas';
					$dir = "/var/www/html/" . dirname ( $_SERVER ['PHP_SELF'] ) . "/companies/" . $_SESSION ['DatabaseName'] . "/SAT/" . str_replace ( '.', '', str_replace ( ' ', '', $legalname ) ) . "/XML/" . $carpeta . "/";
					$nufa = $serie . $folio;
					$mitxt = $dir . $nufa . ".xml";
					unlink ( $mitxt );
					
					$fp = fopen ( $mitxt, "w" );
					fwrite ( $fp, $XMLElectronico );
					fclose ( $fp );
					$fp = fopen ( $mitxt . '.COPIA', "w" );
					fwrite ( $fp, $XMLElectronico );
					fclose ( $fp );
					$liga = "PDFInvoice.php?&clave=chequepoliza_sefia";
					$liga = '<p><span class="glyphicon glyphicon-print"></span>' . ' ' . '<a target="_blank" target="_blank" href="' . $rootpath . '/PDFInvoice.php?OrderNo=' . $OrderNox . '&TransNo=' . $InvoiceNo . '&Type=' . $tipodefacturacion . '&Tagref=' . $Tagref . '">' . _ ( 'Imprimir Factura' ) . ' (' . _ ( 'Laser' ) . ')' . '</a>';
				} else {
					prnMsg ( _ ( 'No fue posible realizar el timbrado del documento, verifique con el administrador; el numero de error es:' ) . $cfdi, 'error' );
					// exit;
				}
			}
		}
		if($_SESSION ['UserID'] == 'admin'){
			//echo 'Antes';
		}
		switch ($action) {
			case "reimpresion" :
				
				if($_SESSION ['UserID'] == 'admin'){
					//echo '->->->';
				}
				
				// ---------------------
				// Caso donde se permite la regeneraci�n del XML de impresion �nicamente
				// ---------------------
				
				// Busqueda de UUID ?fue timbrado?
				$tieneUUID = 0;
				$sql3 ="SELECT IFNULL(debtortrans.uuid,'') AS uuid, IFNULL(cadenatimbre, '') AS cadenatimbre, transno, type, CONCAT('||1.1|' ,uuid, '|' ,fechatimbrado,'|' ,'".$_SESSION['PacTimbrador']."|', '|' ,sello, '|' ,FileSAT,'||') AS cadenatimbreUpdate, debtortrans.tagref, tags.legalid
                        FROM debtortrans
                        LEFT JOIN  tags ON tags.tagref=debtortrans.tagref
                        LEFT JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
                        WHERE id ='" . $DebtorTransID . "'";
				if ($_SESSION ['UserID'] == 'admin' and $debug) {
					/*
					 * var_dump($sql3);
					 * echo '<br/>';
					 */
				}
				$result3 = DB_query ( $sql3, $db );
				if (DB_num_rows ( $result3 ) > 0) {
					$myrow3 = DB_fetch_array ( $result3 );
					if (strlen ( $myrow3 ['uuid'] ) > 0) {
						$tieneUUID = 1;
					}
					$cadenatimbre = $myrow3 ['cadenatimbre'];

					//if(count($cadenatimbre)<50){
						$cadenatimbre=$myrow3['cadenatimbreUpdate'];
						$sql = "UPDATE debtortrans
							SET cadenatimbre='" . $cadenatimbre . "'
							WHERE id=" . $DebtorTransID;
						$ErrMsg = _ ( 'El Sql que fallo fue' );
						$DbgMsg = _ ( 'No se pudo actualizar la  cadena timbre del documento.' );
						$Result = DB_query ( $sql, $db, $ErrMsg, $DbgMsg, true );

					//}
					if ($_SESSION ['UserID'] == 'desarrollo') {
				        //echo "<br>ingreso cadenatimbre:" .  $cadenatimbre."\n";
				    }	
				}
				
				// Busqueda de XML en tabla: Xmls
				$tieneXMLSatDb = 0;
				$query = "SELECT idXml,xmlSat from Xmls where transNo=" . $transno . " and type=" . $tipodefacturacion;
				$XMLElectronicoAddSAT = "";
				$result4 = DB_query ( $query, $db, $ErrMsg, $DbgMsg, true );
				if (DB_num_rows ( $result4 ) > 0) {
					$tieneXMLSatDb = 1;
					$myrow4 = DB_fetch_array ( $result4 );
					$XMLElectronicoAddSAT = utf8_encode($myrow4 ['xmlSat']);
				}
				
				// Busqueda de XML en filesystem
				$tieneXMLSatFileSystem = 0;
				$carpeta = 'Facturas';
				$dir = "/var/www/html/" . dirname ( $_SERVER ['PHP_SELF'] ) . "/companies/" . $_SESSION ['DatabaseName'] . "/SAT/" . str_replace ( '.', '', str_replace ( ' ', '', $legalname ) ) . "/XML/" . $carpeta . "/";
				$nufa = $serie . $folio;
				$FileXMLSatFileSystem = $dir . $nufa . ".xml";
				
				if (file_exists ( $FileXMLSatFileSystem )) {
					$tieneXMLSatFileSystem = 1;
				}
				
				// Regeneraci�n de XML de XML de impresion si tiene UUID
				if ($tieneUUID) {
					prnMsg ( _ ( 'Comprobante timbrado previamente.' ), 'info' );
					if ($tieneXMLSatDb) {
						
						$arraycadena2 = explode ( '@%', $factelectronica );
						$linea2 = $arraycadena2 [0];
						$datos2 = explode ( '|', $linea2 );
						$cantidadLetra = $datos2 [11];

						$XMLElectronico_Impresion = "";
	                    if ($FacturaVersion == "3.3") {
	                        //Agregar nodos de domicilio (Emisor y Receptor)
	                        $XMLElectronico_Impresion = generaXMLCFDI_Impresion($factelectronica, $XMLElectronicoAddSAT, $Tagref, $db);
	                    }
	                    // echo "<br><br>factelectronica: ".$factelectronica."<br><br>";
	                    // echo "<br><br>cantidadLetra: ".$cantidadLetra."<br><br>";
	                    if ($_SESSION ['UserID'] == 'desarrollo') {
					        echo "<br>cadenatimbre:" .  $cadenatimbre."\n";
					    }
	                    if (!empty($XMLElectronico_Impresion)) {
	                    	if ($_SESSION ['UserID'] == 'desarrollo') {
					        	//echo "<br>ingreso OrderNox:" .  $OrderNox."\n";
					    	}
	                        $array = generaXMLIntermedio(utf8_encode($factelectronica), $XMLElectronico_Impresion, $cadenatimbre, utf8_encode($cantidadLetra), $OrderNox, $db, $tipofacturacionxtag, $Tagref, $tipodefacturacion, $transno);
	                    }else{
	                        $array = generaXMLIntermedio($factelectronica, $XMLElectronicoAddSAT, $cadenatimbre, utf8_encode($cantidadLetra), $OrderNox, $db, $tipofacturacionxtag, $Tagref, $tipodefacturacion, $transno);
	                    }

						// $array = generaXMLIntermedio ( $factelectronica, $XMLElectronicoAddSAT, $cadenatimbre, utf8_encode($cantidadLetra), $OrderNox, $db, 1, $Tagref, $tipodefacturacion, $transno );
						
						$xmlImpresion =  ( $array ["xmlImpresion"] );

                        //Quitar caracteres raros, los regresa despues de timbrar
                        $XMLElectronico = caracteresEspecialesFactura($XMLElectronico);
                        $xmlImpresion = caracteresEspecialesFactura($xmlImpresion);

                        $xmlImpresion = str_replace("&amp;", "&", $xmlImpresion);
                       
						if($_SESSION ['UserID'] == 'desarrollo' && $debug){
							echo '<pre>Parametros: XML(3):'.htmlentities($xmlImpresion);
						}
						
						$query = "UPDATE Xmls SET xmlImpresion='" . ( addslashes($xmlImpresion) ) . "' where transNo=" . $transno . " and type=" . $tipodefacturacion;
						
						$Result = DB_query ( $query, $db, $ErrMsg, $DbgMsg, true );
						prnMsg ( _ ( 'Impresion Actualizada.' ), 'success' );
					} elseif ($tieneXMLSatFileSystem == 1) {
						// Regeneraci�n de XML de XML de impresion si tiene XML SAT en DB
						
						// Obtiene XML de filesystem
						/*
						 * $archivo = fopen ($FileXMLSatFileSystem, "r");
						 * $XMLElectronico = fread($archivo, filesize("$FileXMLSatFileSystem"));
						 * fclose($archivo);
						 * //
						 */
						
						$XMLElectronico = file_get_contents ( $FileXMLSatFileSystem );
						
						// echo '<br>-->XML '.htmlentities($XMLElectronico).'<br>-->'.$FileXMLSatFileSystem;
						
						if ($_SESSION ['UserID'] == 'admin' and $debug) {
							// var_dump($XMLElectronico);
							// echo '<br/>';
							// echo '<br/>';
							// echo '<br/>';
						}
						// Genera XML Intermedio
						$arraycadena2 = explode ( '@%', $factelectronica );
						if ($_SESSION ['UserID'] == 'admin' and $debug) {
							// var_dump($arraycadena2);
							// echo '<br/>';
							// echo '<br/>';
							// echo '<br/>';
						}
						$linea2 = $arraycadena2 [0];
						if ($_SESSION ['UserID'] == 'admin' and $debug) {
							// var_dump($linea2);
							// echo '<br/>';
							// echo '<br/>';
							// echo '<br/>';
						}
						$datos2 = explode ( '|', $linea2 );
						if ($_SESSION ['UserID'] == 'admin' and $debug) {
							// var_dump($datos2);
							// echo '<br/>';
							// echo '<br/>';
							// echo '<br/>';
						}
						$cantidadLetra = $datos2 [11];
						if ($_SESSION ['UserID'] == 'admin' and $debug) {
							// var_dump($cantidadLetra);
							// echo '<br/>';
							// echo '<br/>';
							// echo '<br/>';
						}
						// $array = generaXMLIntermedio ( $factelectronica, $XMLElectronico, $cadenatimbre, utf8_encode($cantidadLetra), $OrderNox, $db, 1, $Tagref, $tipodefacturacion, $transno );
						if ($_SESSION ['UserID'] == 'admin' and $debug) {
							// var_dump($array);
							// echo '<br/>';
							// echo '<br/>';
							// echo '<br/>';
						}
						$xmlImpresion =  ( $array ["xmlImpresion"] );

						//var_dump($array);//impresion
						
						//Quitar caracteres raros, los regresa despues de timbrar
                        $XMLElectronico = caracteresEspecialesFactura($XMLElectronico);
                        $xmlImpresion = caracteresEspecialesFactura($xmlImpresion);

                        $xmlImpresion = str_replace("&amp;", "&", $xmlImpresion);

						// Inicializo transaccion
						$Result = DB_Txn_Begin ( $db );
						if ($transno == null || empty ( $transno )) {
							$transno = $InvoiceNo;
						}
						$xmlImpresion = '';
						$query = "INSERT INTO Xmls(transNo,type,rfcEmisor,fechaEmision,xmlSat,xmlImpresion) VALUES(" . $transno . "," . $tipodefacturacion . ",'" . $rfcEmisor . "','" . $fechaEmision . "','" . utf8_decode(addslashes($XMLElectronico))  . "','" . utf8_decode( addslashes($xmlImpresion) ) . "')";
						
						
						$Result = DB_query ( $query, $db, $ErrMsg, $DbgMsg, true );
						prnMsg ( _ ( 'Impresion Actualizada testing testing..' ), 'success' );
					} else {
						prnMsg ( _ ( '...' ), 'info' );
					}

					if ($XMLElectronicoAddSAT != "") {
						//guardar xmlsat, punto de venta
						$carpeta = 'Facturas';
						$dir = "/var/www/html/" . dirname ( $_SERVER ['PHP_SELF'] ) . "/companies/" . $_SESSION ['DatabaseName'] . "/SAT/" . str_replace ( '.', '', str_replace ( ' ', '', $legalname ) ) . "/XML/" . $carpeta . "/";
						$nufa = $serie . $folio;
						$mitxt = $dir . $nufa . ".xml";
						// unlink($mitxt);
						
						$fp = fopen ( $mitxt, "w" );
						fwrite ( $fp, $XMLElectronicoAddSAT );
						fclose ( $fp );
					}
				} else {
					// $factelectronica = XSAInvoicing ( $transno, $_SESSION ['Items' . $identifier]->OrderNo, $_SESSION ['Items' . $identifier]->DebtorNo, $tipodefacturacion, $_SESSION ['Items' . $identifier]->Tagref, $serie, $folio, $db );
					// prnMsg(_('El documento no se encuentra Timbrado'),'info');
				}
				
				break;
			case 1 :
				echo "action es igual a 1";
				break;
			case 2 :
				echo "action es igual a 2";
				break;
			default :
				echo "";
		}
	}
}

if(isset($_GET['Exito']) AND $_GET['Exito'] == 1 ){
	prnMsg("Se genero con éxito la factura con el folio: ".$_GET['folio'],'info');

}elseif (isset($_GET['Exito']) AND $_GET['Exito'] == 0) {
	prnMsg("Ocurrio un error al generar la factura: ".$_GET['error'],'info');
}
// Unifiacion de pedido de venta
// se unifica tomando los datos del primer pedido seleccionado
if (isset ( $_POST ['btnUnificar'] )) {
	$arrdata = $_POST ['selectedorders'];
	$arritems = array ();
	// Inicializo transaccion
	$Result = DB_Txn_Begin ( $db );
	if (count ( $arrdata ) > 0) {
		$neworder = GetNextTransNo ( 30, $db );
		$headerdone = false;
		$itemlineno = 0;
		$taxtotal = 0;
		
		foreach ( $arrdata as $value ) {
			$orderNo = $value;
			// buscar el taxtotal de cada orden a unificar
			$sql = "Select taxtotal FROM salesorders
					WHERE orderno = " . $orderNo;
			$rtax = DB_query ( $sql, $db );
			$rowtax = DB_fetch_array ( $rtax );
			$taxtotal += $rowtax [0];
			if (! $headerdone) {
				// buscar oprtunidad
				$sql = "Select idprospect FROM salesorders
				WHERE orderno = $orderNo";
				$res = DB_query ( $sql, $db );
				$reg = DB_fetch_array ( $res );
				$umov = $reg [0];
				// insertar encabezado de la nueva orden
				$HeaderSQL = "INSERT INTO salesorders ( orderno,debtorno,branchcode,customerref,comments,
						orddate,ordertype,shipvia,deliverto,deladd1,deladd2,
						deladd3,deladd4,deladd5,deladd6,contactphone,contactemail,
						freightcost,fromstkloc,deliverydate,quotedate,confirmeddate,
						quotation,deliverblind,salesman,placa,serie,kilometraje,tagref,
						taxtotal,totaltaxret,currcode,paytermsindicator,advance,UserRegister,
						puestaenmarcha,paymentname,nocuenta,extratext,nopedido,noentrada,
						noremision,idprospect,contid,typeorder,deliverytext
						)
						SELECT  " . $neworder . ",debtorno,branchcode,customerref,comments,
								current_date,ordertype,shipvia,deliverto,deladd1,deladd2,
								deladd3,deladd4,deladd5,deladd6,contactphone,contactemail,
								freightcost,fromstkloc,deliverydate,quotedate,confirmeddate,
								1,deliverblind,salesman,placa,serie,kilometraje,tagref,
								taxtotal,totaltaxret,currcode,paytermsindicator,advance,'" . $_SESSION ['UserID'] . "',
									 puestaenmarcha,paymentname,nocuenta,extratext,nopedido,noentrada,
									 noremision,idprospect,contid,typeorder,deliverytext
										FROM salesorders
										WHERE orderno = " . $orderNo;
				$ErrMsg = _ ( 'El Sql que fallo fue' );
				$DbgMsg = _ ( 'No se pudo actualizar el encabezado del pedido que desea unificar' );
				$Result = DB_query ( $HeaderSQL, $db, $ErrMsg, $DbgMsg, true );
				$headerdone = true;
			}
			
			// insertar partidas
			$qrypart = "Select stkcode,
					unitprice,
					quantity,
					discountpercent,
					discountpercent1,
					discountpercent2,
					narrative,
					poline,
					itemdue,
					fromstkloc,
					salestype,
					warranty,
					servicestatus,
					refundpercent,
					quantitydispatched,
					showdescrip
					From salesorderdetails
					WHERE orderno = " . $orderNo;
			$rspart = DB_query ( $qrypart, $db );
			while ( $rowspart = DB_fetch_array ( $rspart ) ) {
				
				if (in_array ( $rowspart ['stkcode'], $arritems )) {
					// upadte
					$DetailSQL = "UPDATE salesorderdetails
							SET quantity = quantity + " . $rowspart ['quantity'] . "
							WHERE orderno = $neworder
							AND stkcode = '" . $rowspart ['stkcode'] . "'";
				} else {
					$arritems [] = $rowspart ['stkcode'];
					$DetailSQL = "INSERT INTO salesorderdetails (
												orderlineno,
												orderno,
												stkcode,
												unitprice,
												quantity,
												discountpercent,
												discountpercent1,
												discountpercent2,
												narrative,
												poline,
												itemdue,
												fromstkloc,
												salestype,
												warranty,
												servicestatus,
												refundpercent,
												quantitydispatched,
												showdescrip
												) VALUES
												(	$itemlineno,
													$neworder,
													'" . $rowspart ['stkcode'] . "',
													'" . $rowspart ['unitprice'] . "',
													'" . $rowspart ['quantity'] . "',
													'" . $rowspart ['discountpercent'] . "',
													'" . $rowspart ['discountpercent1'] . "',
													'" . $rowspart ['discountpercent2'] . "',
													'" . $rowspart ['narrative'] . "',
													'" . $rowspart ['poline'] . "',
													'" . $rowspart ['itemdue'] . "',
													'" . $rowspart ['fromstkloc'] . "',
													'" . $rowspart ['salestype'] . "',
													'" . $rowspart ['warranty'] . "',
													'" . $rowspart ['servicestatus'] . "',
													'" . $rowspart ['refundpercent'] . "',
													'" . $rowspart ['quantitydispatched'] . "',
													'" . $rowspart ['showdescrip'] . "'
												)";
					$itemlineno ++;
				}
				
				$Result = DB_query ( $DetailSQL, $db, $ErrMsg, $DbgMsg, true );
			}
		} // Fin de recorrido de pedidos a unificar
		  // crear oportunidad
		$SQL = "INSERT INTO prospect_movimientos (areacod,debtorno,u_proyecto,dia,mes,
				anio,concepto,descripcion,u_user,
				cargo,prioridad,referencia,periodo_dev,erp,TipoMovimientoId,
				estimado,fecha,currcode,branchcode,fecha_compromiso,
				grupo_contable,confirmado,activo,u_entidad,catcode,idstatus,
				UserId,fecha_alta,clientcontactid
				)
				Select areacod,debtorno,u_proyecto,'" . date ( "d" ) . "','" . date ( "m" ) . "',
						'" . date ( "Y" ) . "',concepto,descripcion,'" . $_SESSION ['UserID'] . "',
								cargo,prioridad,referencia,periodo_dev,erp,TipoMovimientoId,
								estimado,current_date,currcode,branchcode,fecha_compromiso,
								grupo_contable,confirmado,activo,u_entidad,catcode,idstatus,
								'" . $_SESSION ['UserID'] . "',current_date,clientcontactid
								FROM prospect_movimientos
								WHERE u_movimiento = '$umov'";
		$Result = DB_query ( $SQL, $db, $ErrMsg, $DbgMsg, true );
		$prospectid = DB_Last_Insert_ID ( $db, 'prospect_movimientos', 'u_movimiento' );
		
		$sql2 = "INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
				VALUES ('" . $prospectid . "','Alta de oportunidad:" . $_SESSION ['UserID'] . "@: GENERADA POR UNIFICAR COTIZACIONES',Now(),0,'1','" . $_SESSION ['UserID'] . "','" . $_SESSION ['UserID'] . "','alta')";
		$Result = DB_query ( $sql2, $db, $ErrMsg, $DbgMsg, true );
		// Actualiza num oportunidad y total de impuesto de nuevo pedido
		$SQL = "UPDATE salesorders
				SET idprospect=" . $prospectid . ",
						taxtotal = " . $taxtotal . "
								WHERE orderno= " . $neworder;
		$ErrMsg = _ ( 'ERROR CRITICO' ) . ' ' . _ ( 'ANOTE EL ERROR' ) . ': ' . _ ( 'El encabezado de la  ordern de servicio de venta no se pudo actualizar' );
		$DbgMsg = _ ( 'El siguiente SQL se utilizo para actualizar la orden de venta' );
		$Result = DB_query ( $SQL, $db, $ErrMsg, $DbgMsg, true );
		prnMsg ( 'Se han unificado las cotizaciones en la cotizacion No ' . $neworder );
	} // Fin de validacion
	  // Finalizo transaccion
	$Result = DB_Txn_Commit ( $db );
} // Fin de unificacion

if (isset($_POST['ActualizarTarjeta'])) { //redrogo

	$arrdata = $_POST ['selectedorders'];

	if (count ( $arrdata ) > 0) {
		foreach ( $arrdata as $value ) {
			$orderNo = $value;
			$discountcardnew = $_POST['trm' . $orderNo];
			//$discountcardnew = $_POST['discountcard_' . $orderNo];
			$uscsql = "UPDATE salesorders
						SET discountcard = '" . $discountcardnew . "', 
							placa = '" . $discountcardnew . "'
						WHERE orderno = '" . $orderNo . "'";
			$ErrMsg = _ ( 'ERROR CRITICO' ) . ' ' . _ ( 'ANOTE EL ERROR' ) . ': ' . _ ( 'No se pudo actualizar la tarjeta de descuento' );
			$DbgMsg = _ ( 'El siguiente SQL se utilizo para actualizar la tarjeta de descuento' );
			$Result = DB_query ($uscsql, $db, $ErrMsg, $DbgMsg, true );
			
		}
	}
}

if (isset ( $_POST ['btnDuplicar'] )) {
	$arrdata = $_POST ['selectedorders'];
	if (count ( $arrdata ) > 0) {
		$ordenesDuplicadas = "";
		// Inicializo transaccion
		$Result = DB_Txn_Begin ( $db );
		foreach ( $arrdata as $value ) {
			$orderNo = $value;
			$sql = "Select idprospect,quotation
			FROM salesorders
			WHERE orderno = $orderNo";
			$res = DB_query ( $sql, $db );
			$reg = DB_fetch_array ( $res );
			$umov = $reg [0];
			$quotation = $reg [1];
			
			$neworder = GetNextTransNo ( 30, $db );
			$ordenesDuplicadas .= $neworder . ",";
			
			// Inserta encabezado de pedido
			$HeaderSQL = "INSERT INTO salesorders (orderno,debtorno,branchcode,customerref,comments,orddate,
					ordertype,shipvia,deliverto,deladd1,deladd2,deladd3,deladd4,
					deladd5,deladd6,contactphone,contactemail,freightcost,fromstkloc,
					deliverydate,quotedate,confirmeddate,deliverblind,salesman,
					placa,serie,kilometraje,tagref,taxtotal,totaltaxret,currcode,paytermsindicator,
					advance,UserRegister,puestaenmarcha,paymentname,nocuenta,extratext,nopedido,
					noentrada,noremision,idprospect,contid,typeorder,deliverytext
					)
					Select " . $neworder . ",debtorno,branchcode,customerref,'',Now(),
							ordertype,shipvia,deliverto,deladd1,deladd2,deladd3,deladd4,
							deladd5,deladd6,contactphone,contactemail,freightcost,fromstkloc,
							Now(),Now(),Now(),deliverblind,salesman,
							placa,serie,kilometraje,tagref,taxtotal,totaltaxret,currcode,paytermsindicator,
							advance,'" . $_SESSION ['UserID'] . "',puestaenmarcha,paymentname,nocuenta,extratext,nopedido,
									noentrada,noremision,idprospect,contid,typeorder,deliverytext
									FROM salesorders
									WHERE orderno = " . $orderNo;
			$Result = DB_query ( $HeaderSQL, $db, $ErrMsg, $DbgMsg, true );
			//DB_Last_Insert_ID($db,'salesorders', 'orderno')
			// Inserta partidas de pedido
			$DetailSQL = "INSERT INTO salesorderdetails (orderlineno,orderno,stkcode,unitprice,quantity,discountpercent,
					discountpercent1,discountpercent2,narrative,poline,itemdue,fromstkloc,
					salestype,warranty,servicestatus,refundpercent,quantitydispatched,showdescrip,localidad
					)
					Select orderlineno," . $neworder . ",stkcode,unitprice,quantity,discountpercent,
							discountpercent1,discountpercent2,narrative,poline,itemdue,fromstkloc,
							salestype,warranty,servicestatus,refundpercent,quantitydispatched,showdescrip,localidad
							From salesorderdetails
							WHERE orderno = " . $orderNo;
			$Result = DB_query ( $DetailSQL, $db, $ErrMsg, $DbgMsg, true );

			//Validación de No. pedimento
            if($_SESSION['DatabaseName'] == 'erppisumma_DES' || $_SESSION['DatabaseName'] == 'erppisumma' || $_SESSION['DatabaseName'] == 'erppisumma_CAPA'){

				$SQLpedimento = "INSERT INTO pediment_aduanas (orderlineno,orderno,stockid,nopedimento
						)
						SELECT orderlineno," . $neworder . ",stockid,nopedimento
								From pediment_aduanas
								WHERE orderno = " . $orderNo;
				$Result = DB_query ( $SQLpedimento, $db, $ErrMsg, $DbgMsg, true );
			}
			// Inserta propiedades de pedido anterior
			/*
			 * COMENTE ESTE INSERT POR QUE ESTA DUPLICANDO LOS TRABAJADORES DEL NUEVO PEDIDO*/
			$DetailSQL = "INSERT INTO salesstockproperties (stkcatpropid,orderno,valor,orderlineno,InvoiceValue,typedocument)
					Select salesstockproperties.stkcatpropid,".$neworder.",valor,orderlineno,InvoiceValue,typedocument
					From salesstockproperties
							LEFT JOIN stockcatproperties ON salesstockproperties.stkcatpropid = stockcatproperties.stkcatpropid
					WHERE orderno = " . $orderNo . " 
							AND IFNULL(stockcatproperties.allowduplicate,0) = 1";
					$Result = DB_query($DetailSQL,$db,$ErrMsg,$DbgMsg,true);
			 
			
			// Agregar a tabla de fechas de pedidos
			$qry = "INSERT INTO salesdate(orderno,fecha_solicitud,usersolicitud)
					VALUES(" . $neworder . ",now(),'" . $_SESSION ['UserID'] . "')";
			// echo $qry.'<br>';
			$Result = DB_query ( $qry, $db );
			// Actualizo pedido a status inicial
			
			$sql = "select * from salesfielddate where statusid='" . $_SESSION ['QuotationInicial'] . "'";
			$resultstatus = DB_query ( $sql, $db, $ErrMsg, $DbgMsg, true );
			while ( $RowOrders = DB_fetch_array ( $resultstatus ) ) {
				$sql = "UPDATE salesdate";
				if ($RowOrders [2] == 1) {
					$sql = $sql . " SET " . $RowOrders [0] . "=now() ";
				} else {
					$sql = $sql . " SET " . $RowOrders [0] . "='" . $_SESSION ['UserID'] . "'";
				}
				$sql = $sql . " WHERE orderno= " . $neworder;
				if ($RowOrders [3] == 1) {
					$sql = $sql . " AND " . $RowOrders [0] . " is null";
				}
				$Result = DB_query ( $sql, $db, $ErrMsg, $DbgMsg, true );
			}
			// Actualizo pedido recien creado
			
			$SQL = "UPDATE salesorders 
					SET quotation='" . $_SESSION ['QuotationInicial'] . "' 
					WHERE orderno= '" . $neworder . "'";
			$ErrMsg = _ ( 'ERROR CRITICO' ) . ' ' . _ ( 'ANOTE EL ERROR' ) . ': ' . _ ( 'El encabezado de la venta no se pudo actualizar con el numero de factura' );
			$DbgMsg = _ ( 'El siguiente SQL se utilizo para actualizar la orden de venta' );
			$Result = DB_query ( $SQL, $db, $ErrMsg, $DbgMsg, true );
			
			// Actualiza informacion de fechas
			/**
			 * *********************************************************************
			 */
			$sql = "select * from salesfielddate where statusid='" . $quotation . "'";
			$resultstatus = DB_query ( $sql, $db, $ErrMsg, $DbgMsg, true );
			while ( $RowOrders = DB_fetch_array ( $resultstatus ) ) {
				$sql = "UPDATE salesdate";
				if ($RowOrders [2] == 1) {
					$sql = $sql . " SET " . $RowOrders [0] . "=now() ";
				} else {
					$sql = $sql . " SET " . $RowOrders [0] . "='" . $_SESSION ['UserID'] . "'";
				}
				$sql = $sql . " WHERE orderno= " . $neworder;
				if ($RowOrders [3] == 1) {
					$sql = $sql . " AND " . $RowOrders [0] . " is null";
				}
				// echo '<pre><br>'.$sql;
				$Result = DB_query ( $sql, $db, $ErrMsg, $DbgMsg, true );
			}
			/**
			 * *********************************************************************
			 */
			
			// crear oportunidad 
			$SQL = "INSERT INTO prospect_movimientos (areacod,debtorno,u_proyecto,dia,mes,anio,concepto,descripcion,
					u_user,cargo,prioridad,referencia,periodo_dev,erp,TipoMovimientoId,estimado,
					fecha,currcode,branchcode,fecha_compromiso,grupo_contable,confirmado,
					activo,u_entidad,catcode,idstatus,UserId,fecha_alta,clientcontactid
					)
					Select areacod,debtorno,u_proyecto,'" . date ( "d" ) . "','" . date ( "m" ) . "','" . date ( "Y" ) . "',concepto,descripcion,
							'" . $_SESSION ['UserID'] . "',cargo,prioridad,".$neworder.",periodo_dev,erp,TipoMovimientoId,estimado,
									current_date,currcode,branchcode,fecha_compromiso,grupo_contable,confirmado,
									activo,u_entidad,catcode,idstatus,'" . $_SESSION ['UserID'] . "',current_date,clientcontactid
									FROM prospect_movimientos
									WHERE u_movimiento = '$umov'";
			$r = DB_query ( $SQL, $db );
			$prospectid = DB_Last_Insert_ID ( $db, 'prospect_movimientos', 'u_movimiento' );
			
			$sql2 = "INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
					VALUES ('" . $prospectid . "','Alta de oportunidad:" . $_SESSION ['UserID'] . "@: GENERADA POR DUPLICAR COTIZACION $orderNo',Now(),0,'1','" . $_SESSION ['UserID'] . "','" . $_SESSION ['UserID'] . "','alta')";
			$result2 = DB_query ( $sql2, $db );
			// actualizo pedido con id de prospecto
			$SQL = "UPDATE salesorders
					SET idprospect=" . $prospectid . "
							WHERE orderno= " . $neworder;
			$ErrMsg = _ ( 'ERROR CRITICO' ) . ' ' . _ ( 'ANOTE EL ERROR' ) . ': ' . _ ( 'El encabezado de la  ordern de servicio de venta no se pudo actualizar' );
			$DbgMsg = _ ( 'El siguiente SQL se utilizo para actualizar la orden de venta' );
			$Result = DB_query ( $SQL, $db, $ErrMsg, $DbgMsg, true );
		}
		$ordenesDuplicadas = substr ( $ordenesDuplicadas, 0, strlen ( $ordenesDuplicadas ) - 1 );
		prnMsg ( 'Se ha(n) generado la(s) cotizacion(es) No ' . $ordenesDuplicadas );
		// Finalizo transaccion
		$Result = DB_Txn_Commit ( $db );
	} // fin de cuenta seleccion de pedidos de venta
} // Fin de duplicidad de ordenes de venta
  
// Cancelar pedidos cerrados seleccionados ...
if (isset ( $_POST ['btnCancelarPedidoCerrado'] )) {
	$arrdata = $_POST ['selectedorders'];
	if (empty ( $arrdata ) == false) {
		foreach ( $arrdata as $id ) {
			$sql = "SELECT quotation FROM salesorders WHERE orderno = '$id'";
			$rs = DB_query ( $sql, $db );
			if ($row = DB_fetch_array ( $rs )) {
				// Si es pedido cerrado lo cancelamos
				if ($row ['quotation'] == 0) {
					$sql = "UPDATE salesorders SET quotation = 3 WHERE orderno = '$id'";
					DB_query ( $sql, $db );
					$userCancelado = $_SESSION ['UserID'];
					$sql = "UPDATE salesdate SET fecha_cancelado = NOW(), fecha_canceladomod = NOW(), usercancelado = '$userCancelado', usercanceladomod = '$userCancelado' WHERE orderno = '$id'";
					DB_query ( $sql, $db );
					prnMsg ( 'Se cancelo el pedido cerrado no. ' . $id );
				} else {
					prnMsg ( "El pedido no. $id no es un pedido cerrado, no se pudo cancelar.", "error" );
				}
			}
		}
	}
}
if (isset ( $_POST ['btnGuardarTarjetaRedM'] )) {
	$arrdata = $_POST ['selectedorders'];
       
	if (empty ( $arrdata ) == false) {
		foreach ( $arrdata as $id ) {
                         $noTarRm =$_POST ['trm'.$id];
                         $sql = "UPDATE  salesorders SET placa ='".$noTarRm."'  WHERE orderno = '$id'";
                         $ErrMsg = _ ( 'ERROR CRITICO' ) . ' ' . _ ( 'ANOTE EL ERROR' ) . ': ' . _ ( 'El No. de Tarjeta Red M no se pudo actualizar' );
                         $DbgMsg = _ ( 'El siguiente SQL se utilizo para actualizar el No. de Tarjeta Red M' );
                         $rs = DB_query ( $sql, $db, $ErrMsg, $DbgMsg, true );

                        prnMsg ( 'Se actualizo el No. de Tarjeta Red M del pedido no. ' . $id );
			
		}
	}
}
/**
 * ******************************************
 */
// Criterios de eliminacion
/**
 * ******************************************
 */
?>
<!--Modal cancelar -->

<div class="modal fade" id="ModalCancelar" name="ModalCancelar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document" name="ModalGeneralTam" id="ModalGeneralTam">
    <div class="modal-content">
      <div class="navbar navbar-inverse navbar-static-top">
        <!--Contenido Encabezado-->
        <div class="col-md-12 menu-usuario">
          <span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <div class="nav navbar-nav">
            <div class="title-header">
              <div id="ModalCancelar_Titulo" name="ModalCancelar_Titulo"></div>
            </div>
          </div>
        </div>
        <div class="linea-verde"></div>
      </div>
      <div id="divMensajeOperacion" name="divMensajeOperacion" class="m10"></div>
      <div class="modal-body" id="ModalCancelar_Mensaje" name="ModalCancelar_Mensaje">
        <!--Mensaje o contenido-->
        <div id="msjValidacion" name="msjValidacion"></div>
            
			<div class="col-md-12">
                <component-text-label label="Pase de cobro: " max="9" maxlength="255" id="orderNo" name="orderNo" placeholder="Numero de recibo de pago" readonly></component-text-label>
            </div>
            <div class="row"></div>
            <br>
            <br>
            
            <div class="col-md-12"> 
			<component-textarea-label label="Comentario de cancelación: " max="9" maxlength="255" id="txtComentarioCancel" name="txtComentarioCancel" placeholder="Comentario de cancelación"></component-textarea-label>
		    </div>
			</br>
            </br>
            </br>
			</br>
            </br>
            </br>
      </div>
      <div class="modal-footer">
        <component-button type="button" id="btn" name="btn" onclick="fnAgregarCancelacion()" value="Guardar"></component-button>
        <component-button type="button" id="btn" name="btn" data-dismiss="modal" value="Cerrar"></component-button>
      </div>
    </div>
  </div>
</div>
<!-- TERMINA MODAL DE EDICION -->

<?php

/**
 * ******************************************
 */
// Criterios de consulta nueva
/**
 * ******************************************
 */
?>
<script type="text/javascript" src="javascripts/selectSalesOrder.js"></script>
<!--Modal Agregar/Modificar -->

<div class="modal fade" id="ModalUR" name="ModalUR" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document" name="ModalGeneralTam" id="ModalGeneralTam">
    <div class="modal-content">
      <div class="navbar navbar-inverse navbar-static-top">
        <!--Contenido Encabezado-->
        <div class="col-md-12 menu-usuario">
          <span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <div class="nav navbar-nav">
            <div class="title-header">
              <div id="ModalUR_Titulo" name="ModalUR_Titulo"></div>
            </div>
          </div>
        </div>
        <div class="linea-verde"></div>
      </div>
      <div id="divMensajeOperacion" name="divMensajeOperacion" class="m10"></div>
      <div class="modal-body" id="ModalUR_Mensaje" name="ModalUR_Mensaje">
        <!--Mensaje o contenido-->
        <div id="msjValidacion" name="msjValidacion"></div>
            
            <input type="hidden" id="orderNo" name="orderNo" value="">
            <div class="col-md-10"> 
            	<div class="form-inline row">
			        <div class="col-md-3 col-xs-12">
			            <label>Comentario: </label>
			        </div>
			        <div class="col-md-9 col-xs-12">
			            <textarea class="form-control" id="txtComentario" name="txtComentario" placeholder="Comentario" title="Comentario" rows="5" cols="15" style="width: 100%;"></textarea>
			        </div>
			    </div><br>
				<!-- <component-textarea-label label="Comentario: " id="txtComentario" name="txtComentario" placeholder="Comentario"></component-textarea-label> -->
		    </div>
			</br>
            </br>
            </br>
			</br>
            </br>
            </br>
      </div>
      <div class="modal-footer">
        <component-button type="button" id="btn" name="btn" onclick="fnAgregar()" value="Guardar"></component-button>
        <component-button type="button" id="btn" name="btn" data-dismiss="modal" value="Cerrar"></component-button>
      </div>
    </div>
  </div>
</div>
<!-- TERMINA MODAL DE EDICION -->

<link rel="stylesheet" href="css/listabusqueda.css" />

<div id="formSearch" name="divMensajeOperacion"></div>
<!-- target="_blank" -->
<a href="suficiencia_manual.php" name="Link_NuevoGeneral" id="Link_NuevoGeneral" class="btn btn-primary" style="width: 200px; display: none;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Link Nuevo</a>

<div align="left">
  <!--Panel Busqueda-->
  <div class="panel panel-default">
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
        <div class="col-md-4">
          <div class="form-inline row" style="display: none;">
              <div class="col-md-3">
                  <span><label>Dependencia: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectRazonSocial" name="selectRazonSocial[]" class="form-control selectRazonSocial" onchange="fnCambioRazonSocial()" multiple="multiple">
                  </select>
              </div>
          </div>
          <!-- <br> -->
		  <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>UR: </label></span>
              </div>
              <div class="col-md-9">               
		

			 		<td>
					<select name='UnidNeg[]' id='UnidNeg' class='form-control selectGeneral' multiple="true">
				
					<?php
					$sql = "SELECT t.tagref, t.tagdescription
								FROM tags t, sec_unegsxuser uxu
								WHERE t.tagref=uxu.tagref
								AND uxu.userid='" . $_SESSION ['UserID'] . "'";
					if (isset ( $_POST ['legalbusiness'] ) and $_POST ['legalbusiness'] > 0) {
					$sql = $sql . " and t.legalid = '" . $_POST ['legalbusiness'] . "'";
					}
					$sql = $sql . " ORDER BY tagref";
					$resultTags = DB_query ( $sql, $db, '', '' );
					
					$cbmPostUE = $_POST['UnidNeg'];
					while ( $myrow = DB_fetch_array ( $resultTags ) ) {
						$selected="";
						if (!empty($cbmPostUE)) {
							foreach ($cbmPostUE as $key => $value) {
								if ($value != -1) {
									if ($myrow['tagref'] == $value) {
										$selected="selected";
										break;
									}
								}
							}
						}
					echo "<option ".$selected." value='" . $myrow['tagref'] . "'>" .$myrow['tagref']." - ".$myrow['tagdescription'] . "</option>";

					}
					echo '			</select>
				</td>';

				?>
              </div>
          </div>
		  <br>
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Estatus: </label></span>
              </div>
              <div class="col-md-9">
			<td id='tdestatus'>
			<select name="Quotations[]"  id="Quotations" class="form-control selectGeneral" >
			<?php
			$sql = 'SELECT DISTINCT salesstatus.statusid, salesstatus.statusname, salesstatus.showfunctionid
			FROM  salesstatus
			WHERE salesstatus.activo = 1 and ordenby IS NOT NULL 
			ORDER BY salesstatus.statusid, salesstatus.statusname ASC';
			$resultestatus = DB_query ( $sql, $db, '', '' );
			echo '<option selected Value="-1">' . "Sin selección";
					$selectStatus = array();
				  
				  $cbmPostUE = $_POST['Quotations'];
				   while ($myrow=DB_fetch_array($resultestatus)) {
                            $selected="";
                            if (!empty($cbmPostUE)) {
                                foreach ($cbmPostUE as $key => $value) {
                                    if ($value != -1) {
                                        if ($myrow['statusid'] == $value) {
                                            $selected="selected";
                                            break;
                                        }
                                    }
								}
							
							}
							echo "<option ".$selected." value='" . $myrow['statusid'] . "'>" .$myrow['statusname'] . "</option>";
                    }
					
			echo '				</select>

						</td>'; 
			if (strpos("@".$_SESSION["DatabaseName"], "servillantas")) {
				echo '<td class="texto_lista">UUID: </td>
					<td><input type="text" name="uuids" id="uuids" value="'.$_POST ['uuids'].'" maxlength="36" style="width:327px;" class="form-control"> </td>';
			}
			?>
              </div>
          </div>
          <br>
		<?php
		  $_POST['nocliente'] = (isset($_POST['nocliente'])) ? $_POST['nocliente'] : "";
		?>
          <component-text-label label="No. Contribuyente:" id="nocliente" size='10' name="nocliente" placeholder="No. Contribuyente"  value="<?php echo $_POST ['nocliente'] ?>"></component-text-label>
          <br>
			<div class="form-inline row">
				<div class="col-md-3">
					<span><label>Objeto Principal: </label></span>
				</div>
				<div class="col-md-9">
					<select id="selectObjetoPrincipal" name="selectObjetoPrincipal" class="form-control selectObjetoPrincipal selectGeneral"></select>
				</div>
			</div>
			<br>
			<?php 
				$permisoGenerarPaser = Havepermission($_SESSION ['UserID'], 2536, $db); // Poder editar comentario
				if ($permisoGenerarPaser == 1){?>
					<div class="form-inline row">
						<div class="col-md-3">
							<span><label>Sólo por Obj. Principal: </label></span>
						</div>
						<div class="col-md-9">
							<input type="checkbox" name="onlyObjPrincipal" id="onlyObjPrincipal"  class="form-control" value="1"  <?php echo $_POST ['onlyObjPrincipal'] == '1' ? 'checked = "checked"' : ''; ?> >
						</div>
					</div>

					<?php 
				}
			?>
        </div>
        <div class="col-md-4">
        <?php
		If (isset ( $_REQUEST ['FolioFiscal'] ) and $_REQUEST ['FolioFiscal'] != '') {
			$_REQUEST ['FolioFiscal'] = trim ( $_REQUEST ['FolioFiscal'] );
		} elseif (isset ( $_REQUEST ['OrderNumber'] ) and $_REQUEST ['OrderNumber'] != '') {
			$_REQUEST ['OrderNumber'] = trim ( $_REQUEST ['OrderNumber'] );
			if (! is_numeric ( $_REQUEST ['OrderNumber'] )) {
				echo '<br><b>' . _ ( 'El Numero de Pedido de Venta debe ser numerico' ) . '</b><br>';
				unset ( $_REQUEST ['OrderNumber'] );
				include ('includes/footer.inc');
				exit ();
			} 
		} else {
			If (isset ( $_REQUEST ['SelectedCustomer'] )) {
				$SQL = "SELECT * FROM debtorsmaster WHERE debtorno='" . $_REQUEST ['SelectedCustomer'] . "'";
				$TaxCatQuery = DB_query ( $SQL, $db, $ErrMsg, $DbgMsg, true );
				if ($TaxCatRow = DB_fetch_array ( $TaxCatQuery )) {
					$Name = $TaxCatRow ['name'];
				}
				echo _ ( 'Para el Cliente' ) . ': ' . $_REQUEST ['SelectedCustomer'] . ' - ' . $Name . '<br>';
				echo "<input type=hidden name='SelectedCustomer' value=" . $_REQUEST ['SelectedCustomer'] . '><br>';
			}
		}
		$_POST['OrderNumber'] = (isset($_POST['OrderNumber'])) ? $_POST['OrderNumber'] : "";			
		$_POST['FolioFiscal'] = (isset($_POST['FolioFiscal'])) ? $_POST['FolioFiscal'] : "";
		?>
          <component-text-label label="Pase de Cobro:" id="OrderNumber" name="OrderNumber" placeholder="Pase de Cobro" maxlength=8 size=9 value="<?php echo $_POST ['OrderNumber']; ?>"></component-text-label>
          <br>
          <component-text-label label="Folio Fiscal:" id="FolioFiscal" name="FolioFiscal" placeholder="Folio Fiscal" maxlength=18 size=9 value="<?php echo $_POST ['FolioFiscal'] ?>"></component-text-label>
		  <div class="form-inline row" style="display: none;">
              <div class="col-md-3">
                  <span><label>Razón Social: </label></span>
              </div>
              <div class="col-md-9">
			  <?php

				$sql = "SELECT legalbusinessunit.legalid,
										legalbusinessunit.legalname
									FROM sec_unegsxuser u,
										tags t
									JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid
									WHERE u.tagref = t.tagref
									AND u.userid = '" . $_SESSION ['UserID'] . "'
									GROUP BY legalbusinessunit.legalid,
											legalbusinessunit.legalname
									ORDER BY t.tagref";
				$resulLegal = DB_query ( $sql, $db );
				echo "		<td nowrap style='display:flex;'>
								<select name='legalbusiness' id='legalbusiness' class='form-control selectGeneral'>";
				echo "<option selected value=0>Todas las Razones sociales</option>";
				while ( $myrow = DB_fetch_array ( $resulLegal ) ) {
					if (isset($_POST['legalbusiness']) &&$myrow ['legalid'] == $_POST ['legalbusiness']) {
						echo "<option selected value='" . $myrow ['legalid'] . "'>" . $myrow ['legalname'] . "</option>";
					} else {
						echo "<option value='" . $myrow ['legalid'] . "'>" . $myrow ['legalname'] . "</option>";
					}
				}
				echo "			</select>
								<input type=submit name=btnlegal value='->' class='form-control' style='display:inline; width:initial;'>
							</td>";
			?>
              </div>
          </div>
          <br>
		  <?php
		  
		  $_POST['cliente'] = (isset($_POST['cliente'])) ? $_POST['cliente'] : "";
		?>
		  <component-text-label label="Nombre del Contribuyente:" id="cliente" size='25' name="cliente" placeholder="Nombre del Contribuyente"  value="<?php echo $_POST ['cliente'] ?>"></component-text-label>
        </div>
        <div class="col-md-4">
		  <div class="form-inline row" style="display: none;">
              <div class="col-md-3">
                  <span><label>Vendedor: </label></span>
              </div>
              <div class="col-md-9">
			  <?php               
			  echo "
			<td>
			<select name='SalesMan' id='SalesMan' class='form-control selectGeneral'> ";
			$sql = "SELECT DISTINCT salesmanname,salesmancode
					FROM salesman as sm 
					LEFT JOIN areas as ar ON sm.area=ar.areacode
					JOIN tags as tg ON ar.areacode=tg.areacode
					JOIN sec_unegsxuser as u ON u.tagref = tg.tagref
					WHERE u.userid='" . $_SESSION ['UserID'] . "' AND sm.status = 'Active'
					ORDER BY tg.tagref";
			$result = DB_query ( $sql, $db, '', '' );
			echo "<option selected Value=''>" . "TODOS...";
			while ( $myrow = DB_fetch_array ( $result ) ) {
				if ($myrow ['salesmancode'] == $SalesMan) {
					echo "<option selected Value='" . $myrow ['salesmancode'] . "'>" . $myrow ['salesmanname'] . '</option>';
				} else {
					echo "<option Value='" . $myrow ['salesmancode'] . "'>" . $myrow ['salesmanname'] . '</option>';
				}
			}
			echo '</select>
			</td>';
			?>
              </div>
          </div>
		  
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Usuario: </label></span>
              </div>
              <div class="col-md-9">
			  <?php               
			  echo "
			<td>
				<select name='UserName' id='UserName' class='form-control selectGeneral'> ";
			$sql = "SELECT userid, realname
									FROM www_users";
			if ($permisouser == 0) {
				echo "<option selected Value=''>" . "Sin selección";
			} else {
				echo "<option selected Value=''>" . "Sin selección";
			}
			$result = DB_query ( $sql, $db, '', '' );

			while ( $myrow = DB_fetch_array ( $result ) ) {
				if (isset($_POST['UserName']) && $myrow ['userid'] == $_POST ['UserName']) {
					echo "<option selected Value='" . $myrow ['userid'] . "'>" . $myrow ['realname'] . '</option>';
				} else {
					echo "<option  Value='" . $myrow ['userid'] . "'>" . $myrow ['realname'] . '</option>';
				}
			}
			echo ' </select>
			</td>';
			?>
              </div>
          </div>
		  <br>
		  <component-date-label label="Desde: " type='date' name="txtfechadesde" value="<?php echo $fechadesde ;?>" size='10' ></component-date-label>
          <br>
		  <component-date-label label="Hasta: "  type='date' name="txtfechahasta" value="<?php echo $fechahasta ;?>" size='10'></component-date-label>
     
        </div>
        <div class="row"></div>
        <div align="center">
          <br>
          <component-button type="submit" id="SearchOrders" name="SearchOrders" class="glyphicon glyphicon-search"  value="Buscar Pase de Cobro"></component-button>
		<?php
				if (isset($_POST['Quotations']) and in_array($_POST ['Quotations'], 4)  and $permisoEnviarCorreo == 1) { // Enviar todas la facturas seleccionadas
			echo "<td colspan=2 align=center style='text-align:center'>
				<button type=submit name='SendEmailPedido' value='" . _ ( 'Enviar Por Correo' ) . "' style='cursor:pointer; border:0; background-color:transparent;'>
				<span class='glyphicon glyphicon-envelope'></span>
				</button>
			</td>";
		}
		?>
        </div>
      </div>
    </div>
  </div>

 
  <br>
  
</div>

<?php
/**
 * ******************************************
 */
// Muestra resultados de consulta
/**
 * ******************************************
 */

if (isset ( $_POST ['SearchOrders'] ) or isset( $_POST['SendEmailPedido'] )) {
	$vrArrayssts=array();
	
	$sql = "SELECT *
			FROM  salesstatus
			WHERE statusid IN ( ". implode(',', $Quotations). ")";
	$rstxt = DB_query ( $sql, $db );

	if($_SESSION['UserID']=="desarrollo"){
		//echo '<pre> sql:'.$sql.'</pre>';
	}

	while($reg =DB_fetch_array ( $rstxt )){
		$salesname = $reg ['statusname'];
		$flagCxC = $reg ['invoice'];
		$flagdateinv = $reg ['flagdateinv'];
		$flagventaperdida = $reg ['flagventaperdida'];
		$vrstatusid= $reg ['statusid'];
		array_push($vrArrayssts, array('salesname'=>$salesname, 'flagCxC'=>$flagCxC, 'flagdateinv'=>$flagdateinv, 'flagventaperdida'=>$flagventaperdida, 'statusid'=>$vrstatusid));
	}
	
  
	if (!isset($salesfield)){
		$salesfield="";
	}
	if ($salesfield == '') {
		$salesname = _ ( 'Todos' );
	}

	$vrArrayFlag=array();
	foreach ($vrArrayssts as $value) {

		if (strlen ( $value['flagdateinv'] ) != 0) {
			array_push($vrArrayFlag, $value['statusid']);
			//$flagdateinv = $Quotations;
		}else{
			array_push($vrArrayFlag, $value['flagdateinv']);
		}
	}

	$vrArraysf=array();
	// Trae fechas por status
	$sql = "SELECT *
			FROM  salesfielddate
			WHERE flagupdate=1 AND flagdate=1
			AND statusid IN ( ". implode(',', $vrArrayFlag). ")
			ORDER BY statusid";
	

	$rstxt = DB_query ( $sql, $db );
	while($reg = DB_fetch_array ( $rstxt )){
		$salesfield = $reg ['salesfield'];
		$vrstatusid = $reg ['statusid'];
		if ($salesfield == '') {
			$salesfield = 'fecha_solicitud';
		}
		array_push($vrArraysf, array($salesfield, $vrstatusid));
	}
	$fecha="";
	$vrauxF=false;
	foreach ($vrArraysf as $value) {
		$fecha =$fecha. " 
            WHEN salesdate." . $value[0] . " IS NOT NULL  THEN  salesdate.".$value[0];
		$vrauxF=true;
	}
	if($vrauxF==true){
		$fecha =" ( CASE ". $fecha ." ELSE '' END ) AS fecha,";
	}
	
    if (isset ( $_POST ['FolioFiscal'] ) and strlen ( $_POST ['FolioFiscal'] ) > 0) {
       	$fecha1 = 'fecha_facturado';
    }else{
        $fecha1 = 'fecha_solicitud';
	}
	

	if($orderNumber != ''){
		
	 $SQL = "SELECT DISTINCT salesorders.orderno,
				debtortrans.folio,
				debtorsmaster.name,	
				salesorders.UserRegister,
				www_users.realname,
				salesman.salesmanname,
				custbranch.brname,
				salesorders.customerref,
				salesorders.orddate,
				salesorders.extratext,
				salesorders.deliverydate,
				salesorders.currcode,
				salesorders.comments,
				salesorders.deliverto,
				salesorders.quotation AS estatus,
				debtortrans.id,
				salesorders.printedpackingslip,
				paymentterms.type,
				salesorders.debtorno,
				salesorders.nopedido,
				salesorders.tagref,
				salesorders.idprospect,
				debtortrans.noremisionf,
				salesstatus.statusname,
				salesstatus.statusid,
				salesstatus.invoice,
				" . $fecha . "
				tags.tagdescription,
				case when debtortrans.folio is null then '' else debtortrans.folio end as folio,
				paymentterms.type as tipopago,
				openfunctionid,
				salesstatus.invoice,
				salesstatus.flagopen,
				salesstatus.templateid,
				salesstatus.templateidadvance,
				salesorders.nopedido,
				salesorders.placa,
				SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)* (1-salesorderdetails.discountpercent1)*(1-salesorderdetails.discountpercent2)) + 					(salesorders.taxtotal +salesorders.totaltaxret) 
				 AS ordervalue,
				debtortrans.type,
				debtortrans.transno	,
				case when debtortrans.id is null then 0 else  debtortrans.id end as idfactura,
				case when debtortrans.userid is null then '' else  debtortrans.userid end as useridfactura,
				tags.legalid,
				case when debtortrans.id is null then-1 else debtortrans.prd end as prd,
				cancelfunctionid,
				cancelextrafunctionid,
				flagcancel,
				flagelectronic,
				tags.legalid,
				paymentterms.generateCreditNote,
				debtortrans.transno,
				tags.typeinvoice,
				salesorders.discountcard,
				debtortrans.id,
				custbranch.email,
				SUM(CASE WHEN substring(salesorderdetails.stkcode, 1, 3)='PMV' THEN 1 ELSE 0 END) AS vitalizados,
				if( log_cancelacion_sustitucion.estatus IS NULL OR  log_cancelacion_sustitucion.estatus = 'Finalizado' , log_cancelacion_sustitucion.folio, '') AS folioAnt,
				log_cancelacion_sustitucion.estatus as estatussustitucion,
				valueaddenda.version,
				debtortrans.fechatimbrado,
				IFNULL(RIF,0) AS RIF,
				CONCAT(salesorders.ln_ue, ' - ', tb_cat_unidades_ejecutoras.desc_ue) as desc_ue,
				CONCAT(salesorders.ln_tagref_pase, ' - ', tagsPase.tagdescription) as desc_urPase,
				CONCAT(salesorders.ln_ue_pase, ' - ', tb_cat_unidades_ejecutorasPase.desc_ue) as desc_uePase
			FROM salesorders 
				LEFT JOIN debtortrans ON salesorders.orderno = debtortrans.order_ AND debtortrans.type in (10,110,111,119,410,66,125)
				LEFT JOIN log_cancelacion_sustitucion  ON log_cancelacion_sustitucion.transNo=debtortrans.transno AND  log_cancelacion_sustitucion.type = debtortrans.type
				LEFT JOIN www_users ON www_users.userid=salesorders.UserRegister
				LEFT JOIN salesstatus ON salesstatus.statusid=salesorders.quotation
				LEFT JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ur = salesorders.tagref AND tb_cat_unidades_ejecutoras.ue = salesorders.ln_ue
				LEFT JOIN tags tagsPase ON tagsPase.tagref = salesorders.ln_tagref_pase
				JOIN sec_objetoprincipalxuser ON sec_objetoprincipalxuser.userid = '" . $_SESSION ['UserID'] . "' AND sec_objetoprincipalxuser.loccode = salesorders.fromstkloc
				LEFT JOIN tb_cat_unidades_ejecutoras tb_cat_unidades_ejecutorasPase ON tb_cat_unidades_ejecutorasPase.ur = salesorders.ln_tagref_pase AND tb_cat_unidades_ejecutorasPase.ue = salesorders.ln_ue_pase
				LEFT JOIN salesorderdetails on salesorders.orderno = salesorderdetails.orderno
				LEFT JOIN locations on salesorderdetails.fromstkloc=locations.loccode
				LEFT JOIN salesman ON salesman.salesmancode = salesorders.salesman
				LEFT JOIN tags on tags.tagref=salesorders.tagref
				LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
				LEFT JOIN custbranch ON  debtorsmaster.debtorno = custbranch.debtorno AND salesorders.branchcode = custbranch.branchcode
				LEFT JOIN paymentterms ON paymentterms.termsindicator=salesorders.paytermsindicator
				LEFT JOIN sec_loccxusser ON salesorderdetails.fromstkloc=sec_loccxusser.loccode and  sec_loccxusser.userid ='" . $_SESSION ['UserID'] . "'		
				LEFT JOIN valueaddenda ON valueaddenda.debtorid = debtortrans.id					
			WHERE   1=1 AND salesorders.orderno=" . $orderNumber . "";



	

		$SQL = $SQL . "
			GROUP BY salesorders.orderno,                                
				debtorsmaster.name,
				custbranch.brname,
				salesorders.customerref,
				salesorders.orddate,
				salesorders.deliverydate,
				salesorders.deliverto, 
				salesorders.printedpackingslip,
				salesorders.nopedido";
		if($permisovertodasfacturas){
		$SQL = $SQL . ", debtortrans.folio";
		}

		$SQL = $SQL . " ORDER BY salesorders.orderno, tagdescription ";
	
		$SalesOrdersResult = DB_query ( $SQL, $db, $ErrMsg, '' );

		if ($_SESSION["UserID"] == "desarrollo"){
			//echo "<br>$SQL<pre>";
			// 	print_r($SQL);
			// echo "</pre><br>";
		}
	 
	}else{

				$SQL = "SELECT DISTINCT salesorders.orderno,
				debtortrans.folio,
				debtorsmaster.name,	
				salesorders.UserRegister,
				www_users.realname,
				salesman.salesmanname,
				custbranch.brname,
				salesorders.customerref,
				salesorders.orddate,
				salesorders.extratext,
				salesorders.deliverydate,
				salesorders.currcode,
				salesorders.comments,
				salesorders.deliverto,
				salesorders.quotation AS estatus,
				debtortrans.id,
				salesorders.printedpackingslip,
				paymentterms.type,
				salesorders.debtorno,
				salesorders.nopedido,
				salesorders.tagref,
				salesorders.idprospect,
				debtortrans.noremisionf,
				salesstatus.statusname,
				salesstatus.statusid,
				salesstatus.invoice,
				" . $fecha . "
				tags.tagdescription,
				case when debtortrans.folio is null then '' else debtortrans.folio end as folio,
				paymentterms.type as tipopago,
				openfunctionid,
				salesstatus.invoice,
				salesstatus.flagopen,
				salesstatus.templateid,
				salesstatus.templateidadvance,
				salesorders.nopedido,
				salesorders.placa,
				SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)* (1-salesorderdetails.discountpercent1)*(1-salesorderdetails.discountpercent2)) + 					(salesorders.taxtotal +salesorders.totaltaxret) 
				 AS ordervalue,
				debtortrans.type,
				debtortrans.transno	,
				case when debtortrans.id is null then 0 else  debtortrans.id end as idfactura,
				case when debtortrans.userid is null then '' else  debtortrans.userid end as useridfactura,
				tags.legalid,
				case when debtortrans.id is null then-1 else debtortrans.prd end as prd,
				cancelfunctionid,
				cancelextrafunctionid,
				flagcancel,
				flagelectronic,
				tags.legalid,
				paymentterms.generateCreditNote,
				debtortrans.transno,
				tags.typeinvoice,
				salesorders.discountcard,
				debtortrans.id,
				custbranch.email,
				SUM(CASE WHEN substring(salesorderdetails.stkcode, 1, 3)='PMV' THEN 1 ELSE 0 END) AS vitalizados,
				if( log_cancelacion_sustitucion.estatus IS NULL OR  log_cancelacion_sustitucion.estatus = 'Finalizado' , log_cancelacion_sustitucion.folio, '') AS folioAnt,
				log_cancelacion_sustitucion.estatus as estatussustitucion,
				valueaddenda.version,
				debtortrans.fechatimbrado,
				IFNULL(RIF,0) AS RIF,
				CONCAT(salesorders.ln_ue, ' - ', tb_cat_unidades_ejecutoras.desc_ue) as desc_ue,
				CONCAT(salesorders.ln_tagref_pase, ' - ', tagsPase.tagdescription) as desc_urPase,
				CONCAT(salesorders.ln_ue_pase, ' - ', tb_cat_unidades_ejecutorasPase.desc_ue) as desc_uePase
			FROM salesorders 
				LEFT JOIN debtortrans ON salesorders.orderno = debtortrans.order_ AND debtortrans.type in (10,110,111,119,410,66,125)
				LEFT JOIN log_cancelacion_sustitucion  ON log_cancelacion_sustitucion.transNo=debtortrans.transno AND  log_cancelacion_sustitucion.type = debtortrans.type
				LEFT JOIN www_users ON www_users.userid=salesorders.UserRegister
				LEFT JOIN salesstatus ON salesstatus.statusid=salesorders.quotation
				LEFT JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ur = salesorders.tagref AND tb_cat_unidades_ejecutoras.ue = salesorders.ln_ue
				LEFT JOIN tags tagsPase ON tagsPase.tagref = salesorders.ln_tagref_pase
				JOIN sec_objetoprincipalxuser ON sec_objetoprincipalxuser.userid = '" . $_SESSION ['UserID'] . "' AND sec_objetoprincipalxuser.loccode = salesorders.fromstkloc
				LEFT JOIN tb_cat_unidades_ejecutoras tb_cat_unidades_ejecutorasPase ON tb_cat_unidades_ejecutorasPase.ur = salesorders.ln_tagref_pase AND tb_cat_unidades_ejecutorasPase.ue = salesorders.ln_ue_pase
				LEFT JOIN (
						SELECT * 
						FROM salesdate 
						WHERE ((salesdate.".$fecha1.">= '" . $fechaini . "' and salesdate.".$fecha1."<='" . $fechafin . "')";


		$SQL  = $SQL . 	")";


		$SQL  = $SQL . " GROUP BY orderno
					) as salesdate ON salesdate.orderno=salesorders.orderno
				LEFT JOIN salesorderdetails on salesorders.orderno = salesorderdetails.orderno
				LEFT JOIN locations on salesorderdetails.fromstkloc=locations.loccode
				LEFT JOIN salesman ON salesman.salesmancode = salesorders.salesman
				LEFT JOIN tags on tags.tagref=salesorders.tagref
				LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
				LEFT JOIN custbranch ON  debtorsmaster.debtorno = custbranch.debtorno AND salesorders.branchcode = custbranch.branchcode
				LEFT JOIN paymentterms ON paymentterms.termsindicator=salesorders.paytermsindicator
				LEFT JOIN sec_loccxusser ON salesorderdetails.fromstkloc=sec_loccxusser.loccode and  sec_loccxusser.userid ='" . $_SESSION ['UserID'] . "'		
				LEFT JOIN valueaddenda ON valueaddenda.debtorid = debtortrans.id					
			WHERE   1=1 AND salesorders.orddate >= '" . $fechaini . "' AND salesorders.orddate <='" . $fechafin. "'
			AND salesorders.fromstkloc in (SELECT loccode FROM sec_objetoprincipalxuser WHERE sec_objetoprincipalxuser.userid ='" . $_SESSION ['UserID'] . "')";
			

		$validafecha = true;
		//busqueda por folio fiscal CGA 092016
		if (isset ( $_POST ['FolioFiscal'] ) and strlen ( $_POST ['FolioFiscal'] ) > 0) {
		$SQL = $SQL . " AND (debtortrans.folio like '%" . $_REQUEST ['FolioFiscal'] . "%'
				OR replace(debtortrans.folio,'|','') like '%" . $_REQUEST ['FolioFiscal'] . "%')";
		$validafecha = false;
		}

		if (strlen ( $_POST ['OrderNumber'] ) > 0) {
		$SQL = $SQL . " AND salesorders.orderno=" . $_REQUEST ['OrderNumber'];
		$validafecha = false;
		}

		if (isset($_POST['selectObjetoPrincipal']) && !empty($_POST['selectObjetoPrincipal'])) {
			$SQL .= " AND salesorders.fromstkloc = '".$_POST['selectObjetoPrincipal']."' ";
		}
		$vrFechas="";
		/*Fechas que se tomaban anterior mente pra la busqueda*/
		$estatus = "-1";
		foreach ($vrArrayssts as $value) {
		$estatus .= ",".$value['statusid'];

		}
		if ($estatus <> '-1' ) {

		//if (strlen ( $_POST ['OrderNumber'] ) == 0) {
			$SQL .= " AND salesorders.quotation IN (".$estatus.")";

		//}
		}
		if($_POST['onlyObjPrincipal'] != '1'){
			if (isset($_POST['UnidNeg'])) {
				foreach ($_POST['UnidNeg'] as $supplierId) {
					$typesflag[] = "'$supplierId'";
				}
				$SQL = $SQL . " AND salesorders.ln_tagref_pase IN (" . implode(',', $typesflag) . ") ";
			}
		}
			

		if (strlen ( $SalesMan ) > 0) {
		$SQL = $SQL . " AND salesorders.salesman = '" . $SalesMan . "'";
		}

		if (strlen ( $UserName ) > 0) {
		$SQL = $SQL . " AND salesorders.UserRegister = '" . $UserName . "'";
		}

		if (isset ( $_POST ['nocliente'] ) and strlen ( $_POST ['nocliente'] ) > 0) {
		$SQL = $SQL . " AND salesorders.debtorno like '%" . $_POST ['nocliente'] . "%'";
		}
		if (isset ( $_POST ['cliente'] ) and strlen ( $_POST ['cliente'] ) > 0) {
		$SQL .= " AND custbranch.brname like '%" . $_POST ['cliente'] . "%'";
		}
		if($_POST['onlyObjPrincipal'] != '1'){

			if (isset ( $_POST ['legalbusiness'] ) and $_POST ['legalbusiness'] != 0) {
			$SQL .= " AND tags.legalid = '" . $_POST ['legalbusiness'] . "'";
			}
		}
		$SQL = $SQL . "
			GROUP BY salesorders.orderno,                                
				debtorsmaster.name,
				custbranch.brname,
				salesorders.customerref,
				salesorders.orddate,
				salesorders.deliverydate,
				salesorders.deliverto, 
				salesorders.printedpackingslip,
				salesorders.nopedido";
		if($permisovertodasfacturas){
		$SQL = $SQL . ", debtortrans.folio";
		}

		$SQL = $SQL . " ORDER BY salesorders.orderno, tagdescription ";
	
		$SalesOrdersResult = DB_query ( $SQL, $db, $ErrMsg, '' );

	
	}

	// echo "<pre>".$SQL."</pre>";

	
   
	?>
	<?php
	if (db_num_rows ( $SalesOrdersResult ) > 0) {
		$vrAuxCi=0;
        $vrAuxCf=0;
		
?>


<?php
		echo '<div id="app" style="overflow-x:scroll;overflow-y:scroll;">';
		?>
	 	 <div class="row"></div>
            <div align="center">
             <br>
             <a class="glyphicon glyphicon-save-file btn btn-default botonVerde" href="#" id="test" onClick="javascript:fnExcelReport();">Descargar</a>
        <br>
        <br>
        <br>
        </div>

		<table id="tablaRecibos" class="table table-bordered" >
		<tr class="header-verde">
		<th colspan="15"> 
			Buscar: 
			<input type="text" id="txtFiltroCuentas" name="txtFiltroCuentas"  value="" placeholder="" autocomplete="off"  style="color:black;width:170px;">
			</th>
		</tr>
		
		<?php
		$liga_TIcket ="";
		if($permiso_ticket_fiscal==1){
			$liga_TIcket = "<th style='text-align: center;'  nowrap >" . _ ( 'Ticket Fiscal' ) . "</th>";
		}
		
		$tableheader = "<tr class='header-verde'>";

		if (isset( $_POST['SendEmailPedido'] )) { //Enviar por correo el pedido
			$tableheader = $tableheader . "<th style='text-align: center;' nowrap>Envio <br> Email</th>";
            $vrAuxCi++;
		}

		if ($permisoLogPartida == 1) { // Ver log pedido

			$tableheader = $tableheader . "<th style='text-align: center;' nowrap>Log <br> Pedido</th>";
            $vrAuxCi++;
		}
		// <th nowrap style='display: none;'>" . _ ( 'O.C. Cliente' ) . " #</th>";
		$tableheader = $tableheader . "<th style='text-align: center;' nowrap>" . _ ( 'Sel' ) . "</th>
						<th style='text-align: center;' nowrap>" . _ ( 'Pase de Cobro' ) . "</th>
						<th style='text-align: center;' nowrap>" . _ ( 'Fecha' ) . "</th>
						<th style='text-align: center;' nowrap>" . _ ( 'UR' ) . "</th>
						<th style='text-align: center;' nowrap>" . _ ( 'UE' ) . "</th>
						<th style='display: none;' nowrap>" . _ ( 'Folio' ) . "</th>
						<th style='text-align: center;' nowrap>" . _ ( 'Imprimir' ) . "</th>
								".$liga_TIcket."
						<th style='width: 300px !important;' nowrap>" . _ ( 'No. Contribuyente' ) . "</th>
						";
       
        $vrAuxCi=$vrAuxCi +10;
    
		if ($PerMosBombeoProveedor == 1) {
			$tableheader = $tableheader."<th style='text-align: center;' nowrap>"._ ( 'Bombeo' )."</th>
				<th style='text-align: center;'  nowrap>" . _ ( 'Proveedor' ) . "</th>";
            $vrAuxCi=$vrAuxCi +2;
		}
		// <th  nowrap style='display: none;'>" . _ ( 'Vendedor' ) . "</th>
		// <th  nowrap style='display: none;'>" . _ ( 'Pagares' ) . "</th>
		$tableheader = $tableheader . "<th style='text-align: center;' nowrap>" . _ ( 'Usuario' ) . "</th>
						<th  nowrap>" . _ ( 'Total' ) . "</th>
						<th style='text-align: center;' nowrap>" . _ ( 'Comentarios' ) . "</th>
						<th style='text-align: center;' nowrap>" . _ ( 'Periodo' ) . "</th>";
		// if ($_SESSION ['DatabaseName'] == "gruposervillantas_DES" || $_SESSION ['DatabaseName'] == "gruposervillantas"){
		$tableheader = $tableheader . "<th style='text-align: center;' >" . _ ( 'Estatus' ) . "</th>";
        // $tableheader = $tableheader . "<th style='text-align: center;' >" . _ ( 'Estatus Cancel' ) . "</th>";
		// }
		
		// $tableheader = $tableheader . "<th nowrap style='display: none;'>" . _ ( 'VHO' ) . "</th>";
		$vrAuxCf = 3;
		if ($_SESSION ['ShowProspect'] == 1 and $oportunidadprospectoPermiso == 1) {
			$tableheader = $tableheader . "<th style='text-align: center;'>" . _ ( 'Oportunidad' ) . "</th>";
            $vrAuxCf++;
		}
		// if ($permisocancelar == 1) {
			$tableheader = $tableheader . "<th style='text-align: center;'>" . _ ( 'Cancelar' ) . "</th>";
			$tableheader = $tableheader . "<th style='text-align: center;'>" . _ ( 'Modificar' ) . "</th>";
			
            // $vrAuxCf++;
		// }
		
		//Quitamos la condicion de flagventaperdida ==1
		if ($enviaventaperdida== 1) {
			$tableheader = $tableheader . "<th >" . _ ( 'Venta <br> Perdida' ) . "</th>";
            $vrAuxCf++;
		}
		if ($modificarvendedores == 1) {
			$tableheader = $tableheader . "<th >" . _ ( 'Mod<br>Vend' ) . "</th>";
            $vrAuxCf++;
		}
		if ($modificartrabajadores == 1) {
			$tableheader = $tableheader . "<th >" . _ ( 'Mod<br>Trab' ) . "</th>";
            $vrAuxCf++;
		}
		
		if ($permisoCambiarUsuario == 1) {
			$tableheader = $tableheader . "<th >" . _ ( 'Mod<br>Usua' ) . "</th>";
            $vrAuxCf++;
		}
		
		if ($Exportaexcel == 1) {
			$tableheader = $tableheader . "<th >" . _ ( 'Exporta<br>Excel' ) . "</th>";
            $vrAuxCf++;
		}
		
		if ($add_datosfactura == 1) {
			$tableheader = $tableheader . "<th colspan=4 >" . _ ( 'Mod<br>Inf Factura' ) . "</th>";
            $vrAuxCf = $vrAuxCf+4;
		}
		
		if ($permisoimprimeservicio == 1) {
			$tableheader = $tableheader . "<th >" . _ ( 'Orden Servicio' ) . "</th>";
            $vrAuxCf++;
		}
		
		if ($permisodesbloqueapedido == 1) {
			$tableheader = $tableheader . "<th >" . _ ( 'Desbloquear' ) . "</th>";
            $vrAuxCf++;
		}

		$tableheader = $tableheader . '</tr>';
		?>
		<tbody id="mytable">

		<?php
		echo $tableheader;
		$j = 1;
		$k = 0; // row colour counter
		$montoTotalUSD = 0;
		$montoTotal = 0;
		$indextable = 0;

		while ( $myrow = DB_fetch_array ( $SalesOrdersResult ) ) {

			if($myrow ['prd'] == -1 ){
				$date = date('d/m/Y', strtotime($myrow['orddate']));
				$PeriodNo = GetPeriodByDate ( $date, $db );  
				$period = $PeriodNo ;
			}else{
				$period = $myrow ['prd'];
			}
			$statusPeriodo = TraestatusPeriod ( $myrow ['legalid'], $period, $db );

			$arrayStatus = array(0,1,2,7,18);

			if ($statusPeriodo == 1 ){
				
				if(in_array($myrow ['statusid'],$arrayStatus)){
					echo "si existe";
					$flagCancelConta = 0;
				}else{
					$flagCancelConta = 1;
				}
			}else{
				$flagCancelConta = 0;
			}
			$vitalizado = "";
            $vrIDvitalizado = "";
			if ($myrow["vitalizados"]>0){
				echo '<tr bgcolor="lightblue">';
				$vitalizado= "<font color='blue'>(vitalizado)</font>";
                $vrIDvitalizado = '&fgvitalizado=1';
			}
			else if ($indextable == 0) {
				echo '<tr bgcolor="eeeeee">';
				$indextable = 1;
			} else {
				echo '<tr bgcolor="ffffff">';
				$indextable = 0;
			}

			if (isset( $_POST['SendEmailPedido'] )) { //Enviar por correo el pedido

				if ($myrow ['estatus'] == 1) {
					//Enviar cotizacion
					echo "<td class='numero_normal'>--</td>";
				}else{
					//Funcion para envio del correo
					$envio = SendMailFactura($myrow ['id'], $myrow ['email'], $$myrow ['debtorno'], 0, $db);
					if ($envio['success'] == 1) {
						echo "<td class='numero_normal'>Enviado</td>";
					}else{
						echo "<td class='numero_normal'>Error Envio</td>";
					}
				}
			}

			if ($permisoLogPartida == 1) { // Ver log pedido
				echo "<td class='numero_normal'><button type='button' class='btn btn-xs btn-primary' onclick='mostrar_log(".$myrow ['orderno'].")'><span class='glyphicon glyphicon-plus'></span></button>";

				//Crear contenido tabla a mostrar log
				$SQLLog = "SELECT salesorderdetails_log.orderno, www_users.realname, salesorderdetails_log.actualdispatchdate, salesorderdetails_log.stkcode, locations.locationname, 
						salesorderdetails_log.fromstkloc, salesorderdetails_log.qtyinvoiced, salesorderdetails_log.unitprice, salesorderdetails_log.quantity, 
						salesorderdetails_log.discountpercent, salesorderdetails_log.discountpercent1, salesorderdetails_log.discountpercent2,
						salesorderdetails_log.movimiento
						FROM salesorderdetails_log
						LEFT JOIN locations ON locations.loccode = salesorderdetails_log.fromstkloc
						LEFT JOIN www_users ON www_users.userid = salesorderdetails_log.userid
						WHERE orderno = '".$myrow ['orderno']."'
						GROUP BY salesorderdetails_log.orderlineno, salesorderdetails_log.movimiento, salesorderdetails_log.stkcode,
						salesorderdetails_log.unitprice, salesorderdetails_log.quantity, salesorderdetails_log.discountpercent, 
						salesorderdetails_log.discountpercent1, salesorderdetails_log.discountpercent2
						ORDER BY actualdispatchdate";

				$ErrMsg = "No se obtuvieron los registros del log del pedido";
				$ResultLog = DB_query ( $SQLLog, $db, $ErrMsg, '');

				$contenidoTabla = "";
				$styleTabla = "style='text-align: center;'";
				$num = 1;
				if (db_num_rows ( $ResultLog ) > 0) {
					while ( $rowLog = DB_fetch_array ( $ResultLog ) ) {
						$contenidoTabla = $contenidoTabla. 
											"<tr><td ".$styleTabla.">".$num."</td><td ".$styleTabla.">".$rowLog['realname']."</td><td ".$styleTabla.">".$rowLog['movimiento']."</td><td ".$styleTabla.">".$rowLog['actualdispatchdate']."</td><td ".$styleTabla.">".$rowLog['stkcode']."</td><td ".$styleTabla.">".$rowLog['locationname']."</td><td ".$styleTabla.">$ ".$rowLog['unitprice']."</td><td ".$styleTabla.">".$rowLog['quantity']."</td><td ".$styleTabla.">".($rowLog['discountpercent'] * 100)."%</td><td ".$styleTabla.">".($rowLog['discountpercent1'] * 100)."%</td><td ".$styleTabla.">".($rowLog['discountpercent2'] * 100)."%</td></tr>";
						$num ++;
					}
				}else{
					$contenidoTabla = $contenidoTabla . "<tr><td>".$num."</td><td>Sin</td><td>Informacion</td><td>Para</td><td>Mostrar</td><td></td><td></td></tr>";
				}

				echo "<textarea rows='1' name='txtContenidoTabla_".$myrow ['orderno']."' id='txtContenidoTabla_".$myrow ['orderno']."' style='display: none;'>".$contenidoTabla."</textarea>";
				echo "</td>";
			}
			
			echo '<td class="numero_normal"><input type="checkbox" name="selectedorders[]" value="' . $myrow ['orderno'] . '"></td>';
			
			$ModifyPage = $rootpath . "/" . $paginapedidos . "?" . SID . '&ModifyOrderNumber=' . $myrow ['orderno'];
			
			// si tiene permiso para abrir pedido y el pedido aun cuenta con atributo de modificacion
			if (Havepermission ( $_SESSION ['UserID'], $myrow ['openfunctionid'], $db ) == 1 and $myrow ['flagopen'] == 1) {
				echo '<td class="numero_normal"><a href=' . $ModifyPage . ' > ' . $myrow ['orderno'] . "<br>".$vitalizado.'</a></td>';
			} else {
				echo '<td class="numero_normal">' . $myrow ['orderno'] . "<br>". $vitalizado . '</td>';
			}

			echo '<td class="numero_normal" nowrap>' . $myrow ['orddate'] . '</td>';
			echo '<td class="texto_normal2">' .$myrow ['desc_urPase'] . '</td>';
			echo '<td class="texto_normal2">' . $myrow ['desc_uePase'] . '</td>';
			$tagref = $myrow ['tagref'];
			
			if (($myrow ['idfactura']) > 0) {
				// $email_link ="<a target='_blank' href='SendEmail.php?tagref=$tagref&transno=".$myrow['orderno']."&debtorno=".$myrow['debtorno']."'><img src='part_pics/Mail-Forward.png' border=0></a>";
				$SendInvoiceByMailFile = "SendInvoiceByMail.php";
				$EnvioXML = $rootpath . '/' . $SendInvoiceByMailFile . '?id=' . $myrow ['idfactura'];
	
				$EnvioXML = "&nbsp;&nbsp;&nbsp;<a target='_blank'  href=" . $EnvioXML . "><span class='glyphicon glyphicon-envelope'></span></a>";

				if($_SESSION['ImpTiketPV']==1 and $myrow ['type']==125){
                    $linkfactura = 'PDFSalesTicket.php';
                    $linkfactura = $rootpath . '/' . $linkfactura . '?tipodocto=1&OrderNo=' . $myrow ['orderno'] . '&TransNo=' . $myrow ['transno'] . '&Type=' . $myrow ['type'] . '&Tagref=' . $tagref;
                               
                }else{
                	$linkfactura = 'PDFInvoice.php';
					$linkfactura = $rootpath . '/' . $linkfactura . '?tipodocto=1&OrderNo=' . $myrow ['orderno'] . '&TransNo=' . $myrow ['transno'] . '&Type=' . $myrow ['type'] . '&tagref=' . $tagref;


                }               
				echo '<td class="numero_normal" nowrap style="display: none;" >';

				//echo '<a target="_blank" style="display: none;" id="1'.$myrow ['folio'].'"  href=' . $linkfactura . '><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir' ) . '" alt="">' . $myrow ['folio'] . '</a> ';
				//Restringuir por permiso para placacentro
                $noSustitucion = 0;
				if($_SESSION['DatabaseName'] == 'erpplacacentro_DES' or $_SESSION['DatabaseName'] == 'erpplacacentro_CAPA' or $_SESSION['DatabaseName'] == 'erpplacacentro'){
					if(Havepermission($_SESSION['UserID'],2043,$db)=="1"){
						echo '<a target="_blank" href=' . $linkfactura . '><span class="glyphicon glyphicon-print"></span>' . $myrow ['folio'] . '</a> ' . $EnvioXML ;
                        if($myrow ['folioAnt'] !=''){
                            echo '<br><span class="linkfactura">Folio Anterior:</span> <p> <a target="_blank"   href=' . $linkfactura.'&sustitucion=1' . '>'. $myrow ['folioAnt'] . '</a></p>';
                            $noSustitucion = 1;
                        
                        }
                        echo  '</td>';
					}else{
						echo  $myrow ['folio'] .'</td>';

					}
                }
                elseif($_SESSION['DatabaseName'] == 'erpcodigoqro_DES' or $_SESSION['DatabaseName'] == 'erpcodigoqro_CAPA' or $_SESSION['DatabaseName'] == 'erpcodigoqro')
                {
                	    	// redrogo
	           		?>		
						<button style="outline: none; border:0;" name="<?php echo str_replace("|", "", $myrow ['folio']);?>" id="show-modal" v-on:click.prevent="abrirModal($event);">
						<span class="glyphicon glyphicon-print"></span>
						<?php echo $myrow ['folio'];?>					
						</button>				 
					<?php
					echo '<a target="_blank" style="display: none;" id="1'.str_replace("|", "",$myrow ['folio']).'"  href=' . $linkfactura."&mostrarCodigoCliente=1". '><span class="glyphicon glyphicon-print"></span>' . $myrow ['folio'] . '</a> ';
					echo '<a target="_blank" style="display: none;" id="2'.str_replace("|", "",$myrow ['folio']).'"  href=' . $linkfactura."&mostrarCodigoCliente=0". '><span class="glyphicon glyphicon-print"></span>' . $myrow ['folio'] . '</a> ' . $EnvioXML . ' ';
                    if($myrow ['folioAnt'] !=''){
                        echo '<br><span class="linkfactura">Folio Anterior:</span> <p> <a target="_blank"   href=' . $linkfactura.'&sustitucion=1' . '>'. $myrow ['folioAnt'] . '</a></p>';
                        $noSustitucion = 1;
                    }
                    echo "</td>";
					
                }
                else{
            				            					
                		echo '<a target="_blank"   href=' . $linkfactura . '><span class="glyphicon glyphicon-print"></span>' . $myrow ['folio'] . '</a> ' . $EnvioXML . ' ';
                		if($myrow ['folioAnt'] !=''){
                			echo '<br><span class="linkfactura">Folio Anterior:</span> <p> <a target="_blank"   href=' . $linkfactura.'&sustitucion=1' . '>'. $myrow ['folioAnt'] . '</a></p>';
                            $noSustitucion = 1;
                		}
                        echo " </td>";

		        }
				
				//echo '<a target="_blank" style="display: none;" id="1'.$myrow ['folio'].'"  href=' . $linkfactura . '><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir' ) . '" alt="">' . $myrow ['folio'] . '</a> ';
				//echo '<a target="_blank"  id="2'.$myrow ['folio'].'"  href=' . $linkfactura . '><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir' ) . '" alt="">' . $myrow ['folio'] . '</a> ' . $EnvioXML . '</td>';

			} else {
				$email_link = "<a target='_blank' href='SendEmailV2_0.php?tagref=$tagref&transno=" . $myrow ['orderno'] . "&debtorno=" . $myrow ['debtorno'] . "'><span class='glyphicon glyphicon-envelope'></span></a>";
																																	
				echo '<td style="display: none;" class="numero_normal">' . $myrow ['folio'] . ' ' . $email_link . '</td>';
			}
			// Links de impresion////
			//echo "<br>ENTRA: ";
			$urlcotizacion = HavepermissionURLV2($_SESSION['UserID'], '1884', $db);
			//echo "<br>ENTRA2 ";
			if ($urlcotizacion == ""){
				$urlcotizacion = 'PDFCotizacionTemplateV3.php';
			}

			$liga = $urlcotizacion . '?tipodocto=' . $myrow ['templateid'] . '&';
			$liga2 = $urlcotizacion . '?tipodocto=' . $myrow ['templateidadvance'] . '&';
			
			if ($_SESSION ['TypeQuotation'] == 0) {
				$PrintQuotation = $rootpath . '/' . $liga . '&' . SID . 'PrintPDF=Yes&TransNo=' . $myrow ['orderno'] . '&Tagref=' . $myrow ['tagref'] . '&legalid=' . $myrow ['legalid'];
				$PrintDispatchNote = $rootpath . '/' . $liga2 . '&' . SID . 'PrintPDF=Yes&TransNo=' . $myrow ['orderno'] . '&Tagref=' . $myrow ['tagref'] . '&legalid=' . $myrow ['legalid'];
			} else {
				$liga = GetUrlToPrint2 ( $tagref, 10, $db );
				$PrintQuotation = $rootpath . '/' . $liga . '&' . SID . 'PrintPDF=Yes&TransNo=' . $myrow ['orderno'] . '&Tagref=' . $myrow ['tagref'] . '&legalid=' . $myrow ['legalid'];
				$PrintDispatchNote = $rootpath . '/' . $liga . '&' . SID . 'PrintPDF=Yes&TransNo=' . $myrow ['orderno'] . '&Tagref=' . $myrow ['tagref'] . '&legalid=' . $myrow ['legalid'];
			}
			
			if ($myrow ['templateid'] == $myrow ['templateidadvance']) {				
				echo '<td class="numero_normal">
				<a target="_blank"  href=' . $PrintQuotation . '><span class="glyphicon glyphicon-print"></span></a>' . _ ( 'Imprimir' ) . '</td>';
			} else {
				// if ($_SESSION ['DatabaseName'] <> "erpmservice_DIST" AND $_SESSION ['DatabaseName'] <> "erpmservice_DES" AND $_SESSION ['DatabaseName'] <> "erpmservice" AND $_SESSION ['DatabaseName'] <> "erpmservice_CAPA") {

				echo '<td>							  	 								  			 						
				<a style="display: none;" target="_blank" href=' . $PrintQuotation . '><span class="glyphicon glyphicon-print"></span>' . _ ( 'Imprimir (Simple)' ) . '</a>
				<a target="_blank" href=' . $PrintDispatchNote . '><span class="glyphicon glyphicon-print"></span>' . _ ( 'Imprimir' ) . '</a></td>';
			
			}
			if($permiso_ticket_fiscal==1){
				echo '<td class="numero_normal"><a  target="_blank" href="PDFInvoiceticketV2_0.php?PrintPDF=Yes&OrderNo=' . $myrow ['orderno'] . '&TransNo=' . $myrow ['transno'] . '&Type=' .  $myrow ['type']  . '&Tagref=' . $myrow ['tagref'] . '">' . _ ( 'Ticket Fiscal' ) . ' (' . _ ( 'Termico' ) . ')' . '<br><span class="glyphicon glyphicon-print"></span></a></td>';
			}
		
                        //
			// echo '<td class="numero_normal">' . $myrow ['tipopago'] . '</td>';
			//***
			$dato_columna_cliente=$myrow ['debtorno']." - ".$myrow ['name'] ;
			if($myrow ['brname']!="" && $myrow ['brname']!=$myrow ['name']){
				$dato_columna_cliente.= ' / ' . $myrow ['brname'];
			}
			echo '<td class="texto_normal2">' .  $dato_columna_cliente . '</td>';

			echo '<td class="texto_normal2">' .$myrow ['UserRegister']. ' - ' .$myrow ['realname'] . '</td>';
			
			if ($myrow ['statusid'] == 3) {
				echo '<td class="numero_celda">$ 0.00</td>';
			}else{
				echo '<td class="numero_celda">$' . number_format ( $myrow ['ordervalue'], 2 ) . '</td>';
			}
			echo '<td class="numero_normal">' . $myrow ['comments'] . '</td>';


			$SQL = "SELECT  cancelStatus FROM log_cancelInvoice WHERE id ='".$myrow ['idfactura']."' ORDER BY reg desc limit 1";
			
			$SQL = "SELECT log_cancelInvoice.*
            FROM  debtortrans
            INNER JOIN log_cancelInvoice ON debtortrans.id=log_cancelInvoice.id
            WHERE log_cancelInvoice.id ='".$myrow ['idfactura']."'
            ORDER BY reg DESC
            LIMIT 1";
            //echo "<br> CONS: ". $SQL ;
            $resultCancel = DB_query ( $SQL, $db, '', '' );
            $myRowCancel = DB_fetch_array($resultCancel);

            // echo '<td class="texto_normal2">' . $myRowCancel ['cancelStatus'] . '</td>';
			// }
			
			$cambiarImprimeVehicleLink = "";
			// if ($_POST['Quotations'] == 6 || $_POST['Quotations'] == 9 || $_POST['Quotations'] == 8) {
			$jsWindowOpen = 'var win = window.open("ChangePrintVehicle.php?orderno=' . $myrow ['orderno'] . '", "vehiculo", "menubar=0, scrollbars=1, resizable=0, width=400, height=500"); win.focus();';
			$cambiarImprimeVehicleLink = "<a href='#' onclick='$jsWindowOpen'>" . _ ( "" ) . "<img title='Imprimir Vehiculo' src='images/imgs/proveedores.png' width='25'  height='20' border=0></a><br />";
			// echo '<td class="numero_normal" style="display: none;">' . $cambiarImprimeVehicleLink . '</td>';
			// }
			if ($myrow ['estatus'] != 3) {
				if ($myrow ['currcode']=="USD"){
					$montoTotalUSD = $montoTotalUSD + $myrow ['ordervalue'];
				}else{
					$montoTotal = $montoTotal + $myrow ['ordervalue'];
				}
			}
			
			if ($myrow ['idprospect'] > 0) {
				$oportunidad = "<a target='_blank' href='" . $oportunidadprospecto . "?u_movimiento=" . $myrow ['idprospect'] . "'><img title='Ingresar a Oportunidad' src='images/user_24x32.gif' border=0></a>";
			} else {
				$oportunidad = ' ';
			}
			// $Cancelar = "&nbsp;&nbsp;";
			if ($_SESSION ['ShowProspect'] == 1 and $oportunidadprospectoPermiso == 1) {
				echo '<td style="text-align:center">' . $oportunidad . '</td>';
			}

			$fechaini = new DateTime($myrow ['fechatimbrado']);//fecha inicial
			$fechafin = new DateTime("now");
			$fechafin->format("Y-m-d H:i:s");
			// $can = 0;

			$intervalo = $fechaini->diff($fechafin);
			$year =  $intervalo->format('%Y');
			$month = $intervalo->format('%m');
			$day = $intervalo->format('%d');
			$hour = $intervalo->format('%H');
			$Cancelar  = "";
            // PROCESO DEL VITALIZADO PARA QUE NO PERMITA CANCELAR PEDIDOS DE VENTA
            $vrVitalizadoC=-1;
            if ($myrow["vitalizados"]>0){
                $vrVitalizadoC=1;
                $sqlVital = "SELECT SUM(purchorderdetails.quantityrecd) AS recibido,purchorders.orderno
                        FROM purchorders
                        LEFT JOIN purchorderdetails ON purchorders.orderno=purchorderdetails.orderno
                        WHERE purchorders.requisitionno=".$myrow ['orderno'];

                $resultVital = DB_query($sqlVital,$db);
                if (DB_num_rows($resultVital)>0) {
                    $rowVital = DB_fetch_array($resultVital);
                    if($rowVital['recibido']>0){
                        $vrVitalizadoC=0;   
                    }
                }
            }

			// Validaciones para cancelar documento cxc y/o pedidos
			if (($myrow ['idfactura']) > 0) {
 
                        // $statusPeriodo = TraestatusPeriod ( $myrow ['legalid'], $myrow ['prd'], $db );
                        // Verificar si se utiliza...
                        $sql = "SELECT date(fechacorte) AS fechacorte
                                FROM usrcortecaja
                                WHERE u_status=0 AND tag='".$myrow ['tagref']."'";
                        $Fechacorte = DB_query ( $sql, $db, '', '' );
                        while ( $myrowcorte = DB_fetch_array ( $Fechacorte ) ) {
                            $fechacorte = $myrowcorte ['fechacorte'];
                        }
                        
                        if ($myrow ['type'] == 10) {
                            $XSQL = "SELECT DISTINCT d2.id,custallocns.transid_allocfrom
                                    FROM debtortrans INNER JOIN custallocns ON debtortrans.id=custallocns.transid_allocto
                                    INNER JOIN debtortrans d2 ON d2.type=70 and d2.order_=debtortrans.transno
                                    LEFT JOIN custallocns C2 ON d2.id=C2.transid_allocto
                                    WHERE  debtortrans.id='" . $myrow ['idfactura'] . "'
                                            AND (C2.amt  IS NOT NULL or C2.amt<>0)
                                            Union
                                            SELECT debtortrans.id,custallocns.transid_allocfrom
                                            FROM debtortrans INNER JOIN custallocns ON debtortrans.id=custallocns.transid_allocto and abs(alloc)<>0
                                            INNER JOIN debtortrans d2 ON custallocns.transid_allocfrom=d2.id and d2.type<>70
                                            
                                            WHERE  debtortrans.id='" . $myrow ['idfactura'] . "'";
                        } else {
                            $XSQL = "SELECT debtortrans.id,custallocns.transid_allocfrom
                                    FROM debtortrans INNER JOIN custallocns ON debtortrans.id=custallocns.transid_allocto
                                    and abs(debtortrans.alloc)<>0
                                    WHERE  debtortrans.id='" . $myrow ['idfactura'] . "'";
                        }

                        if($_SESSION['UserID']=="desarrollo"){
                            //echo '<pre>sql2:'.$sql;
                        }

                        $tienerecibos = DB_query ( $XSQL, $db, '', '' );


                        $bancancelar = 1;
                        if (intval ( DB_num_rows ( $tienerecibos ) ) > intval ( 0 )) {

                            while ( $myrowtienerecibos = DB_fetch_row ( $tienerecibos )) {
                                if ($myrow ['type'] == 10 OR  $myrow ['type'] == 66) {
                                    $sql = "SELECT * FROM debtortrans WHERE type  NOT IN(65) AND id=" . $myrowtienerecibos [1];
                                }else{
                                    $sql = "SELECT * FROM debtortrans WHERE  id=" . $myrowtienerecibos [1];
                                }
                                 //echo '<pre>sql2:'.$sql;
                                $Getstatus = DB_query ( $sql, $db, $ErrMsg, "", true );
                                if(DB_num_rows($Getstatus)>0){
                                    $bancancelar = 0;
                                    break;
                                }                       
                            }

                            //$myrowtienerecibos = DB_fetch_row ( $tienerecibos );
                            // VALIDA SI NO ES FACTURA DE TICKET
                            // Se agregó tipo 65 si es  Nota Credito Remision Facturada, si se podrá cancelar
                             
                            
                            
                        }

                        //echo "<br> bancancelar=".$bancancelar;
						
                        if($bancancelar == 1) {
                            $banderaPermisoCancel = 1;
                            //echo "<br> PRUEBA2";
                            $myrowtienerecibos = DB_fetch_row ( $tienerecibos );
                            if (Havepermission ( $_SESSION ['UserID'], $myrow ['cancelfunctionid'], $db ) == 1) {
                                // $Cancelar = $rootpath . '/SelectSalesOrder.php?orderno='. $myrow['orderno'];
                                
                                $xflag = ($myrow ['fecha'] >= (date("Y") . "-" .  date("m") . "-" . date("d") . " 00:00:00.000"));
                                if ($xflag==""){
                                    $xflag = 0;
                                }
                                
                                if ($flagCancelConta == 0) {

                                    //echo "<br> entrar : ".Havepermission ( $_SESSION ['UserID'], 377, $db )." / ".$xflag;
                                    if ((Havepermission ( $_SESSION ['UserID'], 377, $db ) == 1) or ($xflag)) {
                                        //echo "<br> entrar 11: ";
                                        
                                        $Cancelar = $rootpath . '/ConfirmCancel_Invoice.php?TransNo=' . $myrow ['transno'] . '&OrderNumber=' . $myrow ['orderno'] . '&debtorno=' . trim ( $myrow ['debtorno'] ) . '&type=' . $myrow ['type'] . '&tagref=' . $myrow ['tagref'];
                                        $Cancelar = "<a href=" . $Cancelar . "><span class='glyphicon glyphicon-remove'></span> </a>";

                                    }else{
                                        //echo "<br> entrar ewe: ";
                                        $Cancelar = '<b>' . _ ( 'Fecha' ) . '<br>' . _ ( 'Anterior' ) . '</b>';
                                        $banderaPermisoCancel = 0;
                                    }
                                } else {
                                    $Cancelar = '<b>' . _ ( 'Contabilidad' ) . '<br>' . _ ( 'Cerrada' ) . '</b>';
                                    $banderaPermisoCancel = 0;
                                }

                                
                                    //VALIDACIONES PARA CANCELACION DIRECTA
                                if( ($myrow ['type'] == 10 OR $myrow ['type'] == 110 OR $myrow ['type'] == 66 ) AND  $banderaPermisoCancel == 1){

                                    if($myrow ['ordervalue'] <= 5000){
                                        $can = 0;
                                    }else{
                                        $can = 1;
                                    }
                                    if( ($year > 0 OR $month > 0 OR $day > 3 OR ($day == 3 AND $hour >0)) AND $can == 1){
                                        $can = 1;
                                    }else{
                                        $can = 0;
                                    }
                                    if(($myrow ['RIF'] == 0 OR !isset($myrow ['RIF']) ) AND $can == 1){
                                        $can = 1;
                                    }else{
                                        $can = 0;
                                    }
                                    if($myrow ['customerref'] != 'XAXX010101000' AND $can == 1){
                                        $can = 1;
                                    }else{
                                        $can = 0;
                                    }
                                    if($vrVitalizadoC==1){
										if ($flagCancelConta == 0) {
											if($can == 1){
												//echo "<br>fsfsdf";
												$Cancelar = $rootpath . '/ConfirmCancel_Invoice.php?TransNo=' . $myrow ['transno'] . '&OrderNumber=' . $myrow ['orderno'] . '&debtorno=' . trim ( $myrow ['debtorno'] ) . '&type=' . $myrow ['type'] . '&tagref=' . $myrow ['tagref'].'&buzonTrib=1';
												$Cancelar = "<a href=" . $Cancelar . "><span class='glyphicon glyphicon-remove'> Buz&oacute;n</a> ";
											}else{
												//echo "<br>fsfsdf/lewe";
												$Cancelar = $rootpath . '/ConfirmCancel_Invoice.php?TransNo=' . $myrow ['transno'] . '&OrderNumber=' . $myrow ['orderno'] . '&debtorno=' . trim ( $myrow ['debtorno'] ) . '&type=' . $myrow ['type'] . '&tagref=' . $myrow ['tagref'].'&buzonTrib=0';
												$Cancelar = "<a href=" . $Cancelar . "><span class='glyphicon glyphicon-remove'></span> ";
											}
										}
										else {
											$Cancelar = '<b>' . _ ( 'Contabilidad' ) . '<br>' . _ ( 'Cerrada' ) . '</b>';
											$banderaPermisoCancel = 0;
										}
                                    }elseif($vrVitalizadoC==0){
                                        $Cancelar = " ";   
                                    }else{
										if ($flagCancelConta == 0) {
                                        	if($can == 1){
												//echo "<br>fsfsdf";
												$Cancelar = $rootpath . '/ConfirmCancel_Invoice.php?TransNo=' . $myrow ['transno'] . '&OrderNumber=' . $myrow ['orderno'] . '&debtorno=' . trim ( $myrow ['debtorno'] ) . '&type=' . $myrow ['type'] . '&tagref=' . $myrow ['tagref'].'&buzonTrib=1';
												$Cancelar = "<a href=" . $Cancelar . "><span class='glyphicon glyphicon-remove'> Buz&oacute;n</a> ";
											}else{
												$Cancelar = $rootpath . '/ConfirmCancel_Invoice.php?TransNo=' . $myrow ['transno'] . '&OrderNumber=' . $myrow ['orderno'] . '&debtorno=' . trim ( $myrow ['debtorno'] ) . '&type=' . $myrow ['type'] . '&tagref=' . $myrow ['tagref'].'&buzonTrib=0';
												$Cancelar = "<a href=" . $Cancelar . "><span class='glyphicon glyphicon-remove'></span> ";
											}
										}
										else {
											$Cancelar = '<b>' . _ ( 'Contabilidad' ) . '<br>' . _ ( 'Cerrada' ) . '</b>';
											$banderaPermisoCancel = 0;
										}

                                    }
                                }//cancelacion directa

                                if($myrow ['flagcancel'] == 0){
                                    $Cancelar = "";
                                }
                                // No permitir al usario cancelar la factura hasta que cancele la nota de credito para que se cancele la nota de cargo del Anticipo
                                $vrFlagNC = false;
                                
                                $sql = "SELECT transidncredito AS NCAutomatica,ABS(ovamount+ovgst) as total, invtext
                                        FROM salesinvoiceadvance
                                        INNER JOIN debtortrans  ON transidncredito = debtortrans.id
                                        WHERE transidinvoice=".$myrow ['idfactura'];

                                if($_SESSION['UserID']=="desarrollo"){
                                    //echo '<pre>sql2:'.$sql;
                                }

                                $Getstatus = DB_query ( $sql, $db, $ErrMsg, "", true );
                                if(DB_num_rows($Getstatus)>0){
                                    while ( $myrowGs=DB_fetch_array($Getstatus)) {
                                        if($myrowGs['total']>0){
                                            $vrFlagNC = true;
                                            break;   
                                        }
                                    }
                                }
                                if($vrFlagNC==true){
                                    $Cancelar  = "TIene Recibos";
                                }
                            }else {
                                $Cancelar  = " ";
                            }

                            // Si no tiene recibos, agregarl url para generarlo
                            $Cancelar = '<a href="CustomerReceiptFacturaContado.php?transnofac='.$myrow ['transno'].'&typeinvoice='.$myrow ['type'].'&debtorno='.trim($myrow['debtorno']).'&branchcode='.trim($myrow['debtorno']).'&tag='.$myrow ['tagref'].'&id='.$myrow ['idfactura'].'&shippinglogid='.$myrow ['useridfactura'].'&shippingno=">Generar <br> Recibo</a>';
                           	if ($myrow ['statusid'] == 3) {
                           		$Cancelar = $myrow ['statusname'];
                           	}
                        } else if ($myrow ['statusid'] == 3) {
                        	$Cancelar = $myrow ['statusname'];
                        } else if ($myrow ['statusid'] == 5){
                            $Cancelar = "Tiene<br>Recibos";
						}
							
						
						
            } else {
				if ($flagCancelConta == 0) {
					if($vrVitalizadoC==1){
						if (Havepermission ( $_SESSION ['UserID'], $myrow ['cancelfunctionid'], $db ) == 1) {
							if ($myrow ['flagcancel'] == 0 && Havepermission ( $_SESSION ['UserID'], $myrow ['cancelextrafunctionid'], $db ) == 1) {
								$Cancelar = $rootpath . '/' . $paginabusquedapedidos . '?orderno=' . $myrow ['orderno'].$vrIDvitalizado;
								$Cancelar = "<a href=" . $Cancelar . "><span class='glyphicon glyphicon-remove'></span> </a>";
							} else {
								$Cancelar = $rootpath . '/' . $paginabusquedapedidos . '?orderno=' . $myrow ['orderno'].$vrIDvitalizado;
								$Cancelar = "<a href=" . $Cancelar . "><span class='glyphicon glyphicon-remove'></span> </a>";
							}
						}
					}elseif($vrVitalizadoC==0){
						$Cancelar="";
					}else{

						if (Havepermission ( $_SESSION ['UserID'], $myrow ['cancelfunctionid'], $db ) == 1) {
							if ($myrow ['flagcancel'] == 0 && Havepermission ( $_SESSION ['UserID'], $myrow ['cancelextrafunctionid'], $db ) == 1) {
								$Cancelar = $rootpath . '/' . $paginabusquedapedidos . '?orderno=' . $myrow ['orderno'].$vrIDvitalizado;
								$Cancelar = "<a href=" . $Cancelar . "><span class='glyphicon glyphicon-remove'></span> </a>";
							} else {
								$Cancelar = $rootpath . '/' . $paginabusquedapedidos . '?orderno=' . $myrow ['orderno'].$vrIDvitalizado;
								$Cancelar = "<a href=" . $Cancelar . "><span class='glyphicon glyphicon-remove'></span></a>";
							}
						}
					}
				}
				else {
					$Cancelar = '<b>' . _ ( 'Contabilidad' ) . '<br>' . _ ( 'Cerrada' ) . '</b>';
					$banderaPermisoCancel = 0;
				}
			}
			
			if (DB_num_rows ( $resultCancel ) > 0) {
				if($myRowCancel['cancelFlag']=='0' ){
					$Cancelar="";
				}else if( $myRowCancel['cancelFlag']=='2'){
					// verificr si el uuid fue sustituido
					$SQL = "SELECT * FROM log_cancelacion_sustitucion
					INNER JOIN debtortrans ON log_cancelacion_sustitucion.id = debtortrans.id AND log_cancelacion_sustitucion.UUID = debtortrans.uuid
					WHERE debtortrans.transno  = '".$myrow ['transno']."' AND debtortrans.type = '".$myrow ['type']."';";
					$ResultS = DB_query ( $SQL, $db );
					if(Db_num_rows($ResultS)>0){
						$Cancelar="";
					}else{
					}
				}
			}

			if (($myrow ['estatus'] == 1) || ($myrow ['estatus'] == 3)) {
				$Cancelar = $myrow ['statusname'];
			}
           
			$SQL = "SELECT
			DISTINCT
			CONCAT(SUBSTRING(tb_administracion_contratos.id_periodo, 1, 4), ' ', cat_Months.mes) as id_periodo
			FROM salesorders
			JOIN salesorderdetails on salesorderdetails.orderno = salesorders.orderno
			JOIN tb_administracion_contratos ON tb_administracion_contratos.id_administracion_contratos = salesorderdetails.id_administracion_contratos
			JOIN cat_Months ON cat_Months.u_mes = SUBSTRING(tb_administracion_contratos.id_periodo, 5, 2)
			WHERE
			salesorders.orderno = '".$myrow['orderno']."'
			AND salesorderdetails.id_administracion_contratos <> 0
			ORDER BY tb_administracion_contratos.id_periodo ASC";
			$resultPeriodos = DB_query ( $SQL, $db );
			$periodosContratos = "";
			while ( $myrowPeriodsContratos = DB_fetch_array ( $resultPeriodos ) ) {
				if (empty($periodosContratos)) {
					$periodosContratos = $myrowPeriodsContratos['id_periodo'];
				} else {
					$periodosContratos .= ", ".$myrowPeriodsContratos['id_periodo'];
				}
			}

			echo '<td style="text-align:center">'.$periodosContratos.'</td>';
			
			// if ($myrow ['statusid'] == 5){
			// 	$Cancelar = "Tiene<br>Recibos";
			// }
			echo '<td style="text-align:center">' . $Cancelar .'</td>';

			$flagAuxp=0;
			foreach ($vrArrayssts as $value) {
				if($myrow ['estatus']==$value['statusid']){
					if($value['flagventaperdida']==1){
						$flagAuxp=1;
						break;
					}
				}
			}
			
		$ovamount="0";
        $order_="";
        $sqlValidacion2 = "SELECT 
        debtortrans.ovamount,
        debtortrans.order_
        FROM debtortrans 
        Where debtortrans.type = 119 AND debtortrans.order_ = '".$myrow ['orderno']."'" ;
        $resultSelectVal2 = DB_query($sqlValidacion2, $db);

        while ($row = DB_fetch_array($resultSelectVal2)) {
            $ovamount = $row ['ovamount'];
			$order_ = $row ['order_'];
		}
		$permisoCancelacionPase = Havepermission ( $_SESSION ['UserID'], 196, $db );
	
		echo '<td style="text-align: center;">';
		if(($permisoCancelacionPase == 1) && ($myrow ['estatus'] == 1)) {
			if($ovamount == "0"){
				echo ' <a onclick="fnCancelar('.$myrow ['orderno'].')"><span class="glyphicon glyphicon-remove"></span></a>';
			}
		}
		echo ' </td>';
				

		echo '<td style="text-align: center;">
		';
		if ($permisoEdicion == 1){
		
			echo '<textarea style="display: none;" id="txtComents_'.$myrow ['orderno'].'">'.$myrow ['comments'].'</textarea>';
			echo ' <a onclick="fnModificar('.$myrow ['orderno'].')"><span class="glyphicon glyphicon-edit"></span></a>';

		}
		echo ' </td>';

			echo '</tr>';
		}

		$vrAuxCi ++; // la columa de la unidad ejecutora
		$vrAuxCi ++; // la columa periodos
		
		echo '<tr>';
		echo ' <th colspan="'.$vrAuxCi.'" class="pie_derecha" >' . _ ( 'Total MXN:' ) . '</th>';
		echo ' <th colspan="1" class="pie_derecha">$' . number_format ( $montoTotal, 2 ) . '</th>';
		echo ' <th colspan="'.$vrAuxCf.'" class="pie_derecha"></th>';
		echo '</tr>';
		echo '<tr>';
		echo ' <th colspan="'.$vrAuxCi.'" class="pie_derecha">' . _ ( 'Total USD:' ) . '</th>';
		echo ' <th colspan="1" class="pie_derecha">$' . number_format ( $montoTotalUSD, 2 ) . '</th>';
		echo ' <th colspan="'.$vrAuxCf.'" class="pie_derecha"></th>';
		echo '</tr>';
		echo '</table></div>';
		
		echo '<table class="table table-bordered" align=center>';
		
		$guardarBtn = "";
        if (Havepermission ( $_SESSION ['UserID'], 1612, $db ) == 1) {
			$guardarBtn = '<button  type="submit" name="btnGuardarTarjetaRedM" value="' . _ ( 'Guardar Tarjeta Red M' ) . '" style="cursor:pointer; border:0; background-color:transparent; margin-left: 15px;">
								<img src="images/b_procesar_25.png" title="' . _ ( 'Guardar Tarjeta Red M' ) . '">
	 						</button>';
		}
        
        $cancelarBtn = "";
		if ($flagCxC == 0) {
			if (Havepermission ( $_SESSION ['UserID'], 1140, $db ) == 1) {
				/*$cancelarBtn = '<button  type="submit" name="btnCancelarPedidoCerrado" value="' . _ ( 'Cancelar Pedidos Cerrados' ) . '" style="cursor:pointer; border:0; background-color:transparent; margin-left: 15px;">
        							<img src="images/b_cancelar_25.png" title="' . _ ( 'Cancelar pedidos cerrados' ) . '">
         						</button>';*/
			}
		}

?>
<?php
		
	}
}
?>
</tbody>
<?php
echo '</td>
	 <tr></table>';
?>

<script type="text/javascript">
	window.onload = function(e){
		$(document).ready(function(){
			var select_config = {
		            enableFiltering: true,
		            enableCaseInsensitiveFiltering: true,
		            selectAllJustVisible: false,
		            includeSelectAllOption: true,
		            maxHeight: 200,
		            buttonWidth: '259px',
		            nonSelectedText:"Seleccionar",
		            selectAllText: "Seleccionar todos",
		            allSelectedText: 'Todos Seleccionados',
		            filterPlaceholder: 'Buscar',
		            numberDisplayed: 1
		    };
		    $("#Quotations").multiselect(select_config);
		    $("button.multiselect").on("click", function (e) {
		    	var opened = $(this).parent().hasClass("open");
			    if (!opened) {
			        $(this).parent().addClass("open");
			        $("button.multiselect").attr("aria-expanded", "true");
			        $('.open .dropdown-menu').slideDown();
			        e.stopPropagation();
			    
			    } else {
			    	$(this).parent().removeClass("open");
			        $("button.multiselect").attr("aria-expanded", "false");
					$('.open .dropdown-menu').slideUp();
					e.stopPropagation();
			         
			    }
			});
		});
	}
</script>
<?php
// <input title="'._('Unifica las cotizaciones seleccionads en una nueva').'" type="submit" name="btnUnificar" value="'._('Unificar Seleccion').'">

echo '</form>';?>

<!-- <script src="css/jquery/jquery.min.js"></script> -->
<script>
 // Write on keyup event of keyword input element
 $(document).ready(function(){
 $("#txtFiltroCuentas").keyup(function(){
 _this = this;
 // Show only matching TR, hide rest of them
 $.each($("#mytable tr"), function() {
 if($(this).text().toLowerCase().indexOf($(_this).val().toLowerCase()) === -1)
 $(this).hide();
 else
 $(this).show();
 });
 });
});
</script>

<script src="css/vue/Vue.js"></script>

<script>
	function fnExcelReport() {
		var tab_text = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
		tab_text = tab_text + '<meta charset="UTF-8">';
		tab_text = tab_text + '<head><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
		tab_text = tab_text + '<x:Name>Test Sheet</x:Name>';
		tab_text = tab_text + '<x:WorksheetOptions><x:Panes></x:Panes></x:WorksheetOptions></x:ExcelWorksheet>';
		tab_text = tab_text + '</x:ExcelWorksheets></x:ExcelWorkbook></xml></head><body>';
		tab_text = tab_text + "<table border='1px'>";
		
		//get table HTML code
		tab_text = tab_text + $('#tablaRecibos').html();
		tab_text = tab_text + '</table></body></html>';

		var data_type = 'data:application/vnd.ms-excel';
		
		var ua = window.navigator.userAgent;
		var msie = ua.indexOf("MSIE ");
		//For IE
		if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
			if (window.navigator.msSaveBlob) {
			var blob = new Blob([tab_text], {type: "application/csv;charset=utf-8;"});
			navigator.msSaveBlob(blob, 'Pases de cobro.xls');
			}
		} 
    //for Chrome and Firefox 
		else {
			$('#test').attr('href', data_type + ', ' + encodeURIComponent(tab_text));
			$('#test').attr('download', 'Pases de cobro.xls');
		}
    }
</script>

<script type="text/javascript">
	var selectObjetoPrincipal = "<?php echo $selectObjetoPrincipal; ?>";
	$( document ).ready(function() {
		// alert("selectObjetoPrincipal:  "+selectObjetoPrincipal);
		if (selectObjetoPrincipal != "0") {
			fnSeleccionarDatosSelect("selectObjetoPrincipal", selectObjetoPrincipal);
		}
	});
</script>
<?php
include 'includes/footer_Index.inc';
?>
