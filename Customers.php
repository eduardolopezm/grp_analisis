<?php
/*
 * CGM 16/05/2013 SE AGREGA TABLA DE ADDENDAS DROP TABLE IF EXISTS `typeaddenda`; CREATE TABLE `typeaddenda` ( `id_addenda` int(11) NOT NULL auto_increment, `nameaddenda` varchar(50) default NULL, `archivoaddenda` varchar(100) default NULL, PRIMARY KEY (`id_addenda`) ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1; -- ---------------------------- -- Records of `typeaddenda` -- ---------------------------- BEGIN; INSERT INTO `typeaddenda` VALUES ('1', 'FEMSA', 'SendAddendaFemsa.inc'), ('2', 'AMECE', 'SendAddendaLala.inc'), ('3', 'PROLAMSA', 'SendAddendaProlamsa.inc'), ('4', 'AHMSA', 'SendAddendaAhmsa.inc'), ('5', 'TERNIUM', 'SendAddendaTernium.inc'); COMMIT; ALTER TABLE `custbranch` ADD COLUMN `typeaddenda` int DEFAULT '0' COMMENT 'Tipo addenda para FE'; SP 07/02/2013 Se crea consulta para incluir al cliente en la tabla prospect_movimientos si es tipo PROSPECTO SP 14/06/2012 Se agrego un campo hidden al formulario de edicion, para cuando entramos a esta pagina desde la pagina de ordenes de venta retornar a esta cuando se actualicen los datos del cliente SP 13/06/2011 marcar campos obligatorios y validar su captura JAHEPI 04/08/2012 - Agregu� campos para n�mero Movil y Nextel. SP 04/06/2012 - Se cambio la captura del RFC que estaba en la seccion de SUCURSAL a DATOS PERSONALES. GAZ - 03/MAYO/2012 - Corregi error que al deshabilitar lista de holdreasons, la forma no envia el valor, asi que agregue un campo hidden.. OJO !! JAHEPI -07/ENE/2013 - Agregu� variable de configuraci�n ValidarLimiteCredito, para validarlo en caso de que este en 1 o no hacerlo si est� en 0, si est� variable no est� definida en el cat�logo por defecto si valida el l�mite. ARCHIVO MODIFICADO POR: CARMEN GARCIA FECHA DE MODIFICACION: 30-NOV-2011 CAMBIOS: 1. se agrego campo de num ext y num interior ALTER TABLE `custbranch` ADD COLUMN `brnumint` VARCHAR(255) AFTER `ruta`, ADD COLUMN `brnumext` VARCHAR(255) AFTER `brnumint`; FIN DE CAMBIOS termina Elaboro Jesus Vargas Montes Fecha Modificacion 16 Abril 2013 Inicia Cambios 1. Se agrego al dar de alta o actualizar el campo de sector comercial para poderle asignarselo al cliente 2. Se agrego el campo de pais para agregarselo al clientex Termina Cambios Elaboro Jesus Vargas Montes Fecha Modificacion 30 Abril 2013 Inicia Cambios 1. Se agrego al dar de alta o actualizar el campo de NumeAsigCliente poder asignarselo al cliente en la tabla de custbranch
 */
// se agrega variable para poder meter caracteres especiales en el rfc $_SESSION ['PermiteCaracRFC']
// Se agrega validación de RFC y que no se borren datos al tener el error en captura de RFC (Si se borra RFC) V9CJ880326XXX

 /* ini_set('display_errors', 1); 
 ini_set('log_errors', 1); 
 error_reporting(E_ALL); 
 ini_set('error_log', dirname(__FILE__) . '/error_log.txt');  */

 // EPM 20/May/2019
 // Subir a produccion tarea : 78240, se sube a todo distribucion

$PageSecurity = 3;
$funcion = 26;
include ('includes/session.inc');
$title = _ ( 'Mantenimiento de Cliente' );
include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');
include ('includes/SecurityFunctions.inc');
require_once ('includes/mail.php');
include ('includes/Functions.inc');
// variables para link
// $SelectOrderItemsV5 = "SelectOrderItemsV5_0.php";
//MG, server 16
$SelectOrderItemsV5 = HavepermissionURL ( $_SESSION ['UserID'], 4, $db );
$permisotipocliente = Havepermission ( $_SESSION ['UserID'], 1615, $db );
$permisovendedor = Havepermission ( $_SESSION ['UserID'], 1644, $db );
$vrOpn=1;
if (isset ( $_GET ['from'] )) {
	$_SESSION ['frompage'] = $_GET ['from'];
}
// DATOS DE COTIZACION O DE ORDEN DE VENTA
if (isset ( $_GET ['identifier'] )) {
	$_SESSION ['identifier'] = $_GET ['identifier'];
}

$identifier="";
if (isset ( $_POST ['identifier'] )) {
	$identifier = $_POST ['identifier'];
} else {
	if (isset ( $_GET ['identifier'] )) {
		$identifier = $_GET ['identifier'];
	}
}

if (isset ( $_GET ['BranchCode'] )) {
	$_SESSION ['ExistingBranchCoder'] = $_GET ['BranchCode'];
}

if (isset ( $_POST ['FromYear'] )) {
	$FromYear = $_POST ['FromYear'];
} else if (isset ( $_GET ['FromYear'] )) {
	$FromYear = $_GET ['FromYear'];
} else {
	$FromYear = date ( 'Y' );
}

if (isset ( $_POST ['FromMes'] )) {
	$FromMes = $_POST ['FromMes'];
} else if (isset ( $_GET ['FromMes'] )) {
	$FromMes = $_GET ['FromMes'];
} else {
	$FromMes = date ( 'm' );
}

if (isset ( $_GET ['FromDia'] )) {
	$FromDia = $_GET ['FromDia'];
} else if (isset ( $_POST ['FromDia'] )) {
	$FromDia = $_POST ['FromDia'];
} else {
	$FromDia = date ( 'd' );
}

$SelectedVehicle="";
if (isset ( $_GET ['vehicleno'] )) {
	$SelectedVehicle = $_GET ['vehicleno'];
} else if (isset ( $_POST ['SelectedVehicle'] )) {
	$SelectedVehicle = $_POST ['SelectedVehicle'];
}

if(isset ( $_POST ['New'] ) ){
	prnMsg("Alta Nuevo cliente");
}
// die(var_dump($_POST ['rif']));
$fechaini = rtrim ( $FromYear ) . '-' . rtrim ( $FromMes ) . '-' . rtrim ( $FromDia );
$fechainic = mktime ( 0, 0, 0, rtrim ( $FromMes ), rtrim ( $FromDia ), rtrim ( $FromYear ) );

if(!isset($_POST ['CreditLimit_7'])){ 	$_POST ['CreditLimit_7'] = ""; }
if(!isset($_POST ['CreditLimit_8'])){ 	$_POST ['CreditLimit_8'] = ""; }
if(!isset($_POST ['CreditLimit_9'])){ 	$_POST ['CreditLimit_9'] = ""; }
if(!isset($_POST ['CreditLimit_10'])){ 	$_POST ['CreditLimit_10'] = ""; }
if(!isset($_POST ['CreditLimit_11'])){ 	$_POST ['CreditLimit_11'] = ""; }
if(!isset($_POST ['CreditLimit_12'])){ 	$_POST ['CreditLimit_12'] = ""; }
if(!isset($_POST ['CreditLimit_13'])){ 	$_POST ['CreditLimit_13'] = ""; }
if(!isset($_POST ['CreditLimit_14'])){ 	$_POST ['CreditLimit_14'] = ""; }
if(!isset($_POST ['CreditLimit_15'])){ 	$_POST ['CreditLimit_15'] = ""; }
if(!isset($_POST ['FaxNo'])){ 			$_POST ['FaxNo'] = ""; }
if(!isset($_POST ['prefer'])){ 			$_POST ['prefer'] = ""; }
if(!isset($_POST ['DescClienteComercial'])){ 	$_POST ['DescClienteComercial'] = ""; }
if(!isset($_POST ['DescClienteProPago'])){ 		$_POST ['DescClienteProPago'] = ""; }
if(!isset($_POST ['chkactivarecordatorio'])){ 	$_POST ['chkactivarecordatorio'] = ""; }
if(!isset($_POST ['urlfromorders'])){ 			$_POST ['urlfromorders'] = ""; }
if(!isset($_GET ['urlfromorders'])){ 			$_GET ['urlfromorders'] = ""; }
if(!isset($_POST ['DefaultLocation'])){ 		$_POST ['DefaultLocation'] = ""; }

if(isset($_POST ['CustName1'])){		
	
	$CustName1=$_POST ['CustName1'];		
}else{	
	$CustName1="";
}

if(isset($_POST ['CustName2'])){		$CustName2=$_POST ['CustName2'];			}else{	$CustName2="";}
if(isset($_POST ['CustName3'])){		$CustName3=$_POST ['CustName3'];			}else{	$CustName3="";}
if(isset($_POST ['CommercialName'])){	$CommercialName=$_POST ['CommercialName'];	}else{	$CommercialName="";}
if(isset($_POST ['taxid'])){			$txtid=$_POST ['taxid'];					}else{	$txtid="";}
if(isset($_POST ['Email'])){			$Email=$_POST ['Email'];					}else{	$Email="";}
if(isset($_POST ['PhoneNo'])){			$PhoneNo=$_POST ['PhoneNo'];				}else{	$PhoneNo="";}
if(isset($_POST ['MovilNo'])){			$MovilNo=$_POST ['MovilNo'];				}else{	$MovilNo="";}
if(isset($_POST ['NextelNo'])){			$NextelNo=$_POST ['NextelNo'];				}else{	$NextelNo="";}
if(isset($_POST ['Address1'])){			$Address1=$_POST ['Address1'];				}else{	$Address1="";}
if(isset($_POST ['brnumext'])){			$brnumext=$_POST ['brnumext'];				}else{	$brnumext="";}
if(isset($_POST ['brnumint'])){			$brnumint=$_POST ['brnumint'];				}else{	$brnumint="";}
if(isset($_POST ['Address2'])){			$Address2=$_POST ['Address2'];				}else{	$Address2="";}
if(isset($_POST ['Address3'])){			$Address3=$_POST ['Address3'];				}else{	$Address3="";}
if(isset($_POST ['custpais'])){			$custpais=$_POST ['custpais'];				}else{	$custpais="";}
if(isset($_POST ['Address5'])){			$Address5=$_POST ['Address5'];				}else{	$Address5="";}
if(isset($_POST ['Address6'])){			$Address6=$_POST ['Address6'];				}else{	$Address6="";}
if(isset($_POST ['BrPostAddr1'])){		$BrPostAddr1=$_POST ['BrPostAddr1'];		}else{	$BrPostAddr1="";}
if(isset($_POST ['BrPostAddr2'])){		$BrPostAddr2=$_POST ['BrPostAddr2'];		}else{	$BrPostAddr2="";}
if(isset($_POST ['discountcard'])){		$discountcard=$_POST ['discountcard'];		}else{	$discountcard="";}
if(isset($_POST ['Salesman'])){			$Salesman=$_POST ['Salesman'];				}else{	$Salesman="";}
if(isset($_POST ['custdata1'])){		$custdata1=$_POST ['custdata1'];			}else{	$custdata1="";}
if(isset($_POST ['custdata2'])){		$custdata2=$_POST ['custdata2'];			}else{	$custdata2="";}
if(isset($_POST ['custdata3'])){		$custdata3=$_POST ['custdata3'];			}else{	$custdata3="";}
if(isset($_POST ['custdata4'])){		$custdata4=$_POST ['custdata4'];			}else{	$custdata4="";}
if(isset($_POST ['custdata5'])){		$custdata5=$_POST ['custdata5'];			}else{	$custdata5="";}
if(isset($_POST ['custdata6'])){		$custdata6=$_POST ['custdata6'];			}else{	$custdata6="";}
if(isset($_POST ['CreditLimit'])){	$CreditLimit= $_POST ['CreditLimit'];		}else{	$CreditLimit=$_SESSION ['DefaultCreditLimit'];}
if(isset($_POST ['CreditLimit2'])){	$CreditLimit2=$_POST ['CreditLimit2'];		}else{	$CreditLimit2=$_SESSION ['DefaultCreditLimit'];}
// if (isset($_POST ['rif'])) { $RIF = $_POST ['rif']; }else{ $_POST ['rif'] == ""; }
if(isset($_POST ['Discount'])){ $Discount=$_POST ['Discount'];	}else{ $Discount=0;}

if (isset ( $_POST ['eliminaremails'] )) {
	$eemail = '';
	$sesql = "SELECT email FROM custmails WHERE idemail = '" . $_POST ['boxemails'] . "'";
	// echo "<br>" . $sesql;
	$resulte = DB_query ( $sesql, $db );
	if (DB_num_rows ( $resulte ) > 0) {
		$myrowe = DB_fetch_array ( $resulte );
		$eemail = $myrowe ['email'];
	}
	
	$desql = "DELETE FROM  custmails WHERE idemail = '" . $_POST ['boxemails'] . "'";
	$dresult = DB_query ( $desql, $db );
}

$codificacionJibe = 0;
if (preg_match("/erpjibe/", $_SESSION['DatabaseName']) and !preg_match("/erpjibe_DES/", $_SESSION['DatabaseName'])) {
	//Si es jibe codificar para letra ñ, solo en produccion y capa
	$codificacionJibe = 1;
}

echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/customer.png" title="' . _ ( 'Cliente' ) . '" alt="">' . ' ' . _ ( 'Mantenimiento de Clientes' ) . '';
if (isset ( $Errors )) {
	unset ( $Errors );
}
$Errors = array ();


if (isset ( $_POST ['submit'] ) || $_POST ['actualizar'] == 1) {

	
	$_POST ['CreditLimit'] = ( double ) $_POST ['CreditLimit'];
	$validarLimiteCredito = isset ( $_SESSION ['ValidarLimiteCredito'] ) ? $_SESSION ['ValidarLimiteCredito'] : 1;
	
	// initialise no input errors assumed initially before we test
	$InputError = 0;


	$i = 1;
	/*
	 * actions to take once the user has clicked the submit button ie the page has called itself with some user input
	 */
	
	// first off validate inputs sensible
	$_POST ['DebtorNo'] = strtoupper ( $_POST ['DebtorNo'] );

	echo 'NO: '.$_POST ['DebtorNo'] ;
	
	$sql = "SELECT * FROM custbranch WHERE debtorno = '" . $_POST ['DebtorNo'] . "' and branchcode = '" . $_POST ['DebtorNo'] . "'";;
	$rs_custbranch = DB_query ( $sql, $db );
	$sucursal_principal = DB_fetch_array ( $rs_custbranch );

	$sql = "SELECT COUNT(debtorno) FROM debtorsmaster WHERE debtorno='" . $_POST ['DebtorNo'] . "'";
	$result = DB_query ( $sql, $db );
	$myrow = DB_fetch_row ( $result );
	
	if ($myrow [0] > 0 and isset ( $_POST ['New'] ) and $_POST ['actualizar'] != 1) {
		$InputError = 1;
		prnMsg ( _ ( 'El numero de cliente ya existe en la base de datos' ), 'error' );
		$Errors [$i] = 'DebtorNo';
		$i ++;
	} else{		
		echo 'error'.$InputError;

		// if (strlen ( $_POST ['CustName1'] ) > 150 or strlen ( $_POST ['CustName1'] ) == 0) {
		// 	$InputError = 1;
		// 	prnMsg ( _ ( 'El nombre del cliente debe ser mayor a 0 y menor a 150 caracteres' ), 'error' );
		// 	$Errors [$i] = 'CustName1';
		// 	$i ++;
		// } 
		// if (strlen ( $_POST ['PhoneNo'] ) == 0) {
		// 	$InputError = 1;
		// 	prnMsg ( _ ( 'El tel�fono fijo es obligatorio' ), 'error' );
		// 	$Errors [$i] = 'PhoneNo';
		// 	$i ++;
		// } 
		// if (strlen ( trim ( $_POST ['taxid'] ) ) == 0 and $_SESSION ['ForzarCapturaRFC'] == 1) {
		// 	$InputError = 1;
		// 	prnMsg ( _ ( 'Recuerde que para facturar es necesario captutar RFC  de la sucursal' ), 'warn' );
		// 	$Errors [$i] = 'taxid';
		// 	$i ++;
		// } 
		// unset($_SESSION ['PermiteCaracRFC']);
		// if(!isset($_SESSION ['PermiteCaracRFC'])){
		// 	$_SESSION ['PermiteCaracRFC'] = 1;
		// }
		
		// if ( contains(trim ( $_POST ['taxid'] ), "&") and $_SESSION ['PermiteCaracRFC'] == 0) {
		// 	$InputError = 1;
		// 	prnMsg ( _ ( 'No se permiten caracteres &' ), 'error' );
		// 	$Errors [$i] = 'taxid';
		// 	$i ++;
		// } //*/
	
		// if (strlen ( trim ( $txtid ) ) <= 0){
		// 	prnMsg ( _ ( 'El RFC esta vacio, se ha colocado el RFC de publico en General' ), 'warning' );
		// 	$_POST ['taxid']="XAXX010101000";
		// }
		
		// if (strlen ( trim ( $_POST ['taxid'] ) ) > 0 and validaRFC ( $_POST ['taxid'] ) == 0) {
		// 	$InputError = 1;
		// 	prnMsg ( _ ( 'El RFC es INVALIDO "'.$_POST ['taxid'].'"' ), 'error' );
		// 	$Errors [$i] = 'taxid';
		// 	if(isset($_POST ['actualizar']) && $_POST ['actualizar']==1){
		// 		$_POST ['taxid']=$sucursal_principal['taxid'];
		// 	}
		// 	$i ++;
		// } 
		
		// if ($_SESSION ['AutoDebtorNo'] == 0 and strlen ( $_POST ['DebtorNo'] ) == 0) {
		// 	$InputError = 1;
		// 	prnMsg ( _ ( 'El codigo del cliente no debe estar vacio' ), 'error' );
		// 	$Errors [$i] = 'DebtorNo';
		// 	$i ++;
		// } 
		// if ($_SESSION ['AutoDebtorNo'] == 0 and ContainsIllegalCharacters ( $_POST ['DebtorNo'] )) {
		// 	$InputError = 1;
		// 	prnMsg ( _ ( 'El codigo del cliente no puede contener cualquiera de los siguientes caracteres' ) . " . - ' & + \" " . _ ( 'o un espacio' ), 'error' );
		// 	$Errors [$i] = 'DebtorNo';
		// 	$i ++;
		// 	// } elseif (ContainsIllegalCharacters($_POST['Address1']) OR ContainsIllegalCharacters($_POST['Address2'])) {
		// 	// $InputError = 1;
		// 	// prnMsg( _('Lines of the address must not contain illegal characters'),'error');//
		// } 
		// if (strlen ( $_POST ['Address1'] ) > 300) {
		// 	$InputError = 1;
		// 	prnMsg ( _ ( 'La direccion debe tener de 0 a 40 caracteres' ), 'error' );
		// 	$Errors [$i] = 'Address1';
		// 	$i ++;
		// } 
		// if (strlen ( $_POST ['Address2'] ) > 300) {
		// 	$InputError = 1;
		// 	prnMsg ( _ ( 'La colonia debe de tener de 0 a 40 caracteres' ), 'error' );
		// 	$Errors [$i] = 'Address2';
		// 	$i ++;
		// } 
		// if (strlen ( $_POST ['Address3'] ) > 300) {
		// 	$InputError = 1;
		// 	prnMsg ( _ ( 'La ciudad debe de tener de 0 a 40 caracteres' ), 'error' );
		// 	$Errors [$i] = 'Address3';
		// 	$i ++;
		// } 
		// if (strlen ( $_POST ['Address4'] ) > 300) {
		// 	$InputError = 1;
		// 	prnMsg ( _ ( 'El estado debe de tener mas de 50 caracteres' ), 'error' );
		// 	$Errors [$i] = 'Address4';
		// 	$i ++;
		// } 
		// if (strlen ( $_POST ['Address5'] ) > 20) {
		// 	$InputError = 1;
		// 	prnMsg ( _ ( 'El codigo postal debe de tener mas de 20 caracteres' ), 'error' );
		// 	$Errors [$i] = 'Address5';
		// 	$i ++;
		// } 
		// if (strlen ( $_POST ['Address6'] ) > 255) {
		// 	$InputError = 1;
		// 	prnMsg ( _ ( 'La direccion extra debe ser mayor a 255 caracteres' ), 'error' );
		// 	$Errors [$i] = 'Address6';
		// 	$i ++;
		// } 
		// if (! is_numeric ( $_POST ['CreditLimit'] ) and $validarLimiteCredito == 1) {
		// 	$InputError = 1;
		// 	prnMsg ( _ ( 'EL limite de credito debe ser numerico' ), 'error' );
		// 	$Errors [$i] = 'CreditLimit';
		// 	$i ++;
		// } 
		// if (! is_numeric ( $_POST ['PymtDiscount'] )) {
		// 	$InputError = 1;
		// 	prnMsg ( _ ( 'El descuento por pago debe ser numerico' ), 'error' );
		// 	$Errors [$i] = 'PymtDiscount';
		// 	$i ++;
		// 	// } elseif (!is_date($_POST['ClientSince'])) {
		// 	// $InputError = 1;
		// 	// prnMsg( _('La fecha de alta de cliente debe tener formato') . ' ' . $_SESSION['DefaultDateFormat'],'error');
		// 	// $Errors[$i] = 'ClientSince';
		// 	// $i++;
		// } 
		// if (! is_numeric ( $_POST ['Discount'] )) {
		// 	$InputError = 1;
		// 	prnMsg ( _ ( 'El porcentaje de descuento debe ser numerico' ), 'error' );
		// 	$Errors [$i] = 'Discount';
		// 	$i ++;
		// } 
		// if (( double ) $_POST ['CreditLimit'] < 0 and $validarLimiteCredito == 1) {
		// 	$InputError = 1;
		// 	prnMsg ( _ ( 'El limite de credito debe ser positivo' ), 'error' );
		// 	$Errors [$i] = 'CreditLimit';
		// 	$i ++;
		// } 
		// if ($_POST ['CreditLimit'] > $_SESSION ['creditlimit'] and intval ( $_POST ['CreditLimit'] ) != intval ( $_POST ['CreditLimit2'] ) and $validarLimiteCredito == 1) {
		// 	$InputError = 1;
		// 	prnMsg ( _ ( 'No puedes capturar un l�mite de cr�dito mayor al que tienes configurado (' . $_SESSION ['creditlimit'] . ')' ), 'error' );
		// 	$Errors [$i] = 'CreditLimit';
		// 	$i ++;
		// } 
		// if ((( double ) $_POST ['PymtDiscount'] > 10) or (( double ) $_POST ['PymtDiscount'] < 0)) {
		// 	$InputError = 1;
		// 	prnMsg ( _ ( 'El descuento por pago debe ser menor al 10% o menor igual a 0%' ), 'error' );
		// 	$Errors [$i] = 'PymtDiscount';
		// 	$i ++;
		// } 
		// if ((( double ) $_POST ['Discount'] > 100) or (( double ) $_POST ['Discount'] < 0)) {
		// 	$InputError = 1;
		// 	prnMsg ( _ ( 'El descuento debe ser menor al 100% o menor o igual a 0%' ), 'error' );
		// 	$Errors [$i] = 'Discount';
		// 	$i ++;
		// }
	}


	
	if (empty ( $_POST ["BrPostAddr1"] )) {
		$_POST ["BrPostAddr1"] = htmlspecialchars_decode ( $_POST ['Address1'], ENT_NOQUOTES );
	}
	
	if (empty ( $_POST ["BrPostAddr2"] )) {
		$_POST ["BrPostAddr2"] = htmlspecialchars_decode ( $_POST ['Address2'], ENT_NOQUOTES );
	}
	
	$BrPostAddr3 = htmlspecialchars_decode ( $_POST ['Address3'], ENT_NOQUOTES );
	$BrPostAddr4 = htmlspecialchars_decode ( $_POST ["Address4"], ENT_NOQUOTES );
	$BrPostAddr5 = htmlspecialchars_decode ( $_POST ['Address5'], ENT_NOQUOTES );
	$BrPostAddr6 = htmlspecialchars_decode ( $_POST ['Address6'], ENT_NOQUOTES );
	$specialinstructions = strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName1'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName2'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName3'], ENT_NOQUOTES ) ) ) );
	
	//Seccion para campos obligatorios
	//if ($_SESSION ['CamposObligatorio'] == 1 and isset ( $_POST ['submit'] )) {
	if ($_SESSION ['CamposObligatorio'] == 1 and isset ( $_POST ['submit'] )) {
		
		if (validaRFC ( $_POST ['taxid'] ) == 0 and $_SESSION ['ForzarCapturaRFC'] == 1) {
			prnMsg ( _ ( 'El rfc esta mal formado favor de verificarlo' ), 'error' );
			$InputError = 1;
			$Errors [$i] = 'taxid';
			$i ++;
		}
		
		if (trim($_POST ['taxid']) != 'XAXX010101000' and trim($_POST ['taxid']) != 'XEXX010101000') {
			if ($_POST ['actualizar'] != 1) {
				$SQLVal = "SELECT *
		                    FROM custbranch
		                    WHERE custbranch.taxid = '" . $_POST ['taxid'] . "'";
				$ResultVal = DB_query ( $SQLVal, $db );
				if (DB_num_rows ( $ResultVal ) > 0) {
					prnMsg ( _ ( 'El cliente ya ha sido de alta anteriormente favor de verificarlo' ), 'error' );
					$InputError = 1;
					$Errors [$i] = 'taxid';
					$i ++;
				}
			}
		}
	}

	if (isset($_POST['submit']) or $_POST ['actualizar'] == 1) {
		//validar RFC
		if (trim($_POST ['taxid']) != 'XAXX010101000' and trim($_POST ['taxid']) != 'XEXX010101000') {
			if ($_POST ['actualizar'] != 1) {
				$SQLVal = "SELECT *
			                FROM custbranch
			                WHERE custbranch.taxid = '" . trim($_POST ['taxid']) . "'";
				$ResultVal = DB_query ( $SQLVal, $db );
				if (DB_num_rows ( $ResultVal ) > 0) {
					prnMsg ( _ ( 'El cliente ya ha sido de alta anteriormente favor de verificarlo' ), 'error' );
					$InputError = 1;
					$Errors [$i] = 'taxid';
					$i ++;
				}	
			}
		}
	}
	
	// validar por variable de configuracion //
	if ($_SESSION ['CreditByDepto'] == 1) {
		
		$result = DB_query ( 'SELECT u_department,department FROM departments WHERE u_department >1 order by u_department', $db );
		while ( $myrow = DB_fetch_array ( $result ) ) {
			if(isset($_POST ['CreditLimit_' . $myrow ['u_department']])){
				$limite1 = $_POST ['CreditLimit_' . $myrow ['u_department']];
			}else{
				$limite1="";
			}
			// echo "limite".$limite1;
			if ($limite1 != 0 and strlen ( trim ( $limite1 ) ) > 0) {
				$Sql = "SELECT credit.u_department,credit.userid,credit.creditlimit,t.areacode,t.u_department,d.department
				FROM creditlimitxuserdpto as credit
				left join tags as t
				on credit.u_department=t.u_department
				left join departments as d
				on t.u_department=d.u_department
				WHERE credit.userid='" . $_SESSION ['UserID'] . "'
				and  t.areacode='" . $_SESSION ['DefaultArea'] . "'
				and t.u_department='" . $myrow ['u_department'] . "'
				and credit.creditlimit <= '" . $limite1 . "'";
				$result2 = DB_query ( $Sql, $db );
				// echo "<br><br>query".$Sql;
				// echo "<br>credit0".$limite1;
				// echo "<br>area".$_SESSION['DefaultArea'];
				if (DB_num_rows ( $result2 ) > 0) {
					// if($myrow = DB_fetch_array($result))
					// echo "aui entroooooooooooooooo";
					$myrow2 = DB_fetch_array ( $result2 );
					if ($limite1 > $myrow2 ['creditlimit']) {
						// echo "entro a mayor";
						
						$InputError = 1;
						prnMsg ( _ ( 'No puedes capturar un l�mite de cr�dito mayor al que tienes configurado en el Departamento(' . $myrow2 ['department'] . ') ' ), 'error' );
						$Errors [$i] = 'CreditLimit_';
						$i ++;
						break;
					}
				} else {
					$InputError = 1;
					prnMsg ( _ ( 'No existen permisos configurados para el departamento seleccionado(' . $myrow ['department'] . ') ' ), 'error' );
					$Errors [$i] = 'CreditLimit_';
					$i ++;
					break;
				}
			}
		}
	}

	echo 'InputErrpr:'.$InputError;
	
	if ($InputError != 1) {
		// Verificar la restriccion del usuario para la asignacion del vendedor
		/*
		 * if (Havepermission ( $_SESSION ["UserID"], 1311, $db )) {
		 * $sql = "SELECT sm.salesmancode, sm.salesmanname
		 * FROM salesman as sm
		 * LEFT JOIN areas as ar ON sm.area=ar.areacode
		 * LEFT JOIN tags as tg ON ar.areacode=tg.areacode
		 * LEFT JOIN sec_unegsxuser as u ON u.tagref = tg.tagref
		 * WHERE u.userid='" . $_SESSION ["UserID"] . "'
		 * and sm.type=1
		 * and sm.salesmanname like '%sin%'
		 * and area= '" . $_POST ["Area"] . "'
		 * ORDER BY tg.tagref";
		 *
		 * $resultado = DB_query ( $sql, $db );
		 * $renglon = DB_fetch_array ( $resultado );
		 * $_POST ['Salesman'] = $renglon ["salesmancode"];
		 * }
		 */
		
		$updCfd = "UPDATE debtorsmaster SET usoCFDI = '".$_POST['usoCFDI']."' WHERE debtorno = '" . $_POST ['DebtorNo'] . "' ";
		DB_query($updCfd,$db);

		$SQL_ClientSince = FormatDateForSQL ( $_POST ['ClientSince'] );
		echo  'New:'.$_POST ['New'];
		if (! isset ( $_POST ['New'] )) {
			
			if ($_POST ['rif'] == 'on') {
				$RIF = 1;
			}else{
				$RIF = 0;
			}
			
			// elimino registro de debtorsreminder
			// insertar registro en debtorsreminder si es viable
			if (!isset($_POST ['chkactivarecordatorio'])) {
				$sql = "DELETE from debtorsreminder
						WHERE debtorno = '" . $_POST ['DebtorNo'] . "'";
				$r = DB_query ( $sql, $db );
			}
			
			$sql = "SELECT count(id)
					  FROM debtortrans
					where debtorno = '" . $_POST ['DebtorNo'] . "'";
			//echo "<br>". $sql;
			$result = DB_query ( $sql, $db );
			$myrow = DB_fetch_array ( $result );
			
			(! isset ( $_POST ['AddrInvBranch'] )) ? $AddrInvBranch = 0 : $AddrInvBranch = $_POST ['AddrInvBranch'];
			(! isset ( $_POST ['CustomerPOLine'] )) ? $CustomerPOLine = 0 : $CustomerPOLine = $_POST ['CustomerPOLine'];
			
			//echo "<br>sas: ".$myrow [0];
			if ($myrow [0] == 0) {
				
				$sql = "UPDATE debtorsmaster SET
					name='" .  ($codificacionJibe == 0 ? strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName1'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName2'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName3'], ENT_NOQUOTES ) ) ) ) :  utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName1'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName2'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName3'], ENT_NOQUOTES ) ) ) ))) . "',
					name1='" . ($codificacionJibe == 0 ? strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName1'], ENT_NOQUOTES ) ) ) : utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName1'], ENT_NOQUOTES ) ) ))) . "',
					name2='" . ($codificacionJibe == 0 ? strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName2'], ENT_NOQUOTES ) ) ) : utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName2'], ENT_NOQUOTES ) ) ))) . "',
					name3='" . ($codificacionJibe == 0 ? strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName3'], ENT_NOQUOTES ) ) ) : utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName3'], ENT_NOQUOTES ) ) ))) . "',
					nameextra='" .($codificacionJibe == 0 ? strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CommercialName'], ENT_NOQUOTES ) ) ) : utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CommercialName'], ENT_NOQUOTES ) ) ))) . "',
					address1='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address1'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address1'] )) . "',
					address2='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address2'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address2'] )) . "',
					address3='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address3'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address3'] )) . "',
					address4='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address4'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address4'] )) . "',
					address5='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address5'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address5'] )) . "',
					address6='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address6'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address6'] )) . "',
					currcode='" . $_POST ['CurrCode'] . "',
					clientsince='" . $SQL_ClientSince . "',
					holdreason='" . $_POST ['HoldReason'] . "',
					paymentterms='" . $_POST ['PaymentTerms'] . "',
					discount='" . ($_POST ['Discount']) / 100 . "',
					discountcode='" . $_POST ['DiscountCode'] . "',
					pymtdiscount='" . ($_POST ['PymtDiscount']) / 100 . "',
					creditlimit='" . $_POST ['CreditLimit'] . "',
					salestype = '" . $_POST ['SalesType'] . "',
					invaddrbranch='" . $AddrInvBranch . "',
					taxref='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['TaxRef'], ENT_NOQUOTES ) : utf8_decode(htmlspecialchars_decode ( $_POST ['TaxRef'], ENT_NOQUOTES ))) . "',
					customerpoline='" . $CustomerPOLine . "',
					typeid='" . $_POST ['typeid'] . "',
					coments='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['coments'], ENT_NOQUOTES ) : utf8_decode(htmlspecialchars_decode ( $_POST ['coments'], ENT_NOQUOTES ))) . "',
					blacklist='" . $_POST ['lista'] . "',
					NumRegIdTrib='".(isset($_POST['NumRegIdTrib']) ? $_POST['NumRegIdTrib'] : '')."',
					RIF = '".$RIF."'

				  WHERE debtorno = '" . $_POST ['DebtorNo'] . "'";
				 //echo "<br>update".$sql;
			} else {
				$currsql = "SELECT currcode
					  		FROM debtorsmaster
							where debtorno = '" . $_POST ['DebtorNo'] . "'";
				$currresult = DB_query ( $currsql, $db );
				$currrow = DB_fetch_array ( $currresult );
				$OldCurrency = $currrow [0];
				$sql = "UPDATE debtorsmaster SET
					name='" .  ($codificacionJibe == 0 ? strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName1'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName2'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName3'], ENT_NOQUOTES ) ) ) ) :  utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName1'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName2'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName3'], ENT_NOQUOTES ) ) ) ))) . "',
					name1='" . ($codificacionJibe == 0 ? strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName1'], ENT_NOQUOTES ) ) ) : utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName1'], ENT_NOQUOTES ) ) ))) . "',
					name2='" . ($codificacionJibe == 0 ? strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName2'], ENT_NOQUOTES ) ) ) : utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName2'], ENT_NOQUOTES ) ) ))) . "',
					name3='" . ($codificacionJibe == 0 ? strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName3'], ENT_NOQUOTES ) ) ) : utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName3'], ENT_NOQUOTES ) ) ))) . "',
					nameextra='" .($codificacionJibe == 0 ? strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CommercialName'], ENT_NOQUOTES ) ) ) : utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CommercialName'], ENT_NOQUOTES ) ) ))) . "',
					address1='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address1'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address1'] )) . "',
					address2='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address2'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address2'] )) . "',
					address3='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address3'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address3'] )) . "',
					address4='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address4'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address4'] )) . "',
					address5='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address5'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address5'] )) . "',
					address6='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address6'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address6'] )) . "',
					clientsince='$SQL_ClientSince',
					holdreason='" . $_POST ['HoldReason'] . "',
					paymentterms='" . $_POST ['PaymentTerms'] . "',
					discount=" . ($_POST ['Discount']) / 100 . ",
					discountcode='" . $_POST ['DiscountCode'] . "',
					pymtdiscount=" . ($_POST ['PymtDiscount']) / 100 . ",
					creditlimit=" . $_POST ['CreditLimit'] . ",
					salestype = '" . $_POST ['SalesType'] . "',
					invaddrbranch='" . $AddrInvBranch . "',
					currcode='" . $_POST ['CurrCode'] . "',
					taxref='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['TaxRef'], ENT_NOQUOTES ) : utf8_decode(htmlspecialchars_decode ( $_POST ['TaxRef'], ENT_NOQUOTES ))) . "',
					customerpoline='" . $CustomerPOLine . "',
					typeid='" . $_POST ['typeid'] . "',
					coments='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['coments'], ENT_NOQUOTES ) : utf8_decode(htmlspecialchars_decode ( $_POST ['coments'], ENT_NOQUOTES ))) . "',
					blacklist='" . $_POST ['lista'] . "',
					NumRegIdTrib='".$_POST['NumRegIdTrib']."',
					RIF = '".$RIF."'
				  WHERE debtorno = '" . $_POST ['DebtorNo'] . "'";
				  //echo "<br>".$sql;
				if ($OldCurrency != $_POST ['CurrCode']) {
					prnMsg ( _ ( 'La moneda del cliente no se puede actualizar' ), 'info' );
				}
			}
			// echo 'SQL:<br>'.$sql.'<br>';
			$ErrMsg = _ ( 'La actualizacion de los datos del cliente no fue posible' );
			$result = DB_query ( $sql, $db, $ErrMsg );
			
			// si cliente es seteado como prospecto insertarlo si no existe en prospect_movimientos
			if ($_POST ['typeid'] == 7) {
				$qry = "Select distinct debtorno FROM prospect_movimientos
						WHERE debtorno = '" . $_POST ['DebtorNo'] . "'";
				$r = DB_query ( $qry, $db );
				if (DB_num_rows ( $r ) == 0) {
					$qry = "insert into prospect_movimientos (u_proyecto,dia,mes,anio,concepto,descripcion,prioridad,u_user,UserId,fecha,activo,catcode,idstatus,fecha_compromiso,fecha_alta,debtorno,confirmado)
									VALUES ('" . $_POST ['Area'] . "',day(current_date),month(current_date),year(current_date),'" . strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName1'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName2'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName3'], ENT_NOQUOTES ) ) ) ) . "','" . strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName1'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName2'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName3'], ENT_NOQUOTES ) ) ) ) . "',3,'" . $_SESSION ['UserID'] . "','" . $_SESSION ['UserID'] . "',current_date,1,1,1,current_date,current_date,'" . $_POST ['DebtorNo'] . "',0)";
					
					$r = DB_query ( $qry, $db );
				}
			}
			
			$sql = "UPDATE custbranch SET
				      brname='" .($codificacionJibe == 0 ? strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName1'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName2'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName3'], ENT_NOQUOTES ) ) ) ) : utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName1'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName2'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName3'], ENT_NOQUOTES ) ) ) ))) . "',
				      phoneno='" . $_POST ['PhoneNo'] . "',
				      movilno='" . $_POST ['MovilNo'] . "',
				      nextelno='" . $_POST ['NextelNo'] . "',
				      faxno='" . $_POST ['FaxNo'] . "',
				      email='" . $_POST ['Email'] . "',
				      braddress1='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address1'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address1'] )) . "',
				      braddress2='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address3'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address3'] )) . "',
				      brpostaddr1='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['BrPostAddr1'], ENT_NOQUOTES ) : utf8_decode( $_POST ['BrPostAddr1'] )) . "',
				      brpostaddr2='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['BrPostAddr2'], ENT_NOQUOTES ) : utf8_decode( $_POST ['BrPostAddr2'] )) . "',
				      brpostaddr3='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $BrPostAddr3, ENT_NOQUOTES ) : utf8_decode( $BrPostAddr3 )) . "',
				      brpostaddr4='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $BrPostAddr4, ENT_NOQUOTES ) : utf8_decode( $BrPostAddr4 )) . "',
				      brpostaddr5='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $BrPostAddr5, ENT_NOQUOTES ) : utf8_decode( $BrPostAddr5 )) . "',
				      brpostaddr6='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $BrPostAddr6, ENT_NOQUOTES ) : utf8_decode( $BrPostAddr6 )) . "',
				      specialinstructions= '" .($codificacionJibe == 0 ? htmlspecialchars_decode ( $specialinstructions, ENT_NOQUOTES ) : utf8_decode(htmlspecialchars_decode ( $specialinstructions, ENT_NOQUOTES ))) . "',
				      
				      braddress3='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address4'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address4'] )) . "',
				      braddress4='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address5'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address5'] )) . "',
				      braddress5='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address6'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address6'] )) . "',
				      braddress6='" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address2'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address2'] )) . "',
				      brnumext='" . htmlspecialchars_decode ( $_POST ['brnumext'], ENT_NOQUOTES ) . "',
				      brnumint='" . htmlspecialchars_decode ( $_POST ['brnumint'], ENT_NOQUOTES ) . "',
				      custdata1='" . htmlspecialchars_decode ( $_POST ['custdata1'], ENT_NOQUOTES ) . "',
				      custdata2='" . htmlspecialchars_decode ( $_POST ['custdata2'], ENT_NOQUOTES ) . "',
				      custdata3='" . htmlspecialchars_decode ( $_POST ['custdata3'], ENT_NOQUOTES ) . "',
				      custdata4='" . htmlspecialchars_decode ( $_POST ['custdata4'], ENT_NOQUOTES ) . "',
				      custdata5='" . htmlspecialchars_decode ( $_POST ['custdata5'], ENT_NOQUOTES ) . "',
				      custdata6='" . htmlspecialchars_decode ( $_POST ['custdata6'], ENT_NOQUOTES ) . "',
				      taxid='" . htmlspecialchars_decode ( $_POST ['taxid'], ENT_NOQUOTES ) . "',
				      salesman='" . htmlspecialchars_decode ( $_POST ['Salesman'], ENT_NOQUOTES ) . "',
				      area='" . $_POST ['Area'] . "',
					  welcomemail = '" . $_POST ['welcomeemail'] . "',
				      defaultlocation='" . $_POST ['DefaultLocation'] . "',
				      taxgroupid='" . $_POST ['TaxGroup'] . "',
                                      prefer='" . $_POST ['prefer'] . "',
  				      lineofbusiness='" . htmlspecialchars_decode ( $_POST ['giro'], ENT_NOQUOTES ) . "',
				      paymentname='" . $_POST ['paymentname'] . "',
					  nocuenta='" . $_POST ['nocuenta'] . "',
					  custpais='" . $_POST ['custpais'] . "',
					  NumeAsigCliente = '" . $_POST ["NumeAsigCliente"] . "',
					  descclientecomercial='" . $_POST ['DescClienteComercial'] . "',
					  descclientepropago = '" . $_POST ["DescClienteProPago"] . "',
					  descclienteop = '" . $_POST ['descclienteop'] . "',
					  typeaddenda = '" . $_POST ["typeaddenda"] . "',
					  DiasRevicion = '" . $_POST ['DiasRevicion'] . "',
					  DiasPago = '" . $_POST ['DiasPago'] . "',
					  discountcard = '" . htmlspecialchars_decode ( $_POST ['discountcard'], ENT_NOQUOTES ) . "',
                                          typecomplement = '".$_POST['typecomplement']."'    
				WHERE debtorno = '" . $_POST ['DebtorNo'] . "' and branchcode = '" . $_POST ['DebtorNo'] . "'";
			//echo 'SQL:<br>'.$sql.'<br>';
			$result = DB_query ( $sql, $db, $ErrMsg );

			//si hay datos
			$sql = "SELECT * FROM custbranchnumprovider WHERE idcustbranch = '".$_POST ['DebtorNo']."'";
			$result = DB_query ($sql , $db );
			$dato = false;
			while ( $myrow = DB_fetch_array ( $result ) ) { $dato = true; }

			if ($dato == true) {
				$sql="UPDATE custbranchnumprovider set idtypeaddenda = '" . $_POST ["typeaddenda"] . "', numprovider='".$_POST['noproveedor']."' where idcustbranch = '" . $_POST ['DebtorNo'] . "'";
				$result = DB_query ( $sql, $db, $ErrMsg );
			}else{
				$sql="INSERT INTO `custbranchnumprovider`(`idcustbranch`,`idtypeaddenda`,`numprovider`) values('".$_POST ['DebtorNo']."','".$_POST ["typeaddenda"]."','".$_POST ["noproveedor"]."')";
				$result = DB_query ( $sql, $db, $ErrMsg );
			}

			$sqlc = "UPDATE custbranch 
					SET discountcard = '" . $_POST ['discountcard'] . "' 
					WHERE debtorno = '" . $_POST ['DebtorNo'] . "'";
			$resultc = DB_query ( $sqlc, $db, $ErrMsg );
			
			// actualizar tipos de impuesto
			$qry = "DELETE FROM debtortaxes
						WHERE debtorno = '" . $_POST ['DebtorNo'] . "'";
			$r = DB_query ( $qry, $db, $ErrMsg );
			
			if ($_POST ['taxclient']) {
				$lista = trim ( $_POST ['taxclient'], "|" );
				$arr = explode ( "|", $lista );
				
				$qry = "INSERT INTO debtortaxes VALUES ";
				for($i = 0; $i < count ( $arr ); $i ++) {
					$qry .= "('" . $_POST ['DebtorNo'] . "','" . $arr [$i] . "'),";
				}
				
				$qry = substr ( $qry, 0, strlen ( $qry ) - 1 );
				$r = DB_query ( $qry, $db, $ErrMsg );
			}

			$qry = "DELETE FROM valuecomplement
					WHERE debtorno = '" . $_POST ['DebtorNo'] . "'";
			$r = DB_query ( $qry, $db, $ErrMsg );

			if ($_POST ['complementclient']) {
				$lista = trim ( $_POST ['complementclient'], "|" );
				$arr = explode ( "|", $lista );
				
				$qry = "INSERT INTO valuecomplement VALUES ";
				for($i = 0; $i < count ( $arr ); $i ++) {
					$qry .= "('','" . $arr [$i] . "', '', '" . $_POST ['DebtorNo'] . "'),";
				}
				
				$qry = substr ( $qry, 0, strlen ( $qry ) - 1 );
				$r = DB_query ( $qry, $db, $ErrMsg );
			}
			
			// inserta email
			$emails = $_POST ['Email'];
			if (! IsEmailAddress ( $emails )) {
				$emails = "";
			} else {
				// consulto si el email existe en la base de datos
				$SQL = "SELECT * FROM custmails
					WHERE debtorno='" . $_POST ['DebtorNo'] . "'
					AND branchcode='" . $_POST ['DebtorNo'] . "'
					AND email='" . $emails . "'";
				$ErrMsg = _ ( 'ERROR CRITICO' ) . ' ' . _ ( 'ANOTE EL ERROR' ) . ': ' . _ ( 'No se obtuvieron los datos del email' );
				$DbgMsg = _ ( 'El siguiente SQL se utilizo' );
				$Result = DB_query ( $SQL, $db, $ErrMsg, $DbgMsg, true );
				// en caso de que no exista se inserta
				if (DB_num_rows ( $Result ) == 0) {
					$SQL = "INSERT INTO custmails(debtorno,branchcode,email,trandate,active)
					  VALUES('" . $_POST ['DebtorNo'] . "','" . $_POST ['DebtorNo'] . "','" . $emails . "',now(),1)";
					$ErrMsg = _ ( 'ERROR CRITICO' ) . ' ' . _ ( 'ANOTE EL ERROR' ) . ': ' . _ ( 'No se realizo la insercion del email' );
					$DbgMsg = _ ( 'El siguiente SQL se utilizo' );
					$Result = DB_query ( $SQL, $db, $ErrMsg, $DbgMsg, true );
				}
			}
			
			// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			$result = DB_query ( 'SELECT u_department,department FROM departments where u_department >1 ', $db );
			
			$sqlDEL = "DELETE FROM limitesxdepartamento
					WHERE idcliente = '" . $_POST ['DebtorNo'] . "'";
			$resultDEL = DB_query ( $sqlDEL, $db, $ErrMsg );
			// echo '<br>ELIMINA: '.$sqlDEL.'<br>';
			echo '<br>';
			while ( $myrow = DB_fetch_array ( $result ) ) {
				$limite1 = $_POST ['CreditLimit_' . $myrow ['u_department']];
				// echo 'limite: '.$limite1.' - ';
				$sqldep = "INSERT INTO limitesxdepartamento (
									idcliente,
									iddepartment,
									limitecredito
									)
							VALUES ('" . $_POST ['DebtorNo'] . "',
								'" . $myrow ['u_department'] . "',
								'" . $limite1 . "')";
				// echo "<br>INSERTA: " . $sqldep;
				$resultdep = DB_query ( $sqldep, $db, $ErrMsg );
			}

			/*Guarda los datos de la razon social*/
			/*eliminamos   razon social para este cliente*/
			if($_SESSION['DatabaseName']=="erppisumma_DES" OR $_SESSION['DatabaseName']=="erppisumma_CAPA" OR $_SESSION['DatabaseName']=="erppisumma"){
				$sql="DELETE from debtorsmaster_legalid WHERE debtorno = '".$_POST ['DebtorNo']."'";
				$ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
				$DbgMsg = _('El SQL utilizado es:');
				$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				/* Agregar  razon social por cliente*/
				if (isset($_POST['TotalLegal'])){
					$totalsucursales=$_POST['TotalLegal'];
					for ( $suc = 0 ; $suc <= $totalsucursales ; $suc++) {
						//echo "<br>suc: " . $_POST['Namesucursal'.$suc] . " ==> " . $_POST['SucursalSel'.$suc];
						if ($_POST['LegalSel'.$suc]==TRUE){
							$namesucursal=$_POST['NameLegal'.$suc];
							$sql="INSERT INTO debtorsmaster_legalid (debtorno,legalid)";
							$sql=$sql." values('".$_POST ['DebtorNo']."','".$namesucursal."')";
							$ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
							$DbgMsg = _('El SQL utilizado es:');
							//echo "<br> ".$sql;
							$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
						}
					}
						
				}

			}
			

			// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			/*
			 * ibeth$resultterm=DB_query('SELECT u_department,department FROM departments WHERE u_department >1 ',$db); $sqlDEL2 = "DELETE FROM terminospagoxdepartamento WHERE idcliente = '" . $_POST['DebtorNo'] . "'"; $resultDEL2 = DB_query($sqlDEL2,$db,$ErrMsg); //echo '<br>ELIMINA: '.$sqlDEL2.'<br>'; while ($myrowT = DB_fetch_array($resultterm)) { $termino = $_POST['PaymentTerms_'. $myrowT['u_department']] ; $sql = "INSERT INTO terminospagoxdepartamento ( idcliente, iddepartment, terminopago ) VALUES ('" . $_POST['DebtorNo'] ."', '" .$myrowT['u_department'] . "', '" . $termino . "')"; //echo "<br>INSERTA: " . $sql; $result = DB_query($sql,$db,$ErrMsg); } prnMsg( _('Cliente actualizado'),'success'); echo "<br>";
			 */
			if ($_SESSION ['frompage'] == 'selectorderitems') {
				$BranchCode = $_SESSION ['ExistingBranchCoder'];
				$_SESSION ['frompage'] = '';
				unset ( $_SESSION ['ExistingBranchCoder'] );
				if ($_SESSION ['Items' . $identifier]->OrderNo != 0) {
					
					echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/" . $SelectOrderItemsV5 . "?Select=" . $_POST ['DebtorNo'] . ' - ' . $BranchCode . '|Auto:' . $_SESSION ['Items' . $identifier]->SelectedVehicle . "&identifier=" . $identifier . "'>";
					echo '<div class="centre">' . _ ( 'Tu deberas automaticamente  ser redireccionado a la pagina para dar de alta una Sucursal del Cliente' ) . '. ' . _ ( 'Si esto no sucede' ) . ' (' . _ ( 'Si tu explorador no soporta META Refresh' ) . ') ' . "<a href='" . $rootpath . "/SelectOrderItemsV4_0.php?" . SID . "&Select=" . $_POST ['DebtorNo'] . ' - ' . $BranchCode . 'Auto:' . $_SESSION ['Items' . $identifier]->SelectedVehicle . '</div>';
					exit ();
				} else {
					echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/" . $SelectOrderItemsV5 . "?Select=" . $_POST ['DebtorNo'] . ' - ' . $BranchCode . '|Auto:' . $_SESSION ['Items' . $identifier]->SelectedVehicle . "&identifier=" . $identifier . "'>";
					echo '<div class="centre">' . _ ( 'Tu deberas automaticamente  ser redireccionado a la pagina para dar de alta una Sucursal del Cliente' ) . '. ' . _ ( 'Si esto no sucede' ) . ' (' . _ ( 'Si tu explorador no soporta META Refresh' ) . ') ' . "<a href='" . $rootpath . "/" . $SelectOrderItemsV5 . "?" . SID . "NewOrder=Yes&Select=" . $_POST ['DebtorNo'] . ' - ' . $BranchCode . '|Auto:' . $_SESSION ['Items' . $identifier]->SelectedVehicle . '</div>';
					exit ();
				}
			}

			prnMsg ( _ ( 'Los datos se actualizaron correctamente' ), 'success' );
			
			// si venismo de pedidos de venta, regresamos a esa pagina
			if ($_POST ['urlfromorders'])
				header ( "Location:" . $_POST ['urlfromorders'] );
		} else { // it is a new customer
			/*
			 * set the DebtorNo if $AutoDebtorNo in config.php has been set to something greater 0
			 */
			
			// validar que el nuevo cliente no exista
			if ($_SESSION ['ValidarSoloPorRFCRegistrarCliente'] != 1) {
				$sql = "SELECT m.debtorno
						FROM debtorsmaster m,
						     custbranch c
						WHERE m.debtorno = c.debtorno";
						if (trim($_POST ['taxid']) != 'XAXX010101000' and trim($_POST ['taxid']) != 'XEXX010101000') {
							$sql .= " AND c.taxid = '" . htmlspecialchars_decode ( $_POST ['taxid'], ENT_NOQUOTES ) . "'";
						}
						$sql .= " AND name = '" . strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName1'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName2'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName3'], ENT_NOQUOTES ) ) ) ) . "'";
			} else {
				$sql = "SELECT m.debtorno
						FROM debtorsmaster m,
						     custbranch c
						WHERE m.debtorno = c.debtorno
						  AND c.taxid = '" . htmlspecialchars_decode ( $_POST ['taxid'], ENT_NOQUOTES ) . "'";
			}
			
			if ($_SESSION ['UserID'] == "desarrollo") {
				// echo '<pre>' . $sql;
				// die ( "END" );
			}
			echo $continuar;
			$res = DB_query ( $sql, $db );
			$continuar = true;
			
			if ($_SESSION ['UserID'] == "desarrollo") {
				// echo '<pre>Cant: ' . DB_num_rows ( $res );
				// echo '<pre>ForzarCapturaRFC: ' . $_SESSION ['ForzarCapturaRFC'];
				// die ( "END" );
			}
			
			if (DB_num_rows ( $res ) > 0 and $_SESSION ['ForzarCapturaRFC'] == 1) {
				if (trim($_POST ['taxid']) != 'XAXX010101000' and trim($_POST ['taxid']) != 'XEXX010101000') {
					if ($_SESSION ['ValidarSoloPorRFCRegistrarCliente'] != 1) {
						prnMsg ( _ ( 'Ya existe un cliente con el RFC y razon social que intenta crear' ), 'error' );
						$continuar = false;
					} else {
						if (trim($_POST ['taxid']) != 'XAXX010101000' and trim($_POST ['taxid']) != 'XEXX010101000') {
							$continuar = false;
							prnMsg ( _ ( 'Ya existe un cliente con el RFC especificado...' ), 'error' );
						}
					}
				}
			}
			
			if ($continuar) {
				
				if ($_SESSION ['AutoDebtorNo'] > 0) {
					/* system assigned, sequential, numeric */
					if ($_SESSION ['AutoDebtorNo'] == 1) {
						$_POST ['DebtorNo'] = GetNextTransNo ( 500, $db );
					}
				}
				if (! isset ( $_POST ['AddrInvBranch'] )) {
					$AddrInvBranch = 0;
				} else {
					$AddrInvBranch = $_POST ['AddrInvBranch'];
				}
				
				if (! isset ( $_POST ['CustomerPOLine'] )) {
					$CustomerPOLine = 0;
				}  // //
				else {
					$CustomerPOLine = $_POST ['CustomerPOLine'];
				}
				
				if(strlen($_POST ['Address4a'])>0){
					$vrEstado=$_POST ['Address4a'];
				}else if(strlen($_POST ['Address4'])>0){
					$vrEstado=$_POST ['Address4'];
				}

				$sql = "INSERT INTO debtorsmaster (
							  debtorno,
							  name,
							  name1,
							  name2,
							  name3,
			  				  nameextra,
							  address1,
							  address2,
							  address3,
							  address4,
							  address5,
							  address6,
							  currcode,
							  clientsince,
							  holdreason,
							  paymentterms,
							  discount,
							  discountcode,
							  pymtdiscount,
							  creditlimit,
							  salestype,
							  invaddrbranch,
							  taxref,
							  customerpoline,
							  typeid,
							  coments,
							  blacklist,
			  				  razoncompra, 
							  mediocontacto,
							  fechanacimiento,
							  pagpersonal,
							  NumRegIdTrib
			  		)
				  VALUES ('" . $_POST ['DebtorNo'] . "',
					  '" . ($codificacionJibe == 0 ? strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName1'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName2'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName3'], ENT_NOQUOTES ) ) ) ) : utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName1'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName2'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName3'], ENT_NOQUOTES ) ) ) ))) . "',
					  '" . ($codificacionJibe == 0 ? strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName1'], ENT_NOQUOTES ) ) ) : utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName1'], ENT_NOQUOTES ) ) ))) . "',
					  '" . ($codificacionJibe == 0 ? strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName2'], ENT_NOQUOTES ) ) ) : utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName2'], ENT_NOQUOTES ) ) ))) . "',
					  '" . ($codificacionJibe == 0 ? strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName3'], ENT_NOQUOTES ) ) ) : utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName3'], ENT_NOQUOTES ) ) ))) . "',
					  '" . ($codificacionJibe == 0 ? strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CommercialName'], ENT_NOQUOTES ) ) ) : utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CommercialName'], ENT_NOQUOTES ) ) ))) . "',
					  '" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address1'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address1'] )) . "',
					  '" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address2'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address2'] )) . "',
					  '" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address3'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address3'] )) . "',
					  '" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $vrEstado, ENT_NOQUOTES ) : utf8_decode( $vrEstado )) . "',
					  '" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address5'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address5'] )) . "',
					  '" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address6'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address6'] )) . "',
					  '" . $_POST ['CurrCode'] . "',
					  curdate(),
					  " . $_POST ['HoldReason'] . ",
					  '" . $_POST ['PaymentTerms'] . "',
					  " . ($_POST ['Discount']) / 100 . ",
					  '" . $_POST ['DiscountCode'] . "',
					  " . ($_POST ['PymtDiscount']) / 100 . ",
					  " . $_POST ['CreditLimit'] . ",
					  '" . $_POST ['SalesType'] . "',
					  '" . $AddrInvBranch . "',
					  '" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['TaxRef'], ENT_NOQUOTES ) : utf8_decode(htmlspecialchars_decode ( $_POST ['TaxRef'], ENT_NOQUOTES ))) . "',
					  '" . $CustomerPOLine . "',
					  '" . $_POST ['typeid'] . "',
					  '" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['coments'], ENT_NOQUOTES ) : utf8_decode(htmlspecialchars_decode ( $_POST ['coments'], ENT_NOQUOTES ))) . "',
					  '" . $_POST ['lista'] . "',
					  		
					  '" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['razoncompra'], ENT_NOQUOTES ) : utf8_decode(htmlspecialchars_decode ( $_POST ['razoncompra'], ENT_NOQUOTES ))) . "',
					  '" . htmlspecialchars_decode ( $_POST ['mediocontacto'], ENT_NOQUOTES ) . "',
					  '" . $fechaini . "',
					  '" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['pagpersonal'], ENT_NOQUOTES ) : utf8_decode(htmlspecialchars_decode ( $_POST ['pagpersonal'], ENT_NOQUOTES )))  . "',
					  '" . $_POST ['NumRegIdTrib']. "'
					  )";
				
				$sql1= "UPDATE bankscustomer 
				SET debtorno= '" . $_POST ['DebtorNo'] . "'
				WHERE identificador=  '" . $_POST ['identificador'] . "'";

				 echo "<pre/>El sql : " . $sql;
				 echo "<pre/>El sql1 es:".$sql1;

				$ErrMsg = _ ( 'Este cliente no fue ingresado a la base de datos por que' );
				$result = DB_query ( $sql, $db, $ErrMsg );
				$result1 = DB_query ( $sql1, $db);
				
				// insertar en prospect_movimientos si es el caso
				if ($_POST ['typeid'] == 7) { // prospecto
					$qry = "insert into prospect_movimientos (u_proyecto,dia,mes,anio,concepto,descripcion,prioridad,u_user,UserId,fecha,activo,catcode,idstatus,fecha_compromiso,fecha_alta,debtorno,confirmado)
								VALUES ('" . $_POST ['Area'] . "',day(current_date),month(current_date),year(current_date),'" . strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName1'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName2'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName3'], ENT_NOQUOTES ) ) ) ) . "','" . strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName1'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName2'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName3'], ENT_NOQUOTES ) ) ) ) . "',3,'" . $_SESSION ['UserID'] . "','" . $_SESSION ['UserID'] . "',current_date,1,1,1,current_date,current_date,'" . $_POST ['DebtorNo'] . "',0)";
					
					$r = DB_query ( $qry, $db );
				}
				
				$BranchCode = substr ( $_POST ['DebtorNo'], 0, 10 );
				// *************************************************
				// *******INICIO INSERTA EN TABLA CUSTBRANCH********
				// *************************************************
				$sql = "INSERT INTO custbranch (branchcode,
						  debtorno,
						  brname,
						  braddress1,
						  braddress2,
						  braddress3,
						  braddress4,
						  braddress5,
						  braddress6,
						  brnumint,
						  brnumext,
						  lat,
						  lng,
						  specialinstructions,
						  estdeliverydays,
						  fwddate,
						  salesman,
						  phoneno,
						  movilno,
						  nextelno,
						  faxno,
						  contactname,
						  area,
						  email,
						  taxgroupid,
						  defaultlocation,
						  brpostaddr1,
						  brpostaddr2,
						  brpostaddr3,
						  brpostaddr4,
				  		  brpostaddr5,
				  		  brpostaddr6,
						  disabletrans,
						  defaultshipvia,
						  custbranchcode,
						  deliverblind,
						  taxid,
						  custdata1,
						  custdata2,
						  custdata3,
						  custdata4,
						  custdata5,
						  custdata6,
						  paymentname,
						  nocuenta,
				  		  lineofbusiness,
				  		  SectComClId,
				  		  custpais,
				  		  NumeAsigCliente,
				  		  descclientecomercial,
				  		  descclientepropago,
				  		  descclienteop,
				  		  typeaddenda,
				  		  DiasRevicion,
				  		  DiasPago,
                          prefer,
						  discountcard,
                                                  typecomplement 
				  		)
				  VALUES ('" . $BranchCode . "',
					  '" . $_POST ['DebtorNo'] . "',
					  '" . ($codificacionJibe == 0 ? strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName1'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName2'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName3'], ENT_NOQUOTES ) ) ) ) : utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['CustName1'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName2'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['CustName3'], ENT_NOQUOTES ) ) ) ))) . "',
					  '" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address1'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address1'] )) . "', 
					  '" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address3'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address3'] )) . "',  
					  '" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $vrEstado, ENT_NOQUOTES ) : utf8_decode( $vrEstado )) . "',  
					  '" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address5'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address5'] )) . "',  
					  '" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address6'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address6'] )) . "',  
					  '" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['Address2'], ENT_NOQUOTES ) : utf8_decode( $_POST ['Address2'] )) . "',
					  '" . htmlspecialchars_decode ( $_POST ['brnumint'], ENT_NOQUOTES ) . "',
					  '" . htmlspecialchars_decode ( $_POST ['brnumext'], ENT_NOQUOTES ) . "',  
					  '" . $latitude . "',
					  '" . $longitude . "',
					  '" . ($codificacionJibe == 0 ? $specialinstructions : utf8_decode($specialinstructions)) . "',
					  0,
					  0,
					  '" . $_POST ['Salesman'] . "',
					  '" . $_POST ['PhoneNo'] . "',
					  '" . $_POST ['MovilNo'] . "',
					  '" . $_POST ['NextelNo'] . "',
					  '" . $_POST ['FaxNo'] . "',
					  '" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['ContactName'], ENT_NOQUOTES ) : utf8_decode(htmlspecialchars_decode ( $_POST ['ContactName'], ENT_NOQUOTES )) ) . "',
					  '" . $_POST ['Area'] . "',
					  '" . $_POST ['Email'] . "',
					  " . htmlspecialchars_decode ( $_POST ['TaxGroup'], ENT_NOQUOTES ) . ",
					  '" . $_POST ['DefaultLocation'] . "',
					  '" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['BrPostAddr1'], ENT_NOQUOTES ) : utf8_decode( $_POST ['BrPostAddr1'] )) . "',
					  '" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $_POST ['BrPostAddr2'], ENT_NOQUOTES ) : utf8_decode( $_POST ['BrPostAddr2'] )) . "',
					  '" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $BrPostAddr3, ENT_NOQUOTES ) : utf8_decode( $BrPostAddr3 )) . "',
					  '" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $BrPostAddr4, ENT_NOQUOTES ) : utf8_decode( $BrPostAddr4 )) . "',
					  '" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $BrPostAddr5, ENT_NOQUOTES ) : utf8_decode( $BrPostAddr5 )) . "',
					  '" . ($codificacionJibe == 0 ? htmlspecialchars_decode ( $BrPostAddr6, ENT_NOQUOTES ) : utf8_decode( $BrPostAddr6 )) . "',
					  '0',
					  '1',
					  '1',
					  '" . htmlspecialchars_decode ( $_POST ['DeliverBlind'], ENT_NOQUOTES ) . "',
					  '" . htmlspecialchars_decode ( $_POST ['taxid'], ENT_NOQUOTES ) . "',
					  '" . htmlspecialchars_decode ( $_POST ['custdata1'], ENT_NOQUOTES ) . "',
					  '" . htmlspecialchars_decode ( $_POST ['custdata2'], ENT_NOQUOTES ) . "',
					  '" . htmlspecialchars_decode ( $_POST ['custdata3'], ENT_NOQUOTES ) . "',
					  '" . htmlspecialchars_decode ( $_POST ['custdata4'], ENT_NOQUOTES ) . "',
					  '" . htmlspecialchars_decode ( $_POST ['custdata5'], ENT_NOQUOTES ) . "',
					  '" . htmlspecialchars_decode ( $_POST ['custdata6'], ENT_NOQUOTES ) . "',
					  '" . $_POST ['paymentname'] . "',
					  '" . $_POST ['nocuenta'] . "',
					  '" . htmlspecialchars_decode ( $_POST ['giro'], ENT_NOQUOTES ) . "',
					  '" . $_POST ['SectComClId'] . "',
					  '" . $_POST ['custpais'] . "',
					  '" . $_POST ['NumeAsigCliente'] . "',
					  '" . $_POST ['DescClienteComercial'] . "',
					  '" . $_POST ['DescClienteProPago'] . "',
					  '" . $_POST ['descclienteop'] . "',
					  '" . $_POST ["typeaddenda"] . "',
					  '" . $_POST ['DiasRevicion'] . "',
					  '" . $_POST ['DiasPago'] . "',
					  '" . $_POST ['prefer'] . "',
					  '" . htmlspecialchars_decode ( $_POST ['discountcard'], ENT_NOQUOTES ) . "',
                                              '".$_POST['typecomplement']."'
					  )";
				
				$ErrMsg = _ ( 'Los datos de la oficina del cliente no se insertaron por que' );
				
				$result = DB_query ( $sql, $db, $ErrMsg );

				echo 'result: '.$result;

				$sql="INSERT INTO `custbranchnumprovider`(`idcustbranch`,`idtypeaddenda`,`numprovider`) values('".$BranchCode."','".$_POST ["typeaddenda"]."','".$_POST ["noproveedor"]."')";
				$result = DB_query ( $sql, $db, $ErrMsg );
				// actualizar tipos de impuesto
				$qry = "DELETE FROM debtortaxes
						WHERE debtorno = '" . $_POST ['DebtorNo'] . "'";
				$r = DB_query ( $qry, $db, $ErrMsg );
				
				if ($_POST ['taxclient']) {
					$lista = trim ( $_POST ['taxclient'], "|" );
					$arr = explode ( "|", $lista );
					
					$qry = "INSERT INTO debtortaxes VALUES ";
					for($i = 0; $i < count ( $arr ); $i ++) {
						$qry .= "('" . $_POST ['DebtorNo'] . "','" . $arr [$i] . "'),";
					}
					
					$qry = substr ( $qry, 0, strlen ( $qry ) - 1 );
					$r = DB_query ( $qry, $db, $ErrMsg );
				}


				$qry = "DELETE FROM valuecomplement
						WHERE debtorno = '" . $_POST ['DebtorNo'] . "'";
				$r = DB_query ( $qry, $db, $ErrMsg );

				if ($_POST ['complementclient']) {
					$lista = trim ( $_POST ['complementclient'], "|" );
					$arr = explode ( "|", $lista );
					
					$qry = "INSERT INTO valuecomplement VALUES ";
					for($i = 0; $i < count ( $arr ); $i ++) {
						$qry .= "('','" . $arr [$i] . "','','" . $_POST ['DebtorNo'] . "'),";
					}
					
					$qry = substr ( $qry, 0, strlen ( $qry ) - 1 );
					$r = DB_query ( $qry, $db, $ErrMsg );
				}
				
				// inserta email
				$emails = $_POST ['Email'];
				if (! IsEmailAddress ( $emails )) {
					$emails = "";
				} else {
					// consulto si el email existe en la base de datos
					$SQL = "SELECT * FROM custmails
						  WHERE debtorno='" . $_POST ['DebtorNo'] . "'
						  AND branchcode='" . $BranchCode . "'
						  AND email='" . $emails . "'";
					$ErrMsg = _ ( 'ERROR CRITICO' ) . ' ' . _ ( 'ANOTE EL ERROR' ) . ': ' . _ ( 'No se obtuvieron los datos del email' );
					$DbgMsg = _ ( 'El siguiente SQL se utilizo' );
					$Result = DB_query ( $SQL, $db, $ErrMsg, $DbgMsg, true );
					// en caso de que no exista se inserta
					if (DB_num_rows ( $Result ) == 0) {
						$SQL = "INSERT INTO custmails(debtorno,branchcode,email,trandate,active)
							VALUES('" . $_POST ['DebtorNo'] . "','" . $BranchCode . "','" . $emails . "',now(),1)";
						$ErrMsg = _ ( 'ERROR CRITICO' ) . ' ' . _ ( 'ANOTE EL ERROR' ) . ': ' . _ ( 'No se realizo la insercion del email' );
						$DbgMsg = _ ( 'El siguiente SQL se utilizo' );
						$Result = DB_query ( $SQL, $db, $ErrMsg, $DbgMsg, true );
					}
				}
				if ($_SESSION ['EnviaMailNotificacion'] == 1) {
					$SQLCliente = "SELECT debtorsmaster.debtorno,
									debtorsmaster.name,
									custbranch.taxid
							FROM debtorsmaster
								INNER JOIN custbranch ON debtorsmaster.debtorno = custbranch.branchcode
							WHERE debtorsmaster.debtorno = '" . $_POST ['DebtorNo'] . "'";
					$ResultCliente = DB_query ( $SQLCliente, $db );
					$rowmail = DB_fetch_array ( $ResultCliente );
					$debtornomail = $rowmail ['debtorno'];
					$namemail = $rowmail ['name'];
					$rfcmail = $rowmail ['taxid'];
					
					$SQLCliente = "SELECT www_users.userid,
											www_users.realname,
											www_users.defaultarea
									FROM www_users 
									WHERE www_users.userid =  '" . $_SESSION ['UserID'] . "'";
					$ResultCliente = DB_query ( $SQLCliente, $db );
					$rowmail = DB_fetch_array ( $ResultCliente );
					$useridmail = $rowmail ['userid'];
					$namemailusuario = $rowmail ['realname'];
					$defaultarea = $rowmail ['defaultarea'];
					$tabla = '<table border=1>';
					$tabla = $tabla . '<tr>';
					$tabla = $tabla . '<th>' . _ ( 'Codigo' ) . '</th>';
					$tabla = $tabla . '<th>' . _ ( 'Cliente' ) . '</th>';
					$tabla = $tabla . '<th>' . _ ( 'RFC' ) . '</th>';
					$tabla = $tabla . '<th>' . _ ( 'Usuario' ) . '</th>';
					$tabla = $tabla . '</tr>';
					$tabla = $tabla . '<tr>';
					$tabla = $tabla . '<td>' . $debtornomail . '</td>';
					$tabla = $tabla . '<td>' . $namemail . '</td>';
					$tabla = $tabla . '<td>' . $rfcmail . '</td>';
					$tabla = $tabla . '<td>' . $useridmail . ' - ' . $namemailusuario . '</td>';
					$tabla = $tabla . '</tr>';
					$tabla = $tabla . '</table>';
					$SQL = "SELECT NotificacionesEmail.email
							FROM NotificacionesEmail
							WHERE NotificacionesEmail.tiponotificacion = 'cliente'
							AND NotificacionesEmail.areacode = '" . $_POST ['Area'] . "'";
					if ($_SESSION ['UserID'] == "admin") {
						echo '<pre>' . $SQL;
					} //
					$mensaje = "Se manda este correo para informar sobre el cliente recientemente agregado al sistema <br><br>" . $tabla;
					$from = "soporte@portalito.com";
					$senderName = 'Cliente dado de alta ';
					$asunto = "Cliente dado de alta";
					$Result = DB_query ( $SQL, $db );
					while ( $myrow = DB_fetch_array ( $Result ) ) {
						$mail = new Mail ();
						$mail->protocol = 'smtp';
						$mail->hostname = 'localhost';
						$mail->port = 25;
						$mail->timeout = 25;
						$mail->setTo ( $myrow ['email'] );
						$mail->setFrom ( $from );
						$mail->setSender ( $senderName );
						$mail->setSubject ( $asunto );
						$mail->setHtml ( $mensaje );
						$mail->send ();
					}
				}
				// inserta razon social por cliente
				if($_SESSION['DatabaseName']=="erppisumma_DES" OR $_SESSION['DatabaseName']=="erppisumma_CAPA" OR $_SESSION['DatabaseName']=="erppisumma"){
					if (isset($_POST['TotalLegal'])){
						$totalsucursales=$_POST['TotalLegal'];
						for ( $suc = 0 ; $suc <= $totalsucursales ; $suc++) {
							//echo "<br>suc: " . $_POST['Namesucursal'.$suc] . " ==> " . $_POST['SucursalSel'.$suc];
							if ($_POST['LegalSel'.$suc]==TRUE){
								$namesucursal=$_POST['NameLegal'.$suc];
								$sql="INSERT INTO debtorsmaster_legalid (debtorno,legalid)";
								$sql=$sql." values('".$_POST ['DebtorNo']."','".$namesucursal."')";
								$ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
								$DbgMsg = _('El SQL utilizado es:');
								echo "<br> ".$sql;
								$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
							}
						}
					}

				}
				echo 'NO: '.$_POST ['DebtorNo'] ;
				
				// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				// /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				
				// $_SESSION['DebtorNo']=$_POST['DebtorNo'];
				// /LINK PARA ASIGNAR DIAS Y LIMITES DE CREDITO AL CLIENTE
				// $_POST['DebtorNo']=$_GET['DebtorNo'];
				// header('Location:' . $rootpath . '/ReporteLimitxCliente.php?'. SID .'&DebtorNo=' .$_POST['DebtorNo'].'&CustName1='.$_POST['CustName1'].'');
				
				// echo '<tr><td colspan=3 style="text-align:center;"><a href="'. $rootpath .'/ReporteLimitxCliente.php?' . SID .'&cliente=' .$_SESSION['CustomerID'].'"></a></td></tr>';
				
				// ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				if (isset ( $_POST ['uniquebranch'] )) {
					// si venismo de pedidos de venta, regresamos a esa pagina
					if ($_POST ['urlfromorders'])
						header ( "Location:" . $_POST ['urlfromorders'] );
					
					if ($_SESSION ['frompage'] == 'selectorderitems') {
						$_SESSION ['frompage'] = '';
						
						echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/" . $SelectOrderItemsV5 . "?NewOrder=Yes&Select=" . $_POST ['DebtorNo'] . ' - ' . $BranchCode . '|Auto:' . $SelectedVehicle . "&identifier=" . $identifier . "'>";
						echo '<div class="centre">' . _ ( 'You should automatically be forwarded to the entry of a new Customer Branch page' ) . '. ' . _ ( 'If this does not happen' ) . ' (' . _ ( 'if the browser does not support META Refresh' ) . ') ' . "<a href='" . $rootpath . "/" . $SelectOrderItemsV5 . "?" . SID . "&Select=" . $_POST ['DebtorNo'] . ' - ' . $BranchCode . '|Auto:' . $SelectedVehicle . "&identifier=" . $identifier . '</a></div>';
					} else {
						
						echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/SelectCustomer.php?Select=" . $_POST ['DebtorNo'] . "'>";
						echo '<div class="centre">' . _ ( 'You should automatically be forwarded to the entry of a new Customer Branch page' ) . '. ' . _ ( 'If this does not happen' ) . ' (' . _ ( 'if the browser does not support META Refresh' ) . ') ' . "<a href='" . $rootpath . "/SelectCustomer.php?" . SID . "&DebtorNo=" . $_POST ['DebtorNo'] . '.</div>';
					}
				} else {
					
					echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/CustomerBranches.php?" . SID . "&DebtorNo=" . $_POST ['DebtorNo'] . "'>";
					echo '<div class="centre">' . _ ( 'You should automatically be forwarded to the entry of a new Customer Branch page' ) . '. ' . _ ( 'If this does not happen' ) . ' (' . _ ( 'if the browser does not support META Refresh' ) . ') ' . "<a href='" . $rootpath . "/CustomerBranches.php?" . SID . "&DebtorNo=" . $_POST ['DebtorNo'] . '.</div>';
				}
				include ('includes/footer.inc');
				exit ();
			} // if continuar
		} // new client
		  
		// insertar registro en debtorsreminder si es viable
		if ($_POST ['chkactivarecordatorio']) {
			
			$dias = 0;
			if ($_POST ['chkdiasvence'] && $_POST ['diasvence'] != "")
				$dias = $_POST ['diasvence'];
			
			$msg = "";
			if ($_POST ['chkmensaje'] && $_POST ['mensaje'] != "")
				$msg = $_POST ['mensaje'];
			
			$moratorio = 0;
			if ($_POST ['chkmora'])
				$moratorio = 1;
			
			$repite = 0;
			if ($_POST ['chkrepiteenvio'] && $_POST ['repiteenvio'] > 0)
				$repite = $_POST ['repiteenvio'];
			
			$sql = "SELECT * FROM debtorsreminder WHERE debtorno = '".$_POST ['DebtorNo'] ."'";
			$res = DB_query ( $sql, $db );

			if(DB_num_rows($res) > 0){
				$row = DB_fetch_row ( $result );

				if(isset($_POST['reiniciarEnvio'])){
					$var = ", countRepeat = 0";
				}else{
					$var = '';
				}
			
				$qry = "UPDATE debtorsreminder 
									SET days = '$dias', message = '$msg', interes = '$moratorio', repeatv = '$repite' $var
								WHERE debtorno = '" . $_POST ['DebtorNo'] . "'";
			}else{
				$qry = "INSERT INTO debtorsreminder (debtorno,days,message,interes,repeatv) VALUES ('" . $_POST ['DebtorNo'] . "','$dias','$msg','$moratorio','$repite')";
			}
			$r = DB_query ( $qry, $db );
		}
	} else {
		prnMsg ( _ ( 'NO se actualiz&oacute; ni borr&oacute; informaci&oacute;n' ), 'warn' );
	}
} elseif (isset ( $_POST ['delete'] )) {
	// the link to delete a selected record was clicked instead of the submit button
	
	$CancelDelete = 0;
	// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'
	
	$sql = "SELECT COUNT(*) FROM debtortrans WHERE debtorno='" . $_POST ['DebtorNo'] . "'";
	$result = DB_query ( $sql, $db );
	$myrow = DB_fetch_row ( $result );
	if ($myrow [0] > 0) {
		$CancelDelete = 1;
		prnMsg ( _ ( 'No se puede eliminar el cliente ya que cuenta con operaciones en la base de datos' ), 'warn' );
		echo '<br> ' . _ ( 'Existen' ) . ' ' . $myrow [0] . ' ' . _ ( 'transacciones registradas para este cliente' );
	} else {
		$sql = "SELECT COUNT(*) FROM salesorders WHERE debtorno='" . $_POST ['DebtorNo'] . "'";
		$result = DB_query ( $sql, $db );
		$myrow = DB_fetch_row ( $result );
		if ($myrow [0] > 0) {
			$CancelDelete = 1;
			prnMsg ( _ ( 'No se puede eliminar el cliente ya que cuenta con ordenes de venta' ), 'warn' );
			echo '<br> ' . _ ( 'Existen ' ) . ' ' . $myrow [0] . ' ' . _ ( 'ordenes registradas para este cliente' );
		} else {
			$sql = "SELECT COUNT(*) FROM salesanalysis WHERE cust='" . $_POST ['DebtorNo'] . "'";
			$result = DB_query ( $sql, $db );
			$myrow = DB_fetch_row ( $result );
			if ($myrow [0] > 0) {
				$CancelDelete = 1;
				prnMsg ( _ ( 'No se puede eliminar este cliente ya que se encuentra en los analisis de ventas' ), 'warn' );
				echo '<br> ' . _ ( 'Existen' ) . ' ' . $myrow [0] . ' ' . _ ( 'analisis de ventas registradas para este cliente' );
			} else {
				$sql = "SELECT COUNT(*) FROM custbranch WHERE debtorno='" . $_POST ['DebtorNo'] . "'";
				$result = DB_query ( $sql, $db );
				$myrow = DB_fetch_row ( $result );
				if ($myrow [0] > 0) {
					$CancelDelete = 1;
					prnMsg ( _ ( 'No se puede eliminar este cliente por que tiene oficinas asignadas' ), 'warn' );
					echo '<br> ' . _ ( 'Existen' ) . ' ' . $myrow [0] . ' ' . _ ( 'oficinas relacionadas con este cliente' );
				}
			}
		}
	}
	if ($CancelDelete == 0) { // ie not cancelled the delete as a result of above tests
		$sql = "DELETE FROM custcontacts WHERE debtorno='" . $_POST ['DebtorNo'] . "'";
		$result = DB_query ( $sql, $db );
		$sql = "DELETE FROM debtorsmaster WHERE debtorno='" . $_POST ['DebtorNo'] . "'";
		$result = DB_query ( $sql, $db );
		/*eliminamos   razon social para este cliente*/
		if($_SESSION['DatabaseName']=="erppisumma_DES" OR $_SESSION['DatabaseName']=="erppisumma_CAPA" OR $_SESSION['DatabaseName']=="erppisumma"){
			$sql="DELETE from debtorsmaster_legalid WHERE debtorno = '".$_POST ['DebtorNo']."'";
			$ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
			$DbgMsg = _('El SQL utilizado es:');
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		}
		$sql = "DELETE FROM bankscustomer WHERE debtorno='" . $_POST ['DebtorNo'] . "'";
		$result = DB_query ( $sql, $db );
		
		prnMsg ( _ ( 'Cliente' ) . ' ' . $_POST ['DebtorNo'] . ' ' . _ ( 'ha sido eliminad, asi como sus posibles contactos' ) . ' !', 'success' );
		include ('includes/footer.inc');
		exit ();
	} // end if Delete Customer
}

if (isset ( $reset ) ) {
	// echo 'entraaaa';
	unset ( $_POST ['CustName1'] );
	unset ( $_POST ['CustName2'] );
	unset ( $_POST ['CustName3'] );
	unset ( $_POST ['CommercialName'] );
	unset ( $_POST ['Address1'] );
	unset ( $_POST ['Address2'] );
	unset ( $_POST ['Address3'] );
	unset ( $_POST ['Address4'] );
	unset ( $_POST ['Address4a'] );
	unset ( $_POST ['Address5'] );
	unset ( $_POST ['Address6'] );
	unset ( $_POST ['HoldReason'] );
	unset ( $_POST ['PaymentTerms'] );
	unset ( $_POST ['Discount'] );
	unset ( $_POST ['DiscountCode'] );
	unset ( $_POST ['PymtDiscount'] );
	unset ( $_POST ['CreditLimit'] );
	// Leave Sales Type set so as to faciltate fast customer setup
	// unset($_POST['SalesType']);
	unset ( $_POST ['DebtorNo'] );
	unset ( $_POST ['InvAddrBranch'] );
	unset ( $_POST ['TaxRef'] );
	unset ( $_POST ['CustomerPOLine'] );
	unset ( $_POST ['lista'] );
	unset ( $_POST ['custpais'] );
	//complementos factura
	unset ( $_POST ['NumRegIdTrib'] );
	// Leave Type ID set so as to faciltate fast customer setup
	// unset($_POST['typeid']);
}

/* DebtorNo could be set from a post or a get when passed as a parameter to this page */
$DebtorNo="";
if (isset ( $_POST ['DebtorNo'] )) {
	$DebtorNo = $_POST ['DebtorNo'];
} elseif (isset ( $_GET ['DebtorNo'] )) {
	$DebtorNo = $_GET ['DebtorNo'];
}
if (isset ( $_POST ['identificador'] )) {
	$identificador1 = $_POST ['identificador'];
} elseif (isset ( $_GET ['identificador'] )) {
	$identificador1 = $_GET ['idrentificador'];
}
if (isset ( $_POST ['ID'] )) {
	$ID = $_POST ['ID'];
} elseif (isset ( $_GET ['ID'] )) {
	$ID = $_GET ['ID'];
} else {
	$ID = '';
}
if (isset ( $_POST ['ws'] )) {
	$ws = $_POST ['ws'];
} elseif (isset ( $_GET ['ws'] )) {
	$ws = $_GET ['ws'];
}
if (isset ( $_POST ['Edit'] )) {
	$Edit = $_POST ['Edit'];
} elseif (isset ( $_GET ['Edit'] )) {
	$Edit = $_GET ['Edit'];
} else {
	$Edit = '';
}
if (isset ( $_POST ['Add'] )) {
	$Add = $_POST ['Add'];
} elseif (isset ( $_GET ['Add'] )) {
	$Add = $_GET ['Add'];
}

//if(isset($_POST [''])){			$=$_POST [''];			}else{$="";}
// This link is already on menu bar
// echo "<a href='" . $rootpath . '/SelectCustome r.php?' . SID . "'>" . _('Back to Customers') . '</a><br>';

if (! isset ( $DebtorNo ) || $DebtorNo =="") {
	/*
	 * If the page was called without $_POST['DebtorNo'] passed to page then assume a new customer is to be entered show a form with a Debtor Code field other wise the form showing the fields with the existing entries against the customer will show for editing with only a hidden DebtorNo field
	 */
	
	echo "<form name='FDatos' method='POST'  action='" . $_SERVER ['PHP_SELF'] . "'>";
	
	echo "<input type='Hidden' name='New' value='Yes'>";
	echo "<input type='hidden' name='taxclient' id='taxclient' value=''>";
	echo "<input type='hidden' name='complementclient' id='complementclient' value=''>";
	
	$DataError = 0;
	echo '<table border=2 cellspacing=4><tr><td valign="top"><table border="0">';
	
	/*
	 * if $AutoDebtorNo in config.php has not been set or if it has been set to a number less than one, then provide an input box for the DebtorNo to manually assigned
	 */
	echo '<tr><td colspan="2" style="text-align:center;">' . _ ( 'DATOS PERSONALES' ) . '</td></tr>';
	echo 'No:'.$_POST['DebtorNo'].'-';
	echo '<tr><td colspan="2" style="text-align:center;">' . _ ( 'Los datos con * son obligatorios' ) . '</td></tr>';
	
	if ($_SESSION ['AutoDebtorNo'] == 0) {
		echo '<tr><td>' . _ ( 'Codigo Cliente' ) . ":</td><td><input tabindex=1 type='Text' name='DebtorNo' size=11 maxlength=10></td></tr>";
	}
	echo '<tr><td>* ' . _ ( 'A. Paterno/Razon Social' ) . ':</td>
		<td><input tabindex=2 type="Text" name="CustName1" size=35 maxlength=200 value="' . $CustName1. '"></td></tr>';
	echo '<tr><td>' . _ ( 'A. Materno' ) . ':</td>
		<td><input tabindex=2 type="Text" name="CustName2" size=35 maxlength=100 value="' . $CustName2. '"></td></tr>';
	echo '<tr><td>' . _ ( 'Nombre(s)' ) . ':</td>
		<td><input tabindex=2 type="Text" name="CustName3" size=35 maxlength=100 value="' . $CustName3 . '"></td></tr>';
	echo '<tr><td>' . _ ( 'Nombre Comercial' ) . ':</td>
		<td><input tabindex=2 type="Text" name="CommercialName" size=35 maxlength=100 value="' . $CommercialName . '"></td></tr>';
	echo '<tr><td>* ' . _ ( 'RFC' ) . ':</td>';
	
	echo '<td><input tabindex=3 type="Text" name="taxid" size=15 maxlength=15 value="' . $txtid. '"></td></tr>';
	
	echo '<tr valign="top"><td>*' . _ ( 'Email' ) . ':</td>';
	echo '<td><input tabindex=4 type="Text" name="Email" size=42 maxlength=55 value="' . $Email. '"><br>Si el cliente no tiene email introduzca: sincorreoelectronico@empresa.com';
	echo '</td></tr>';
	echo '<tr><td>* ' . _ ( 'Telefono Fijo' ) . ':</td>';
	echo '<td><input tabindex=5 type="Text" name="PhoneNo" size=22 maxlength=20 value="' . $PhoneNo . '"></td></tr>';
	
	echo '<tr><td>' . _ ( 'Telefono Movil' ) . ':</td>';
	echo '<td><input type="Text" name="MovilNo" size=22 maxlength=20 value="' . $MovilNo . '"></td></tr>';
	echo '<tr><td>' . _ ( 'Nextel' ) . ':</td>';
	echo '<td><input type="Text" name="NextelNo" size=22 maxlength=20 value="' . $NextelNo . '"></td></tr>';
	$dirsepomex = '<img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _ ( 'Direcciones Sepomex' ) . '" alt=""> ';
	$trabsel = 1;
	$dirsepomex = $dirsepomex . "<a style='display:inline' href='#' onclick='javascript:var win = window.open(\"SepomexSearch_Customer.php?idOpener=$trabsel\", \"sepomex\", \"width=500,height=500,scrollbars=1,left=200,top=150\"); win.focus();'>" . _ ( "Seleccionar direccion" ) . "</a>";
	echo '<tr><td>* ' . _ ( 'Direccion' ) . ':</td>
		<td><input tabindex=6 type="Text" id=Address1 name="Address1" size=42 maxlength=150 value="' . $Address1 . '">' . $dirsepomex . '</td></tr>';
	if (Havepermission ( $_SESSION ['UserID'], 2000, $db ) == 1) { //numero ext, int
		echo '<tr><td>' . _ ( 'Num Ext' ) . ':</td>
		<td><input tabindex=8 type="Text" name="brnumext" size=22 maxlength=50 value="' . $brnumext . '"></td></tr>';
		echo '<tr><td>' . _ ( 'Num Int' ) . ':</td>
		<td><input tabindex=9 type="Text" name="brnumint" size=22 maxlength=50 value="' . $brnumint . '"></td></tr>';
	}
	echo '<tr><td> * ' . _ ( 'Colonia' ) . ':</td>
		<td><input tabindex=7 type="Text" id=Address2 name="Address2" size=42 maxlength=100 value="' . $Address2 . '"></td></tr>';
	echo '<tr><td>* ' . _ ( 'Ciudad' ) . ':</td>
		<td><input tabindex=10 type="Text" id=Address3 name="Address3" size=42 maxlength=80 value="' . $Address3 . '"></td></tr>';

	/*echo '<tr><td>* ' . _ ( 'Estado' ) . ':</td>
		<td><select name="Address4" id="Address4">';

	$qry = "Select * FROM states";
	$rss = DB_query ( $qry, $db );
	while ( $rows = DB_fetch_array ( $rss ) ) {
		$isselected="";
		if(isset($_POST ['Address4']) && $_POST ['Address4'] == $rows ['state']){
			$isselected="selected";
		}
		echo "<option value='" . $rows ['state'] . "' ".$isselected.">" . $rows ['state'] . "</option>";
	}
	
	echo '</select></td></tr>';*/

	echo '<tr><td>* ' . _ ( 'Estado' ) . ':</td>';
	if($_SESSION['DatabaseName']=="erppisumma_DES" OR $_SESSION['DatabaseName']=="erppisumma_CAPA" OR $_SESSION['DatabaseName']=="erppisumma"){
		
		$vrOpn=2;
		echo '<td><select name="Address4" id="Address4" >';
		$qry = "Select * FROM states";
		$rss = DB_query ( $qry, $db );
		echo "<option value='0'>Elige un estado </option>";
		while ( $rows = DB_fetch_array ( $rss ) ) {
			echo "<option value='" . $rows ['state'] . "'>" . $rows ['state'] . "</option>";
		}       
	    echo '</select>';		
		echo '</td></tr>';
		echo '<tr><td></td><td>	<input tabindex=12 type="Text" id="Address4a" name="Address4a" size=22 maxlength=20 value="' . $_POST ['Address4'] . '"> <br>';
		echo utf8_decode('Elige solo una opción');
		echo '</td></tr>';
	}else{
		$vrOpn=1;
		echo '<td><select name="Address4" id="Address4" >';
		$qry = "Select * FROM states";
		$rss = DB_query ( $qry, $db );
		echo "<option value='0'>Elige un estado </option>";
		while ( $rows = DB_fetch_array ( $rss ) ) {
			echo "<option value='" . $rows ['state'] . "'>" . $rows ['state'] . "</option>";
		}       
	    echo '</select>';		
		echo '</td></tr>';
	}
	echo '<tr><td>* ' . _ ( 'Pais' ) . ':</td>
		<td><input tabindex=12 type="Text" id="custpais" name="custpais" size=22 maxlength=20 value="' . $custpais . '"></td></tr>';
	echo '<tr><td>' . _ ( 'C.P.' ) . ':</td>
		<td><input tabindex=12 type="Text" id=Address5 name="Address5" size=22 maxlength=50 value="' . $Address5 . '"></td></tr>';
	echo '<tr><td>' . _ ( 'Direccion Extra' ) . ':</td>
		<td><input tabindex=13 type="Text" name="Address6" size=22 maxlength=20 value="' . $Address6 . '"></td></tr>';
	echo '<tr><td>' . _ ( 'Extra 1' ) . ' :</td>
		<td><input tabindex=14 type="Text" name="BrPostAddr1" size=22 maxlength=40 value="' . $BrPostAddr1 . '"></td></tr>';
	echo '<tr><td>' . _ ( $_SESSION ['NameExtra2'] ) . ':</td>
		<td><input tabindex=15 type="Text" name="BrPostAddr2" size=22 maxlength=40 value="' . $BrPostAddr2 . '"></td></tr>';
	echo '<tr><td>' . _ ( 'Sector Comercial' ) . ':</td>';
	echo '<td><select  name="SectComClId" id="SectComClId">';
	$SQL = "Select SectComClId, SectComClNom
			From SectComercialCl";
	$resulttag = DB_query ( $SQL, $db );
	echo '<option selected value="">' . _ ( 'Ninguno' ) . '</option>';
	while ( $myrowUN = DB_fetch_array ( $resulttag ) ) {
		if ($_POST ['SectComClId'] == $myrowUN ['SectComClId']) {
			echo '<option selected value="' . $myrowUN ['SectComClId'] . '">' . $myrowUN ['SectComClNom'] . '</option>';
		} else {
			echo '<option value="' . $myrowUN ['SectComClId'] . '">' . $myrowUN ['SectComClNom'] . '</option>';
		}
	}
	echo '</select></td></tr>';
	
	if (Havepermission ( $_SESSION ['UserID'], 1778, $db )==1) {
		$readonlydisccard = '';
	}else{
		$readonlydisccard = 'readonly';
	}
	
	echo '<tr><td>' . _('Membresia') . ':</td>
		<td><input type="Text" name="discountcard" size="22" maxlength="40" value="' . $discountcard . '" ' . $readonlydisccard . ' ></td></tr>';
	
		
	/*
	 * echo '<tr><td colspan=2><hr color="Darkblue" width=80%></td></tr>'; echo '<tr><td>'._('Fax').':</td>'; echo '<td><input tabindex=10 type="Text" name="FaxNo" size=22 maxlength=20 value="'. $_POST['FaxNo'].'"></td></tr>';
	 */
	
	echo '<tr><td colspan=2><hr color="Darkblue" width=80%>';
	echo '</td></tr>';
	
	echo '<tr><td colspan="2" style="text-align:center;">' . _ ( 'DATOS VEHICULOS' ) . '</td></tr>';
	echo '<tr><td>' . _ ( 'No. Automoviles' ) . ':</td> 
        <td><input tabindex=16 type="Text" name="custdata1" size=4 maxlength=2 class=number value="' . $custdata1 . '"></td></tr>';
	echo '<tr><td>' . _ ( 'No. Camionetas' ) . ':</td>
        <td><input tabindex=17 type="Text" name="custdata2" size=4 maxlength=2 class=number value="' . $custdata2 . '"></td></tr>';
	echo '<tr><td>' . _ ( 'No. Camiones' ) . ':</td> 
        <td><input tabindex=18 type="Text" name="custdata3" size=4 maxlength=2 class=number value="' . $custdata3 . '"></td></tr>';
	echo '<tr><td>' . _ ( 'No. Agricolas' ) . ':</td> 
        <td><input tabindex=19 type="Text" name="custdata4" size=4 maxlength=2 class=number value="' . $custdata4 . '"></td></tr>';
	echo '<tr><td>' . _ ( 'No. Industrial' ) . ':</td> 
        <td><input tabindex=20 type="Text" name="custdata5" size=4 maxlength=2 class=number value="' . $custdata5 . '"></td></tr>';
	echo '<tr><td>' . _ ( 'No. Muevetierra' ) . ':</td> 
        <td><input tabindex=21 type="Text" name="custdata6" size=4 maxlength=2 class=number value="' . $custdata6 . '"></td></tr>';
	echo '<tr><td colspan=2><hr color="Darkblue" width=80%></td></tr>';
	
	// *********************************//
	// ***INICIO DATOS DE SUCURSAL******//
	// *********************************//
	echo '<tr><td colspan="2" style="text-align:center;">' . _ ( 'DATOS SUCURSAL' ) . '</td></tr>';
	// SQL to poulate account selection boxes
	/*
	 * echo '<tr><td>'._('RFC').':</td>'; if (!isset($_POST['taxid'])) {$_POST['taxid']='';} echo '<td><input tabindex=18 type="Text" name="taxid" size=15 maxlength=15 value="'. $_POST['txtid'].'"></td></tr>';
	 */
	
	echo '<tr><td>' . _ ( 'Tipo de Industria' ) . ':</td>';
	if (! isset ( $_POST ['giro'] )) {
		$_POST ['giro'] = '';
	}
	$qry = "Select * FROM giroscliente
			Where status=1";
	$rs = DB_query ( $qry, $db );
	echo '<td><select name="giro" >';
	echo '<option selected value="">' . _ ( 'Ninguno' ) . '</option>';
	while ( $regs = DB_fetch_array ( $rs ) ) {
		if ($_POST ['giro'] == $regs ['description'])
			echo '<option selected value="' . $regs ['description'] . '">' . $regs ['description'] . '</option>';
		else
			echo '<option value="' . $regs ['description'] . '">' . $regs ['description'] . '</option>';
	}
	echo '</select></td></tr>';
	
	// si no esta definido ver si el userid es vendedor para seleciconarlo por defecto
	if (isset($Salesman) && $Salesman == "") {
	//if ($_POST ['Salesman'] == "") {
		if (isset ( $_SESSION ['SalesManDefault'] )) {
			$_POST ['Salesman'] = $_SESSION ['SalesManDefault'];
		} else {
			$qry = "Select salesmancode from salesman
				where usersales = '" . $_SESSION ['UserID'] . "'";
			
			$r = DB_query ( $qry, $db );
			if (DB_num_rows ( $r ) > 0) {
				$row = DB_fetch_array ( $r );
				$_POST ['Salesman'] = $row ['salesmancode'];
			}
		}
	}
	
	// $sql = "SELECT salesmanname, salesmancode
	// FROM salesman WHERE type=1";
	$sql = "SELECT distinct salesmanname, salesmancode
		FROM salesman as sm
		LEFT JOIN areas as ar
		   ON sm.area=ar.areacode
	        LEFT JOIN tags as tg
		   ON ar.areacode=tg.areacode
		LEFT JOIN sec_unegsxuser as u
		   ON u.tagref = tg.tagref
		WHERE u.userid='" . $_SESSION ['UserID'] . "'
		and sm.type=1
                and sm.status = 'Active'
		ORDER BY  salesmanname desc";
	// echo $sql;
	$result = DB_query ( $sql, $db );

	if (DB_num_rows ( $result ) == 0) {
		echo '</table>';
		prnMsg ( _ ( 'There are no sales people defined as yet' ) . ' - ' . _ ( 'customer branches must be allocated to a sales person' ) . '. ' . _ ( 'Please use the link below to define at least one sales person' ), 'error' );
		echo "<br><a href='$rootpath/SalesPeople.php?" . SID . "'>" . _ ( 'Define Sales People' ) . '</a>';
		include ('includes/footer.inc');
		exit (); //
	}
	echo '<tr><td>' . _ ( 'Vendedor' ) . ':</td>';

	if($permisovendedor == 1 and !isset($_GET['DebtorNo'])) {
		$enabled = "disabled";
	} else {
		$enabled = "";
	}
	
	// if (Havepermission ( $_SESSION ['UserID'], 1311, $db )==0) {
	//
	// echo "<input type='hidden' name='Salesman' value='" . $_POST ['Salesman'] . "'>";
	// echo '<td><select tabindex=22 name="Salesman" disabled>';
	// } else {
	echo '<td><select tabindex=22 name="Salesman" ' . $enabled . '>';
	// }
	// 
	$hiddensalesman = "";
	
	while ( $myrow = DB_fetch_array ( $result ) ) {
		if($permisovendedor == 1 and !isset($_GET['DebtorNo']) and $myrow ['salesmanname'] == 'Ventas Mostrador') {
			$_POST ['Salesman'] = $myrow ['salesmancode'];
			$hiddensalesman = '<input type="hidden" name="Salesman" value="'.$myrow ['salesmancode'].'" />';
		}

		if (isset ( $_POST ['Salesman'] ) and $myrow ['salesmancode'] == $_POST ['Salesman']) {
			echo '<option selected VALUE=';
		} else {
			echo '<option VALUE=';
		}
		echo $myrow ['salesmancode'] . '>' . $myrow ['salesmanname'];
	} // end while loop
	
	echo '</select>'.$hiddensalesman.'</td></tr>';
	
	DB_data_seek ( $result, 0 );
	echo $_SESSION ['DefaultArea'];
	if ($_SESSION ['DefaultArea'] == '0') {
		$sql = 'SELECT areacode, areadescription FROM areas';
	} else {
		$sql = "SELECT areacode, areadescription FROM areas where areacode= '" . $_SESSION ['DefaultArea'] . "'";
	}
	
	$result = DB_query ( $sql, $db );
	if (DB_num_rows ( $result ) == 0) {
		echo '</table>';
		prnMsg ( _ ( 'No hay areas definidas' ) . ' - ' . _ ( 'para este cliente' ) . '. ' . _ ( 'Por favor use el enlace para definir areas de venta' ), 'error' );
		echo "<br><a href='$rootpath/Areas.php?" . SID . "'>" . _ ( 'Definir areas de venta' ) . '</a>';
		include ('includes/footer.inc');
		exit ();
	}
	
	echo '<tr><td>' . _ ( 'Area de Ventas' ) . ':</td>';
	echo '<td><select tabindex=23 name="Area">';
	while ( $myrow = DB_fetch_array ( $result ) ) {
		if (isset ( $_POST ['Area'] ) and $myrow ['areacode'] == $_POST ['Area']) {
			echo '<option selected VALUE=';
		} else {
			echo '<option VALUE=';
		}
		echo $myrow ['areacode'] . '>' . $myrow ['areadescription'];
	} // end while loop
	
	echo '</select></td></tr>';
	DB_data_seek ( $result, 0 );
	
	$sql = 'SELECT loccode, locationname FROM locations';
	$result = DB_query ( $sql, $db );
	
	if (DB_num_rows ( $result ) == 0) {
		echo '</table>';
		prnMsg ( _ ( 'No hay almacenes definidos' ) . ' - ' . _ ( 'El cliente debe tener configurado un almacen predeterminado' ) . '. ' . _ ( 'Por favor use el enlace para dar de alta los almacenes' ), 'error' );
		echo "<br><a href='$rootpath/Locations.php?" . SID . "'>" . _ ( 'Define Stock Locations' ) . '</a>';
		include ('includes/footer.inc');
		exit ();
	}
	echo '<tr style=display:none;><td>' . _ ( 'Almacen' ) . ':</td>';
	echo '<td><select tabindex=24 name="DefaultLocation">';
	
	while ( $myrow = DB_fetch_array ( $result ) ) {
		if (isset ( $_POST ['DefaultLocation'] ) and $myrow ['loccode'] == $_POST ['DefaultLocation']) {
			echo '<option selected VALUE=';
		} else {
			echo '<option VALUE=';
		}
		echo $myrow ['loccode'] . '>' . $myrow ['locationname'];
	} // end while loop
	
	echo '</select></td></tr>';
	
	echo '<tr><td>' . _ ( 'Grupo de Impuestos' ) . ':</td>';
	echo '<td><select tabindex=25 name="TaxGroup">';
	
	DB_data_seek ( $result, 0 );
	
	$sql = 'SELECT taxgroupid, taxgroupdescription FROM taxgroups';
	$result = DB_query ( $sql, $db );
	
	while ( $myrow = DB_fetch_array ( $result ) ) {
		if (isset ( $_POST ['TaxGroup'] ) and $myrow ['taxgroupid'] == $_POST ['TaxGroup']) {
			echo '<option selected VALUE=';
		} else {
			echo '<option VALUE=';
		}
		echo $myrow ['taxgroupid'] . '>' . $myrow ['taxgroupdescription'];
	} // end while loop
	
	echo '</select></td></tr>';
	/*seleccion de razon social por cliente*/
	if($_SESSION['DatabaseName']=="erppisumma_DES" OR $_SESSION['DatabaseName']=="erppisumma_CAPA" OR $_SESSION['DatabaseName']=="erppisumma"){
		echo '	<tr><td>&nbsp;</td></tr>';
		echo '	<tr>
					<th colspan=2>'. _('RAZON SOCIAL POR CLIENTE').'</th>
				</tr>';

		echo "	<tr>
					<td colspan=2 style=text-align:center>";//onClick=SelectCheckAuto(1,11)  onClick='DesSelectCheckAuto(1,11);'
		echo "		<input style='font-size:11px;' type=submit Name='SelectAllLegal'  Value='" . _('Sel. Todos') . "'>";
		echo "	<input style='font-size:11px;' type=submit Name='DeSelectAllLegal'  Value='" . _('Quitar Sel. a todos') . "'>
		        	</td>";
		echo "</tr>";

		echo '<tr><td colspan=2>';
		$sql = "SELECT  legalbusinessunit.legalid,legalbusinessunit.legalid as codsuc,t1.`debtorno` as codcliente, legalname as suc
				FROM legalbusinessunit  
				LEFT   JOIN  debtorsmaster_legalid t1 ON  legalbusinessunit.legalid =t1.legalid
				AND t1.debtorno='".$DebtorNo."' ORDER BY legalid ";
		$Result = DB_query($sql, $db);
		echo '<table width=80% align=center>';
		if (DB_num_rows($Result)>0 ) {
			echo "<tr><td ><hr></td></tr>";
		}
		$k=0; //row colour counter
		$j=0;
		$tagname = "";
		while($AvailRow = DB_fetch_array($Result)) {

			if ($tagname != $AvailRow['legalid']) {
			//	echo "<tr><th class='titulo_obj'>" . $AvailRow['legalid'] . "</th></tr>";
				$tagname = $AvailRow['legalname'];
			}

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}

			echo '<td nowrap style="font-weight:normal;">';
			$legalid=$AvailRow['legalid'];
			$codcliente=$AvailRow['codcliente'];
			$nombresuc=$AvailRow['suc'];
			if(is_null($$codcliente)) {
				if(!isset($_POST['DeSelectAllLegal'])){
					if (((isset($_POST['LegalSel'.$j])) and  ($_POST['LegalSel'.$j]<> '') or isset($_POST['SelectAllLegal']))){
						echo '<INPUT type="checkbox" name="LegalSel' .$j . '" checked value="' . ($j+1) . '">';
					}else{
						echo '<INPUT type="checkbox" name="LegalSel' . $j . '" value="' . ($j+1). '">';
					}
				}else{
					echo '<INPUT type="checkbox" name="LegalSel' .$j .'" value="' . ($j+1). '">';
				}
					
				echo "<INPUT type='hidden' name='NameLegal" . $j . "' value='" . $legalid. "'>";
				echo ucwords(strtolower($nombresuc));
			} else{
				if(!isset($_POST['DeSelectAllLegal'])){
					echo '<INPUT type="checkbox" name="LegalSel' .$j . '" checked value="' . ($j+1) . '">';
					echo '<INPUT type="hidden" name="NameLegal' . $j . '" value="' . $legalid .'">';
					echo ucwords(strtolower($nombresuc));
				}else{
					echo '<INPUT type="checkbox" name="LegalSel' . $j . '" value="' . ($j+1) . '">';
					echo '<INPUT type="hidden" name="NameLegal' . $j . '" value=' . $legalid . '>';
					echo ucwords(strtolower($nombresuc));
				}
					
			}
			$j=$j+1;
			echo '</td>';
			echo '</tr>';
		}//
		echo '<tr><td colspan=2><input type="hidden" name="TotalLegal" value="' . $j . '"></td></tr>';
		echo '</table>';
		echo '</td></tr>';

	}

	/*Fin de asignación de razón social*/
	
	// *********************************//
	// ******FIN DATOS DE SUCURSAL******//
	// *********************************//
	echo '</table></td><td valign="top"><table border="0">';
	echo '<tr><td colspan="2" style="text-align:center;">' . _ ( 'DATOS COMPRA' ) . '</td></tr>';
	// Show Sales Type drop down list
	$result = DB_query ( "SELECT typeabbrev, sales_type  FROM salestypes, sec_pricelist where salestypes.typeabbrev = sec_pricelist.pricelist and sec_pricelist.userid = '" . $_SESSION ['UserID'] . "'", $db );
	if (DB_num_rows ( $result ) == 0) {
		$DataError = 1;
		echo '<a href="SalesTypes.php?" target="_parent">Configurar Tipos Venta</a>';
		echo '<tr><td colspan=2>' . prnMsg ( _ ( 'No hay Tipos Venta/Listas de Precio definidos' ), 'error' ) . '</td></tr>';
	} else {
		echo '<tr><td>' . _ ( 'Tipo Venta/Precio de Lista' ) . ':</td>
                       <td><select tabindex=26 name="SalesType">';
		while ( $myrow = DB_fetch_array ( $result ) ) {
			$selected="";
			if(isset($_POST ['SalesType']) && $_POST ['SalesType'] == $myrow ['typeabbrev']){
				$selected="selected";
			}
			echo '<option value="' . $myrow ['typeabbrev'] . '" '.$selected.'>' . $myrow ['sales_type'] . '</option>';
		} // end while loop
		DB_data_seek ( $result, 0 );
		echo '</select></td></tr>';
	}
	// Show Customer Type drop down list
	if(!isset($_SESSION ['DebtorTypeCaption']))
	{
		$_SESSION ['DebtorTypeCaption']="";
	}
	$caption = $_SESSION ['DebtorTypeCaption'];
	if ($caption == "")
		$caption = "Tipo de Cliente";
	if ($_POST ['typeid'] == "" or ! isset ( $_POST ['typeid'] )) {
		echo 'tipo' . $_SESSION ['TypeCustomerDefault'];
		$_POST ['typeid'] = $_SESSION ['TypeCustomerDefault'];
	}
	$result = DB_query ( 'SELECT debtortype.typeid, typename 
						FROM debtortype inner join sec_debtorxuser on sec_debtorxuser.typeid=debtortype.typeid
						and sec_debtorxuser.userid="' . $_SESSION ['UserID'] . '"
						 Order by orden desc', $db );
	if (DB_num_rows ( $result ) == 0) {
		$DataError = 1;
		echo '<a href="SalesTypes.php?" target="_parent">Configurar ' . $caption . '</a>';
		echo '<tr><td colspan=2>' . prnMsg ( _ ( 'No hay Tipos Cliente/Precios de Lista definidos' ), 'error' ) . '</td></tr>';
	} else {
		if($permisotipocliente == 1){
			echo "<tr>";
		 		echo "<td>" . _($caption) . "";
		 			echo "<input type=hidden name=typeid value='".$_POST ['typeid']."'>";
		 		echo"</td>";
		 			$sqltpocl = "SELECT typename
		 				FROM debtortype
		 				WHERE typeid = '".$_POST ['typeid']."'";
		 			$restpocl = DB_query($sqltpocl, $db);
		 			$myrowtpocl = DB_fetch_array($restpocl);
		 			$nombretpcl = $myrowtpocl['typename'];
		 			echo "<td>".$nombretpcl."</td>";
		 		echo "</tr>";
			
		
		}else{
			echo '<tr><td>' . _ ( $caption ) . ':</td>
				<td><select tabindex=27 name="typeid">';
				while ( $myrow = DB_fetch_array ( $result ) ) {
					if ($_POST ['typeid'] == $myrow ['typeid']) {
						echo "<option selected VALUE='" . $myrow ['typeid'] . "'>" . $myrow ['typename'] . '</option>';
					} else {
						echo "<option VALUE='" . $myrow ['typeid'] . "'>" . $myrow ['typename'] . '</option>';
					}
				} // end while loop//
				DB_data_seek ( $result, 0 );
				echo '</select></td></tr>';
		}
		 
	}
	
	$DateString = Date ( $_SESSION ['DefaultDateFormat'] );
	echo '<tr><td>' . _ ( 'Cliente desde' ) . ' (' . $_SESSION ['DefaultDateFormat'] . '):</td><td><input tabindex=28 type="text" readonly alt="' . $_SESSION ['DefaultDateFormat'] . '" name="ClientSince" value="' . $DateString . '" size=12 maxlength=10></td></tr>';
	
	echo '<tr><td>' . _ ( 'Historial Crediticio' ) . ':</td>';
	/*
	 * if (Havepermission ( $_SESSION ['UserID'], 1515, $db )) {
	 * echo "<input type='hidden' name='HoldReason' value='" . $_POST ['HoldReason'] . "'>";
	 * echo '<td><select tabindex=33 name="HoldReason" disabled>';
	 * } else {
	 * echo '<td><select tabindex=33 name="HoldReason">';
	 * }
	 */
	echo '<td><select tabindex=33 name="HoldReason">';
	$result = DB_query ( 'SELECT reasoncode, reasondescription FROM holdreasons where reasoncode in (select reasoncode from sec_holdreasons where userid="' . $_SESSION ['UserID'] . '") order by orden desc', $db );
	if ($_POST ['HoldReason'] == "" or ! isset ( $_POST ['HoldReason'] )) {
		$_POST ['HoldReason'] = $_SESSION ['HistoryCustomerDefault'];
	}
	if (DB_num_rows ( $result ) == 0) {
		$DataError = 1;
		echo '<tr><td colspan=2>' . prnMsg ( _ ( 'No hay actualmente estatus de credito definidos - Ir al tab de configuracion del men� principial y y configurar uno' ), 'error' ) . '</td></tr>';
	} else {
		while ( $myrow = DB_fetch_array ( $result ) ) {
			if ($_POST ['HoldReason'] == $myrow ['reasoncode']) {
				echo '<option selected VALUE=' . $myrow ['reasoncode'] . '>' . $myrow ['reasondescription'];
			} else {
				echo '<option VALUE=' . $myrow ['reasoncode'] . '>' . $myrow ['reasondescription'];
			}
		} // end while loop//
		DB_data_seek ( $result, 0 );
		echo '</select></td></tr>';
	}




	$result = DB_query ( "SELECT terms, paymentterms.termsindicator,cashdiscount,daygrace
			 FROM paymentterms, sec_paymentterms WHERE paymentterms.termsindicator = sec_paymentterms.termsindicator and sec_paymentterms.userid='" . $_SESSION ['UserID'] . "'", $db );
	if (DB_num_rows ( $result ) == 0) {
		$DataError = 1;
		echo '<tr><td colspan=2>' . prnMsg ( _ ( 'No existen condiciones de pago definidas, utilice el menu de configuracion para darlos de alta' ), 'error' ) . '</td></tr>';
	} else {
		
		if (Havepermission ( $_SESSION ['UserID'], 1127, $db ) == 1) {
			$result = DB_query ( "SELECT terms, paymentterms.termsindicator,cashdiscount,daygrace
			 FROM paymentterms, sec_paymentterms WHERE paymentterms.termsindicator = sec_paymentterms.termsindicator and sec_paymentterms.userid='" . $_SESSION ['UserID'] . "'", $db );
			
			echo '<tr><td>' . _ ( 'Terminos de Pago' ) . ":</td>
		<td nowrap ><select name='PaymentTerms'>";
			
			while ( $myrow = DB_fetch_array ( $result ) ) {
				if ($_POST ['PaymentTerms'] == $myrow ['termsindicator']) {
					if (($_POST ['DescClienteProPago'] != $myrow ['cashdiscount']) or strlen ( $_POST ['DescClienteProPago'] ) == 0) {
						$_POST ['DescClienteProPago'] = $myrow ['cashdiscount'];
						// echo 'entraaaaaa';
					}
					if (! isset ( $_POST ['daygrace'] )) {
						$_POST ['daygrace'] = $myrow ['daygrace'];
					}
					echo "<option selected VALUE=" . $myrow ['termsindicator'] . '>' . $myrow ['terms'];
				} else {
					echo '<option VALUE=' . $myrow ['termsindicator'] . '>' . $myrow ['terms'];
				}
			} // end while loop
			DB_data_seek ( $result, 0 );
			echo '</select>';
			// echo 'desceuento:'.$_POST['PymtDiscount'].'-'.$_POST['PaymentTerms'];
			echo '<input type="submit" value="->" onclick="NovalidaDatosObligatorios();" name="btnTag"></td></tr>';
		} else {
			echo '<tr><td>' . _ ( 'Terminos de Pago' ) . ":</td>";
			if ($_POST ['PaymentTerms'] == "" or ! isset ( $_POST ['PaymentTerms'] )) {
				$result = DB_query ( "SELECT terms, paymentterms.termsindicator,cashdiscount,daygrace 
							  FROM paymentterms, sec_paymentterms 
							  WHERE paymentterms.termsindicator = sec_paymentterms.termsindicator 
								and sec_paymentterms.userid='admin'
								and paymentterms.terms like 'contado'
							  Order by terms", $db );
				$myrowtermpago = DB_fetch_array ( $result );
				echo "<td>" . $myrowtermpago ['terms'] . "</td>";
				echo "<input type='hidden' name='PaymentTerms' value='" . $myrowtermpago ['termsindicator'] . "'></td></tr>";
			} else {
				$result = DB_query ( "SELECT terms FROM paymentterms WHERE paymentterms.termsindicator ='" . $_POST ['PaymentTerms'] . "'", $db );
				$myrowtermpago = DB_fetch_array ( $result );
				echo "<td>" . $myrowtermpago ['terms'] . "</td>";
				echo "<input type='hidden' name='PaymentTerms' value='" . $_POST ['PaymentTerms'] . "'></td></tr>";
			}
		}
	}
	
	$result = DB_query ( 'SELECT currency, currabrev FROM currencies', $db );
	if (DB_num_rows ( $result ) == 0) {
		$DataError = 1;
		echo '<tr><td colspan=2>' . prnMsg ( _ ( 'Actualmente no hay monedas definidas - Ir al tab de configuracion del menu principal y configurar uno' ), 'error' ) . '</td></tr>';
	} else {
		if (! isset ( $_POST ['CurrCode'] )) {
			$CurrResult = DB_query ( 'SELECT currencydefault FROM companies WHERE coycode=1', $db );
			$myrow = DB_fetch_row ( $CurrResult );
			$_POST ['CurrCode'] = $myrow [0];
		}
		echo '<tr><td>' . _ ( 'Moneda del Cliente' ) . ':</td><td><select tabindex=34 name="CurrCode">';
		while ( $myrow = DB_fetch_array ( $result ) ) {
			if ($_POST ['CurrCode'] == $myrow ['currabrev']) {
				echo '<option selected value=' . $myrow ['currabrev'] . '>' . $myrow ['currency'] . '</option>';
			} else {
				echo '<option value=' . $myrow ['currabrev'] . '>' . $myrow ['currency'] . '</option>';
			}
		} // end while loop
		echo '</select></td></tr>';
		
		$funcion = 166;
		$permiso = Havepermission ( $_SESSION ['UserID'], $funcion, $db );
		
		if ($permiso == 0) {
			// echo '<tr style=display:none;><td colspan=2><hr color="Darkblue" width=80%></td></tr>';
			echo '<tr style=display:none;><td>' . _ ( 'L&iacute;mite de Cr&eacute;dito General' ) . ':</td>
						<td>
							<input tabindex=32 type="text" class="number" name="CreditLimit" value="' . $_SESSION ['DefaultCreditLimit'] . '" size=16 maxlength=14>
							<input tabindex=33 type="hidden" class="number" name="CreditLimit2" value="' . $_SESSION ['DefaultCreditLimit'] . '" size=16 maxlength=14>
						</td>
				  </tr>';
		} else {
			// echo '<tr><td colspan=2><hr color="Darkblue" width=80%></td></tr>';
			
			echo '<tr>	<td>' . _ ( 'L&iacute;mite de Cr&eacute;dito General' ) . ':</td>
						<td><input tabindex=32 type="text" class="number" name="CreditLimit"   value="' . $CreditLimit . '"  size=16 maxlength=14>
						   <input tabindex=33 type="hidden" class="number" name="CreditLimit2" value="' . $CreditLimit2 . '" size=16 maxlength=14>
						</td>
				   </tr>';
		}
		// PERMISO PARA PONER A UN CLIENTE EN LISTA NEGRA p
		$permiso1 = Havepermission ( $_SESSION ['UserID'], 758, $db );
		// PERMISO PARA NO PONER A UN CLIENTE EN LISTA NEGRA
		$permiso2 = Havepermission ( $_SESSION ['UserID'], 759, $db );
		if ($permiso1 == 1) {
			echo '<tr><td>' . _ ( 'Lista Negra' ) . ':</td><td><select name="lista">';
			
			echo '<option value=0>' . 'NO' . '</option>';
			
			if ($permiso1 == 1) {
				echo '<option value=1>' . 'SI' . '</option>';
			}
			echo '</select></td></tr>';
		} else {
			echo "<tr><td><input type='hidden' name='lista' value=0></td></tr>";
		}
		// $SQL =   DB_query ( "SELECT * FROM debtorsmaster where debtorno = ".$DebtorNo ,$db );
		// $row = DB_fetch_array ( $SQL );
		// if ($_POST ['rif'] == 'on' or  $row['RIF'] == 1) {
		// 	echo '<tr><td>' . _ ( 'R&eacute;gimen fiscal RIF' ) . ':</td> <td><input type="checkbox" id="rif" name="rif" checked /> </td></tr>';
		// }else{
		// 	echo '<tr><td>' . _ ( 'R&eacute;gimen fiscal RIF' ) . ':</td> <td><input type="checkbox" id="rif" name="rif" /> </td></tr>';
		// }
		echo '<tr><td>' . _ ( 'R&eacute;gimen fiscal RIF' ) . ':</td> <td><input type="checkbox" id="rif" name="rif" /> </td></tr>';

//AGREGACIÓN DE DATOS BANCARIOS 
			$identificador1=uniqid('id_',true);
		echo "<tr><td style='text-align:center;padding-top: 25px;'>" . _ ( 'DATOS BANCARIOS' ) . "</td> 
		<td style='padding-top: 25px;'><input type='button' name='btnBank' id='btnBank' value='Agregar'>
		<input type='text' name='identificador' id='identificador' value='".$identificador1."' style='display:none'></tr>";
		echo '<tr><td colspan="2"><div id="dvBanks" style="display:none; padding-top: 15px;">
		<div><label style="min-width: 100px;display: inline-block;font-weight: bold;">Nombre corto: </label>
                <input type="text" name="txtBank_name" id="txtBank_name" value=""></div>';

			
	//SELECCIÓN DEL BANCO
			$infobaks= array();
			$SQL = "SELECT  bank_id, bank_shortdescription, taxid
			FROM banks
			WHERE bank_active = 1  AND taxid IS NOT NULL
			ORDER BY bank_shortdescription ASC";
			$TransResult = DB_query ( $SQL, $db );

			while ( $myrow = DB_fetch_array ( $TransResult ) ) {
			array_push($infobaks, array( 'value' => $myrow ['bank_shortdescription'], 'bank_id' => $myrow ['bank_id'], 'Nombre' => utf8_encode($myrow ['bank_shortdescription']), 'Rfc' => utf8_encode($myrow ['taxid']) ));
			}
			echo '<br><div><label style="min-width: 100px;display: inline-block;font-weight: bold;">Nombre Banco: </label>
			                <select name="txtBank_id" id="txtBank_id" onchange="fnBanks(this,\'rfcbank\');">';
			echo "<option value='0' >Selecciona...</option>";
			foreach ($infobaks as $value) {
				$vrSelect ="";
				if(!empty($_POST['BanksB']) && $_POST['BanksB']==$value['bank_id']){
					$vrSelect ="selected";
				}
				echo '<option value="'.$value['bank_id'].'" title="'.$value['Rfc'].'" '.$vrSelect.'>'.$value['Nombre'].'</option>';
			}
			echo "</select></div>";
	//LLENADO DEL RFC SEGÚN EL BANCO
			echo '<br><div><label style="min-width: 100px;display: inline-block; font-weight: bold;">RFC Banco: </label>
			                <input type="text" name="rfcbank" id="rfcbank" value="' . $_POST['rfcBanks'] . '" maxlength="12" readonly="readonly"></div>';
	//TERMINO DE PAGO
			$SQL_TP = 'SELECT * 
				FROM paymentmethods 
				WHERE receiptuse = 1
				AND active = 1';
			$result_TP = DB_query ( $SQL_TP, $db );
			echo'<br><div><label style="min-width: 100px;display: inline-block; font-weight: bold;">M&eacute;todo de Pago: </label>
			             <select name="txtTermPago" id="txtTermPago"onchange="fnmtdpagos()">';
			echo "<option value='0' >Selecciona...</option>";
			while($myrow = DB_fetch_array ( $result_TP )){
					echo '<option value="' . $myrow ['paymentname'] . '" data-cdsat="'.$myrow ['codesat'].'">' . $myrow ['paymentname'] . '</option>';
			}
			echo'</select></div>';		
	//NÚMERO DE CUENTA
			echo'<br><div><label style="min-width: 100px;display: inline-block; font-weight: bold;">No. cuenta: </label>
			                <input type="text" name="accountbank" id="accountbank" maxlength="18" onchange="Validar1()"></div>';
			echo "<div>
			                
			                     <label class='clLeyenda' id='dvnoOrd' style='padding: 2px 5px; font-size: 11px; color: #ce2a1e;'><br></div>";
			echo "<div>
			                
			             <label class='mensaje1' id='mensaje1' style='padding: 2px 5px; color: #ce2a1e;'><br></div>";
	//BOTON 
			echo '<br><div><input type="button" name="btnsavebank" id="btnsavebank" value="Guardar"></div>';
			echo '<tr><td colspan="2" style="text-align:center;"><div id="dvresbank">Cargando informaci&oacute;n...</div></td></tr>';
	//TABLA DE DATOS BANCARIOS 
			echo '<tr><td colspan="2" style="text-align:center; display:none;" class="clInfbank">'. _ ('INFORMACI&Oacute;N BANCARIA'). '</td></tr>';
			echo '<tr><td colspan="2" style="text-align:center; display:none;" class="clInfbank">
			<table id="tbbanks" style="width:100%;">
			<thead>
			<tr><th>Nombre corto</th>
			<th>Banco</th>
			<th>RFC</th>
			<th>M&eacute;todo de Pago</th>
			<th>No. cuenta</th>
			</tr></thead><tbody></tbody></table></td></tr>';
/////////////////////
		echo '<tr><td colspan=2><hr color="Darkblue" width=80%></td></tr>';
		
		echo '<tr style=display:none;><td>' . _ ( 'Tax Reference' ) . ":</td>
		<td><input tabindex=34 type='Text' name='TaxRef' size=22 maxlength=20></td></tr>";
		
		$qry = "SELECT * FROM typeaddenda WHERE active=1;";
		$rs = DB_query ( $qry, $db );
		echo '<tr><td >' . _ ( 'Tipo Addenda (F.E.)' ) . ':</td>
		  <td><select  name="typeaddenda" id="typeaddenda">';
		echo '<option selected VALUE=0>' . _ ( 'Ninguna' ) . '</option>';
		while ( $myrows = DB_fetch_array ( $rs ) ) {
			if ($myrows ['id_addenda'] == $_POST ['typeaddenda']) {
				echo '<option selected  value="' . $myrows ['id_addenda'] . '">' . $myrows ['nameaddenda'] . '</option>';
			} else {
				echo '<option  value="' . $myrows ['id_addenda'] . '">' . $myrows ['nameaddenda'] . '</option>';
			}
		} // end while loop
		
		echo '</select></td></tr>';
        
        /*Agregar el numero de proveedor*/
		echo '<tr><td>' . _ ( 'No. Proveedor' ) . ': </td><td><input type="text" size=17  name="noproveedor" id="noproveedor" value="'.$_POST['noproveedor'].'"></td></tr>';
		/***/   
                
                /****/
                $qry = "SELECT  * FROM typecomplement";
		$rs = DB_query ( $qry, $db );
		echo '<tr><td >' . _ ( 'Tipo de Complementos' ) . ':</td>
		  <td><select  name="typecomplement" id="typecomplement">';
		echo '<option selected VALUE=0>' . _ ( 'Ninguna' ) . '</option>';
		while ( $myrows = DB_fetch_array ( $rs ) ) {
			if ($myrows ['id'] == $_POST ['typecomplement']) {
				echo '<option selected  value="' . $myrows ['id'] . '">' . $myrows ['namecomplement'] . '</option>';
			} else {
				echo '<option  value="' . $myrows ['id'] . '">' . $myrows ['namecomplement'] . '</option>';
			}
		} // end while loop
		
		echo '</select></td></tr>';
                /****/
                
                
                
		
		$payname = "No identificado";
		$nocta = "";
		
		if ($_POST ['paymentname'])
			$payname = $_POST ['paymentname'];
		
		if ($_POST ['nocuenta'])
			$nocta = $_POST ['nocuenta'];
		
		$qry = "Select * from paymentmethods";
		$rs = DB_query ( $qry, $db );
		echo '<tr><td >' . _ ( 'Condiciones de Pago' ) . ':</td>
		  <td><select  name="paymentname" id="paymentname">';
		while ( $myrows = DB_fetch_array ( $rs ) ) {
			if ($myrows ['paymentname'] == $payname) {
				echo '<option selected  value="' . $myrows ['paymentname'] . '">' . $myrows ['paymentname'] . '</option>';
			} else {
				echo '<option  value="' . $myrows ['paymentname'] . '">' . $myrows ['paymentname'] . '</option>';
			}
		} // end while loop
		
		echo '</select></td></tr>';
		
		echo '<tr><td >' . _ ( 'No de Cuenta' ) . ':</td>
		  <td><input type="text" size=17  name="nocuenta" id="nocuenta" value="' . $nocta . '"></td></tr>';
		
		// tipos de retenciones
		$qry = "SELECT sec_taxes.idtax,
				       sec_taxes.nametax,
				       ifnull(debtortaxes.idtax,0) AS CHECKED,
				       percent
				FROM sec_taxes
				LEFT JOIN debtortaxes ON sec_taxes.idtax = debtortaxes.idtax
				AND debtorno = '$DebtorNo'";
		$rs = DB_query ( $qry, $db );
		echo '<tr valign="top"><td >' . _ ( 'Tipo de Impuesto' ) . ':</td>
	  		<td> ';
		$listchecked = "";
		while ( $myrows = DB_fetch_array ( $rs ) ) {
			if ($myrows ['CHECKED'] != 0) {
				$listchecked .= $myrows ['idtax'] . "|";
				echo '<input onclick="updateList(this,' . $myrows ['idtax'] . ');" checked type="checkbox" value="' . $myrows ['idtax'] . '"> &nbsp;' . $myrows ['nametax'] . '&nbsp;&nbsp;-&nbsp;&nbsp;' . number_format ( $myrows ['percent'], 2 ) . '%<br>';
			} else {
				echo '<input onclick="updateList(this,' . $myrows ['idtax'] . ');" type="checkbox" value="' . $myrows ['idtax'] . '"> &nbsp;' . $myrows ['nametax'] . '&nbsp;&nbsp;-&nbsp;&nbsp;' . number_format ( $myrows ['percent'], 2 ) . '%<br>';
			}
		} // end while loop
		
		echo '</td></tr>';
		echo "<script>document.getElementById('taxclient').value = '" . $listchecked . "';</script>";
		echo '<tr><td colspan=2><hr color="Darkblue"></td></tr>';

		// complementos factura
		/*$qry = "SELECT cfdicomplement.id,
				       cfdicomplement.complement,
				       IFNULL(debtorcomplement.idcomplement,0) AS CHECKED
				FROM cfdicomplement
				LEFT JOIN debtorcomplement ON debtorcomplement.idcomplement = cfdicomplement.id
				AND debtorcomplement.debtorno = '$DebtorNo'
				AND cfdicomplement.active = 1";*/
		
		// complementos factura MHP
		$sqlcom="SELECT cfdicomplement.id,
				       cfdicomplement.complement,
				       IFNULL(valuecomplement.fieldid,0) AS CHECKED
				FROM cfdicomplement
				LEFT JOIN valuecomplement ON valuecomplement.fieldid = cfdicomplement.id
				AND valuecomplement.debtorno='$DebtorNo'
				WHERE cfdicomplement.active = 1";


		$rs = DB_query ( $qry, $db );
		echo '<tr valign="top"><td >' . _ ( 'Complementos CFDI' ) . ':</td>
		  		<td> ';
		$listchecked = "";
		$vrInfcom="";
		while ( $myrows = DB_fetch_array ( $rs ) ) {

			if ($myrows ['CHECKED'] != 0) {
				$listchecked .= $myrows ['id'] . "|";
				echo '<input onclick="updateListComplement(this,' . $myrows ['id'] . ');" checked type="checkbox" value="' . $myrows ['id'] . '"> &nbsp;' . $myrows ['complement'] . '<br>';
			} else {
				echo '<input onclick="updateListComplement(this,' . $myrows ['id'] . ');" type="checkbox" value="' . $myrows ['id'] . '"> &nbsp;' . $myrows ['complement'] . '<br>';
			}
		} // end while loop
		
		echo '</td></tr>';

		echo "<script>document.getElementById('complementclient').value = '" . $listchecked . "';</script>";
		
		// Inicio de Mostreo de descuentos
		
		// echo '<tr><th colspan=2><b>DESCUENTOS</b></th></tr>';
		echo '<tr><td colspan=2><hr color="Darkblue" width=80%></td></tr>';
		
		if (Havepermission ( $_SESSION ['UserID'], 727, $db ) == 1) {
			echo '<tr><td valign="top">' . ('Descuento Comercial(1)') . '</td>';
			echo '<td><input type="text" class="number" size=5 maxlength=4 name="DescClienteComercial" value="' . $_POST ['DescClienteComercial'] . '"></td></tr>';
		}
		
		echo '<tr><td>' . _ ( 'Descuento (2)' ) . ':</td>
					<td><input tabindex=29 type="textbox" class="number" name="Discount" size=5 maxlength=4  value="' . $Discount . '"></td></tr>';
		echo '<tr><td>' . _ ( 'Descuento (3)' ) . ':</td>
	 				<td><input type="text" class="number" size=5 maxlength=4 name="descclienteop" value="' . $_POST ['descclienteop'] . '" ></td></tr>';
		if (Havepermission ( $_SESSION ['UserID'], 728, $db ) == 1) {
			echo '<tr><td valign="top">' . ('Descuento Pronto Pago') . '</td>';
			echo '<td><input type="text" class="number" size=5 maxlength=4 name="DescClienteProPago" value="' . $_POST ['DescClienteProPago'] . '"></td></tr>';
		}
		echo '<tr style=display:none;><td>' . _ ( 'Codigo de Descuento' ) . ':</td>
		<td><input tabindex=30 type="text" class="number" name="DiscountCode" size=3 maxlength=2></td></tr>';
		echo '<tr style=display:none;><td>' . _ ( 'Payment Discount Percent' ) . ':</td>
		<td><input tabindex=31 type="textbox" class ="number" name="PymtDiscount" value=0 size=5 maxlength=4></td></tr>';
		// Fin de Mostreo de descuentos
		echo '<tr><td colspan=2><hr color="Darkblue" width=80%></td></tr>';
		/* Agregacion de Comentarios para cliente */
		echo '<td valign="top">' . _ ( 'Comentarios:' ) . '</td>';
		echo '<td><textarea name=coments cols=30 rows=6 >' . $_POST ['coments'] . '</textarea></td>';
		
		/* Dias de Revision */
		echo '<tr>';
		echo '<td>' . _ ( 'Dias de Revision' ) . ':</td>';
		echo '<td><select name="DiasRevicion">';
		$DiasSemana = array (
				1 => "Lunes",
				2 => "Martes",
				3 => "Miercoles",
				4 => "Jueves",
				5 => "Viernes",
				6 => "Sabado" 
		);
		for($i = 1; $i < 7; $i ++) {
			if ($i == $_POST ['DiasRevicion']) {
				echo '<option selected value="' . $i . '">' . $DiasSemana [$i] . '</option>';
			} else {
				echo '<option value="' . $i . '">' . $DiasSemana [$i] . '</option>';
			}
		}
		echo '</td></tr>';
		/* Dias de Pago */
		echo '<tr>';
		echo '<td>' . _ ( 'Dias de Pago' ) . ':</td>';
		echo '<td><select name="DiasPago">';
		for($i = 1; $i < 7; $i ++) {
			if ($i == $_POST ['DiasPago']) {
				echo '<option selected value="' . $i . '">' . $DiasSemana [$i] . '</option>';
			} else {
				echo '<option value="' . $i . '">' . $DiasSemana [$i] . '</option>';
			}
		}
		echo '</td></tr>';
		
		/* Razon de compra */
		echo "<tr>";
		echo '<td valign="top">' . _ ( 'Razon de compra:' ) . '</td>';
		echo '<td><textarea name=razoncompra cols=30 rows=6 >' . $_POST ['razoncompra'] . '</textarea></td>';
		
		echo "</tr>";
		
		/*
		 * Medio de contacto echo "<tr>"; echo '<td valign="top">'. _('Medio de contacto:') .'</td>'; //echo '<td><textarea name=mediocontacto cols=30 rows=6 >'. $_POST['mediocontacto'] .'</textarea></td>'; $result=DB_query('SELECT id, title FROM contactmeans WHERE active=1 ',$db); echo "<td><select name='mediocontacto'>"; while ($myrow = DB_fetch_array($result)) { if ($_POST['mediocontacto']==$myrow['id']){ echo '<option selected value='. $myrow['id'] . '>' . $myrow['title']; } else { echo '<option value='. $myrow['id'] . '>' . $myrow['title']; } } //end while loop echo "</tr>";
		 */
		/* cumplea�os */
		echo '<tr>';
		echo '<td valign="top">' . _ ( 'Cumplea&ntilde;os/aniversario' ) . ' (' . $_SESSION ['DefaultDateFormat'] . '):</td>';
		echo '<td><select Name="FromDia">';
		$sql = "SELECT * FROM cat_Days";
		$dias = DB_query ( $sql, $db );
		while ( $myrowdia = DB_fetch_array ( $dias ) ) {
			if ($myrowdia ['DiaId'] == $FromDia) {
				echo '<option VALUE="' . $myrowdia ['DiaId'] . '  " selected>' . $myrowdia ['Dia'] . '</option>';
			} else {
				echo '<option VALUE="' . $myrowdia ['DiaId'] . '">' . $myrowdia ['Dia'] . '</option>';
			}
		}
		echo '</select>';
		echo '&nbsp;<select Name="FromMes">';
		$sql = "SELECT * FROM cat_Months";
		$Meses = DB_query ( $sql, $db );
		while ( $myrowMes = DB_fetch_array ( $Meses ) ) {
			if ($myrowMes ['u_mes'] == $FromMes) {
				echo '<option VALUE="' . $myrowMes ['u_mes'] . '  " selected>' . $myrowMes ['mes'] . '</option>';
			} else {
				echo '<option VALUE="' . $myrowMes ['u_mes'] . '" >' . $myrowMes ['mes'] . '</option>';
			}
		}
		
		echo '</select>';
		echo '&nbsp;<input name="FromYear" type="text" size="4" value=' . $FromYear . '></td>';
		
		echo '</tr>';
		/*
		 * echo "<tr>"; c //$DateString2 = (isset($_POST['fechanacimiento']))?Date($_SESSION['DefaultDateFormat']):; echo '<td><input type="text" name="fechanacimiento" value="'.$_POST['fechanacimiento'].'" size="17" class=date alt="'.$_SESSION['DefaultDateFormat'].'"></td>'; //echo '<td><input type="text" name="fechanacimiento" value="'.$_POST['fechanacimiento'].'" size="17"></td>'; echo "</tr>";
		 */
		
		/* Pagina de internet */
		echo "<tr>";
		echo '<td valign="top">' . _ ( 'Pagina personal:' ) . '</td>';
		echo '<td><textarea name=pagpersonal cols=30 rows=6 >' . $_POST ['pagpersonal'] . '</textarea></td>';
		echo "</tr>";
		echo '<tr><td valign="top">' . ('codigo proveedor asignado por el cliente') . '</td>';
		echo '<td><input type="text" name="NumeAsigCliente" value="' . $_POST ['NumeAsigCliente'] . '"></td></tr>';
		/*
		 * if(Havepermission($_SESSION['UserID'], 727, $db)== 1){ echo '<tr><td valign="top">'.('Descuento Comercial').'</td>'; echo '<td><input type="text" name="DescClienteComercial" value="'.$_POST['DescClienteComercial'].'"></td></tr>'; } if(Havepermission($_SESSION['UserID'], 728, $db)== 1){ echo '<tr><td valign="top">'.('Descuento Pronto Pago').'</td>'; echo '<td><input type="text" name="DescClienteProPago" value="'.$_POST['DescClienteProPago'].'"></td></tr>'; }
		 */
		DB_data_seek ( $result, 0 );
	}
	
	/* added line 8/23/2007 by Morris Kelly to set po line parameter Y/N */
	echo '<tr style=display:none;><td>' . _ ( 'Customer PO Line on SO' ) . ":</td><td><select tabindex=35 name='CustomerPOLine'>";
	echo '<option selected value=0>' . _ ( 'No' );
	echo '<option value=1>' . _ ( 'Yes' );
	echo '</select></td></tr>';
	
	echo '<tr style=display:none;><td>' . _ ( 'Invoice Addressing' ) . ":</td><td><select tabindex=36 name='AddrInvBranch'>";
	echo '<option selected VALUE=0>' . _ ( 'Address to HO' );
	echo '<option VALUE=1>' . _ ( 'Address to Branch' );
	echo '</select></td></tr>';
	
	// incluir campos para envio de recordatorio de estado de cuenta al cliente
	echo '<tr><th colspan=2><b>ENVIO DE RECORDATORIO</b></th></tr>';
	echo '<tr><td colspan=2><input title="Habilitar o no el envio de reordatorio al cliente" type="checkbox" name="chkactivarecordatorio" id="chkactivarecordatorio">&nbsp;Habilitar envio</td></tr>';
	echo '<tr><td colspan=2><hr></td></tr>';
	echo '<tr>
			  <td><input type="checkbox" name="chkdiasvence" id="chkdiasvence" onclick="Habilitar(this,\'diasvence\');" >&nbsp;Dias vencimiento:</td>
			  <td><input type="text" size="5" name="diasvence" id="diasvence" disabled></td>			  
		  </tr>';
	echo '<tr valign="top">
			  <td><input type="checkbox" name="chkmensaje" id="chkmensaje" onclick="Habilitar(this,\'mensaje\');">&nbsp;Mensaje:</td>
			  <td><textarea name="mensaje" id="mensaje" cols="30" rows="3" disabled></textarea></td>			  
		  </tr>';
	echo '<tr>
			  <td colspan="2"><input type="checkbox" name="chkmora" id="chkmora" onclick="if (this.checked) document.getElementById(\'chkactivarecordatorio\').checked=true;">&nbsp;Inclu&iacute;r inter&eacute;s moratorio</td>
		  </tr>';
	echo '<tr>
			  <td><input type="checkbox" name="chkrepiteenvio" id="chkrepiteenvio" onclick="Habilitar(this,\'repiteenvio\');">&nbsp;Repite envio:</td>
				<td>
					<input type="text" size="5" name="repiteenvio" id="repiteenvio" disabled>&nbsp;d&iacute;as
				</td>			  
		  </tr>';
	echo '<tr><td colspan=2><hr color="Darkblue"></td></tr>';
	
	echo '</table></td></tr>';
	echo '<tr><td colspan="2">
			<input tabindex=37 type="checkbox" name="uniquebranch" value="1" checked> ' . _ ( 'Sucursal Unica' ) . '
			<input type="hidden" name="identifier" value="' . $identifier . '">		
		</td></tr>';
	// fin recordatorio
	
	echo '</table>';
	if ($DataError == 0) {
		echo "<table border=0 width=40% align=center><tr><td width=50% style=text-align:center;><input tabindex=32 type='submit' onclick='validaDatosObligatorios1();' name='submit' value='" . _ ( 'Agregar Nuevo Cliente' ) . "'></td>";
		echo "<td style=text-align:center;><input tabindex=38 type='reset' VALUE='" . _ ( 'Limpiar Campos' ) . "'></div></td></tr></table>";
		// echo "<div class='centre'>";
	}
	echo '</form>';
} else {
	// DebtorNo exists - either passed when calling the form or from the form itself
	$coments = $_POST ['coments'];
	echo "<form name='FDatos' method='post' action='" . $_SERVER ['PHP_SELF'] . "?" . SID . "'>";
	echo "<input type='hidden' name='taxclient' id='taxclient' value=''>";
	echo "<input type='hidden' name='complementclient' id='complementclient' value=''>";
	echo '<input type="hidden" name="SelectedVehicle" value="' . $SelectedVehicle . '">';
	echo "<input type='hidden' name='actualizar' value='0'>";
	echo "<input type='hidden' name='urlfromorders' value='" . $_GET ['urlfromorders'] . "'>";
	echo '<table border=2 cellspacing=4><tr><td valign=top><table>';
	echo '<tr><td colspan="2" style="text-align:center;">' . _ ( 'DATOS PERSONALES' ) . '</td></tr>';
	echo '<tr><td colspan="2" style="text-align:center;">' . _ ( 'Los datos con * son obligatorios' ) . '</td></tr>';
	if (! isset ( $_POST ['New'] )) {
		$sql = "SELECT debtorno,
				       name1,
				       name2,
				       name3,
				       nameextra,
				       address1,
				       address2,
				       address3,
				       address4,
				       address5,
				       address6,
				       currcode,
				       salestype,
				       clientsince,
				       holdreason,
				       paymentterms,
				       discount,
				       discountcode,
				       pymtdiscount,
				       creditlimit,
				       invaddrbranch,
				       taxref,
				       customerpoline,
				       typeid,
				       coments,
				       blacklist ,
				       razoncompra ,
				       mediocontacto ,
				       fechanacimiento ,
				       pagpersonal,
				       NumRegIdTrib,
					   	RIF
                                   --    ,typecomplement
				FROM debtorsmaster
				WHERE debtorno = '" . $DebtorNo . "'";
		
		$ErrMsg = _ ( 'No se obtuvieron los detalles del cliente, por que' );
		if($codificacionJibe==1){
			DB_query("SET NAMES 'utf8';",$db);
		}
		$result = DB_query ( $sql, $db, $ErrMsg );
		$myrow = DB_fetch_array ( $result );
		
		$sql = "SELECT * FROM custbranch
			WHERE debtorno = '" . $DebtorNo . "' and branchcode = '" . $DebtorNo . "'";
		$ErrMsg = _ ( 'No se obtuvieron los detalles de la sucursal del cliente, por que' );
		$result2 = DB_query ( $sql, $db, $ErrMsg );
		$myrow2 = DB_fetch_array ( $result2 );
		/*
		 * if $AutoDebtorNo in config.php has not been set or if it has been set to a number less than one, then display the DebtorNo
		 */
		$sqlNumProveedor = "SELECT `idnumprovider`,`idcustbranch`,`idtypeaddenda`,`numprovider` FROM custbranchnumprovider
			WHERE idcustbranch = '" . $DebtorNo . "'";
		$rslNumProveedor = DB_query ( $sqlNumProveedor, $db, $ErrMsg );
		$myrowNumProveedor = DB_fetch_array ( $rslNumProveedor );
		$_POST ['noproveedor'] = $myrowNumProveedor['numprovider'];
		
		$resultR = DB_query ( "SELECT *
					FROM limitesxdepartamento,debtorsmaster,departments 
					WHERE u_department >1
					AND limitesxdepartamento.idcliente=debtorsmaster.debtorno
					AND limitesxdepartamento.idcliente='" . $DebtorNo . "'
					AND limitesxdepartamento.iddepartment = departments.u_department
					ORDER BY limitesxdepartamento.iddepartment", $db );
		$resultT = DB_query ( "SELECT *
					FROM terminospagoxdepartamento,debtorsmaster,departments 
					WHERE u_department >1
					AND terminospagoxdepartamento.idcliente=debtorsmaster.debtorno
					AND terminospagoxdepartamento.idcliente='" . $DebtorNo . "'
					AND terminospagoxdepartamento.iddepartment = departments.u_department
					ORDER BY terminospagoxdepartamento.iddepartment", $db );
		
		if ($_SESSION ['AutoDebtorNo'] == 0) {
			echo '<tr><td>' . _ ( 'Codigo de cliente' ) . ":</td>
				<td>" . $DebtorNo . "</td></tr>";
		}
		if (! isset ( $_POST ['CustName1'] )) {
	
			/*if ($codificacionJibe == 1) {
				$_POST ['CustName1'] = utf8_encode($myrow ['name1']);
				$_POST ['CustName2'] = utf8_encode($myrow ['name2']);
				$_POST ['CustName3'] = utf8_encode($myrow ['name3']);
				$_POST ['CommercialName'] = utf8_encode($myrow ['nameextra']);
				$_POST ['Address1'] = utf8_encode($myrow ['address1']);
				$_POST ['Address2'] = utf8_encode($myrow ['address2']);
				$_POST ['Address3'] = utf8_encode($myrow ['address3']);
				$_POST ['Address4'] = utf8_encode($myrow ['address4']);
				$_POST ['Address5'] = utf8_encode($myrow ['address5']);
				$_POST ['Address6'] = utf8_encode($myrow ['address6']);
			}else{*/
				$_POST ['CustName1'] = $myrow ['name1'];
				$_POST ['CustName2'] = $myrow ['name2'];
				$_POST ['CustName3'] = $myrow ['name3'];
				$_POST ['CommercialName'] = $myrow ['nameextra'];
				$_POST ['Address1'] = $myrow ['address1'];
				$_POST ['Address2'] = $myrow ['address2'];
				$_POST ['Address3'] = $myrow ['address3'];
				$_POST ['Address4'] = $myrow ['address4'];
				$_POST ['Address5'] = $myrow ['address5'];
				$_POST ['Address6'] = $myrow ['address6'];
			//}
			
			$_POST ['SalesType'] = $myrow ['salestype'];
			$_POST ['CurrCode'] = $myrow ['currcode'];
			$_POST ['coments'] = $myrow ['coments'];
			$_POST ['lista'] = $myrow ['blacklist'];
			$_POST ['ClientSince'] = ConvertSQLDate ( $myrow ['clientsince'] );
			$_POST ['HoldReason'] = $myrow ['holdreason'];
			$_POST ['PaymentTerms'] = $myrow ['paymentterms'];
			$_POST ['Discount'] = $myrow ['discount'] * 100; // Sherifoz 21.6.03 convert to displayable percentage
			$_POST ['DiscountCode'] = $myrow ['discountcode'];
			$_POST ['PymtDiscount'] = $myrow ['pymtdiscount'] * 100; // Sherifoz 21.6.03 convert to displayable percentage
			
			$_POST ['CreditLimit'] = $myrow ['creditlimit'];
			
			$_POST ['InvAddrBranch'] = $myrow ['invaddrbranch'];
			$_POST ['TaxRef'] = $myrow ['taxref'];
			$_POST ['CustomerPOLine'] = $myrow ['customerpoline'];
			$_POST ['typeid'] = $myrow ['typeid'];
			$_POST ['custdata1'] = $myrow ['custdata1'];
			$_POST ['custdata2'] = $myrow ['custdata2'];
			$_POST ['custdata3'] = $myrow ['custdata3'];
			$_POST ['custdata4'] = $myrow ['custdata4'];
			$_POST ['custdata5'] = $myrow ['custdata5'];
			$_POST ['custdata6'] = $myrow ['custdata6'];
			
			$_POST ['PhoneNo'] = $myrow2 ['phoneno'];
			$_POST ['MovilNo'] = $myrow2 ['movilno'];
			$_POST ['NextelNo'] = $myrow2 ['nextelno'];
			$_POST ['FaxNo'] = $myrow2 ['faxno'];
			$_POST ['Email'] = $myrow2 ['email'];
			
			$_POST ['custdata1'] = $myrow2 ['custdata1'];
			$_POST ['custdata2'] = $myrow2 ['custdata2'];
			$_POST ['custdata3'] = $myrow2 ['custdata3'];
			$_POST ['custdata4'] = $myrow2 ['custdata4'];
			$_POST ['custdata5'] = $myrow2 ['custdata5'];
			$_POST ['custdata6'] = $myrow2 ['custdata6'];
			
			$_POST ['taxid'] = $myrow2 ['taxid'];
			$_POST ['Salesman'] = $myrow2 ['salesman'];
			
			$_POST ['Area'] = $myrow2 ['area'];
			$_POST ['DefaultLocation'] = $myrow2 ['DefaultLocation'];
			$_POST ['TaxGroup'] = $myrow2 ['taxgroupid'];
			
			$_POST ['BrPostAddr1'] = $myrow2 ['brpostaddr1'];
			$_POST ['BrPostAddr2'] = $myrow2 ['brpostaddr2'];
			$_POST ['brnumext'] = $myrow2 ['brnumext'];
			$_POST ['brnumint'] = $myrow2 ['brnumint'];
			$_POST ['paymentname'] = $myrow2 ['paymentname'];
			$_POST ['nocuenta'] = $myrow2 ['nocuenta'];
			$_POST ['giro'] = $myrow2 ['lineofbusiness'];
			$_POST ['welcomeemail'] = $myrow2 ['welcomemail'];
			$_POST ['NumeAsigCliente'] = $myrow2 ['NumeAsigCliente'];
			
			$_POST ['razoncompra'] = $myrow ['razoncompra'];
			$_POST ['mediocontacto'] = $myrow ['mediocontacto'];

			$_POST ['NumRegIdTrib'] = $myrow ['NumRegIdTrib'];
			$_POST ['rif'] = $myrow ['RFI'];
			
			// $_POST['fechanacimiento'] = $myrow['fechanacimiento'];
			// ---------------------------------------------------------------------
			list ( $FromYear, $FromMes, $FromDia ) = explode ( "-", $myrow ['fechanacimiento'] );
			$_POST ['FromYear'] = $FromYear;
			$_POST ['FromMes'] = $FromMes;
			$_POST ['FromDia'] = $FromDia;
			$fechaini = rtrim ( $FromYear ) . '-' . rtrim ( $FromMes ) . '-' . rtrim ( $FromDia );
			$fechainic = mktime ( 0, 0, 0, rtrim ( $FromMes ), rtrim ( $FromDia ), rtrim ( $FromYear ) );
			// ---------------------------------------------------------------------
			$_POST ['pagpersonal'] = $myrow ['pagpersonal'];
			$_POST ['SectComClId'] = $myrow2 ['SectComClId'];
			$_POST ['custpais'] = $myrow2 ['custpais'];
			$_POST ['DescClienteComercial'] = $myrow2 ['descclientecomercial'];
			$_POST ['DescClienteProPago'] = $myrow2 ['descclientepropago'];
			$_POST ['descclienteop'] = $myrow2 ['descclienteop'];
			$_POST ['typeaddenda'] = $myrow2 ['typeaddenda'];
			$_POST ['noproveedor'] = $myrowNumProveedor['numprovider'];
			$_POST ['DiasRevicion'] = $myrow2 ['DiasRevicion'];
			$_POST ['DiasPago'] = $myrow2 ['DiasPago'];
			$_POST ['prefer'] = $myrow2 ['prefer'];
			$_POST ['discountcard'] = $myrow2 ['discountcard'];
            $_POST ['typecomplement'] = $myrow2 ['typecomplement']; 
		}
		
		echo '<input type=hidden name="DebtorNo" value="' . $DebtorNo . '">';
	} else {
		// its a new customer being added
		echo '<input type=hidden name="New" value="Yes">';
		/*
		 * if $AutoDebtorNo in config.php has not been set or if it has been set to a number less than one, then provide an input box for the DebtorNo to manually assigned
		 */
		if ($_SESSION ['AutoDebtorNo'] == 0) {
			echo '<tr><td>' . _ ( 'Codigo de cliente' ) . ':</td>
				<td><input ' . (in_array ( 'DebtorNo', $Errors ) ? 'class="inputerror"' : '') . ' type="Text" name="DebtorNo" value="' . $DebtorNo . '" size=12 maxlength=10></td></tr>';
		}
	}
	
	echo '<tr><td>* ' . _ ( 'A. Paterno/Razon Social' ) . ':</td>
		<td><input ' . (in_array ( 'CustName1', $Errors ) ? 'class="inputerror"' : '') . ' type="Text" name="CustName1" value="' . $_POST ['CustName1'] . '" size=30 maxlength=80></td></tr>';
	echo '<tr><td>' . _ ( 'A. Materno' ) . ':</td>
		<td><input ' . (in_array ( 'CustName2', $Errors ) ? 'class="inputerror"' : '') . ' type="Text" name="CustName2" value="' . $_POST ['CustName2'] . '" size=30 maxlength=40></td></tr>';
	echo '<tr><td> ' . _ ( 'Nombre(s)' ) . ':</td>
		<td><input ' . (in_array ( 'CustName3', $Errors ) ? 'class="inputerror"' : '') . ' type="Text" name="CustName3" value="' . $_POST ['CustName3'] . '" size=30 maxlength=40></td></tr>';
	echo '<tr><td> ' . _ ( 'Nombre Comercial' ) . ':</td>
		<td><input type="Text" name="CommercialName" value="' . $_POST ['CommercialName'] . '" size=30 maxlength=40></td></tr>';
	echo '<tr ><td>* ' . _ ( 'RFC' ) . ':</td>';
	if (! isset ( $_POST ['taxid'] )) {
		$_POST ['taxid'] = '';
	}
	echo '<td><input type="Text" name="taxid" size=15 maxlength=15 value="' . $_POST ['taxid'] . '"></td></tr>';
	
	echo '<tr valign="top"><td>*' . _ ( 'Email' ) . ':</td>';
	echo '<td><input type="Text" name="Email" size=42 maxlength=55 value="' . $_POST ['Email'] . '">';
	$qry = "Select idemail,email FROM custmails
				WHERE debtorno = '" . $DebtorNo . "'
				and active=1
				";
	$res = DB_query ( $qry, $db );
	if (DB_num_rows ( $res ) > 0) {
		echo '<br><select size=3 name="boxemails" >';
		while ( $myregs = DB_fetch_array ( $res ) ) {
			echo '<option value=' . $myregs ['idemail'] . '>' . $myregs ['email'] . '</option>';
		}
		echo '</select>';
		echo "<input type='submit' name='eliminaremails' value='Eliminar'>";
	} else
		echo '<br>Si el cliente no tiene email introduzca: sincorreoelectronico@nombreempresa.com';
	echo '</td></tr>';
	
	echo '<tr><td>* ' . _ ( 'Telefono Fijo' ) . ':</td>
		  <td><input ' . (in_array ( 'PhoneNo', $Errors ) ? 'class="inputerror"' : '') . ' type="Text" name="PhoneNo" value="' . $_POST ['PhoneNo'] . '" size=22 maxlength=20></td></tr>';
	
	echo '<tr><td>' . _ ( 'Telefono Movil' ) . ':</td>';
	echo '<td><input type="Text" name="MovilNo" size=22 maxlength=20 value="' . $_POST ['MovilNo'] . '"></td></tr>';
	echo '<tr><td>' . _ ( 'Nextel' ) . ':</td>';
	echo '<td><input type="Text" name="NextelNo" size=22 maxlength=20 value="' . $_POST ['NextelNo'] . '"></td></tr>';
	$dirsepomex = '<img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _ ( 'Direcciones Sepomex' ) . '" alt=""> ';
	$trabsel = 1;
	$dirsepomex = $dirsepomex . "<a style='display:inline' href='#' onclick='javascript:var win = window.open(\"SepomexSearch_Customer.php?idOpener=$trabsel\", \"sepomex\", \"width=500,height=500,scrollbars=1,left=200,top=150\"); win.focus();'>" . _ ( "Seleccionar direccion" ) . "</a>";
	
	echo '<tr><td>* ' . _ ( 'Direccion' ) . ':</td>
		<td><input ' . (in_array ( 'Address1', $Errors ) ? 'class="inputerror"' : '') . ' type="Text" id=Address1 name="Address1" size=42 maxlength=150 value="' . $_POST ['Address1'] . '">' . $dirsepomex . '</td></tr>';
	echo '<tr><td>* ' . _ ( 'Colonia' ) . ':</td>
		<td><input ' . (in_array ( 'Address2', $Errors ) ? 'class="inputerror"' : '') . ' type="Text" id=Address2 name="Address2" size=42 maxlength=100 value="' . $_POST ['Address2'] . '"></td></tr>';
	echo '<tr><td>* ' . _ ( 'Ciudad' ) . ':</td>
		<td><input ' . (in_array ( 'Address3', $Errors ) ? 'class="inputerror"' : '') . ' type="Text" id=Address3 name="Address3" size=42 maxlength=80 value="' . $_POST ['Address3'] . '"></td></tr>';
	
	/*echo '<tr><td>* ' . _ ( 'Estado' ) . ':</td>
			<td><select name="Address4" id="Address4">';
	
	$qry = "Select * FROM states";
	$rss = DB_query ( $qry, $db );
	echo "<option value=''>" . _ ( 'Ninguno' ) . "</option>";
	
	while ( $rows = DB_fetch_array ( $rss ) ) {
		if ($_POST ['Address4'] == $rows ['state'])
			echo "<option selected value='" . $rows ['state'] . "'>" . $rows ['state'] . "</option>";
		else
			echo "<option value='" . $rows ['state'] . "'>" . $rows ['state'] . "</option>";
	}
	
	echo '</select></td></tr>';*/

	echo '<tr><td>* ' . _ ( 'Estado' ) . ':</td><td>';

	$qslq = "Select * FROM states WHERE UPPER(state)=UPPER('".$_POST ['Address4']."')";
    $rslq = DB_query ( $qslq, $db );
    if ( $rowslq = DB_fetch_array ( $rslq ) ) {
    	$vrauxEd=true;
    }else{
    	$vrauxEd=false;
    }

    if($_SESSION['DatabaseName']=="erppisumma_DES" OR $_SESSION['DatabaseName']=="erppisumma_CAPA" OR $_SESSION['DatabaseName']=="erppisumma"){
	    if($vrauxEd==true){
	    	echo '<select name="Address4" id="Address4">';
	    	echo "<option value='0'>Elige un estado </option>";
	    }
		if($vrauxEd==true){
			$qry = "Select * FROM states";
	        $rss = DB_query ( $qry, $db );
	        while ( $rows = DB_fetch_array ( $rss ) ) {
				if ($_POST ['Address4'] == $rows ['state'])
					echo "<option selected value='" . $rows ['state'] . "'>" . $rows ['state'] . "</option>";
				else
					echo "<option value='" . $rows ['state'] . "'>" . $rows ['state'] . "</option>";
	        }
	        echo '</select>';
		}else{
			echo '<input type="Text" name="Address4"  id="Address4" value="' . $_POST ['Address4'] . '" size=22 maxlength=50>';
		}  
	}else{

	    echo '<select name="Address4" id="Address4">';
	    echo "<option value='0'>Elige un estado </option>";
    	$qry = "Select * FROM states";
        $rss = DB_query ( $qry, $db );
        while ( $rows = DB_fetch_array ( $rss ) ) {
			if ($_POST ['Address4'] == $rows ['state'])
				echo "<option selected value='" . $rows ['state'] . "'>" . $rows ['state'] . "</option>";
			else
				echo "<option value='" . $rows ['state'] . "'>" . $rows ['state'] . "</option>";
        }
	    echo '</select>';
		  
	}  
    
    echo '</td></tr>';


	//echo '<td><input type="Text" name="Address4"  id="Address4" value="' . $_POST ['Address4'] . '" size=22 maxlength=50></td></tr>';
	
	echo '<tr><td>* ' . _ ( 'Pais' ) . ':</td>
		<td><input type="Text" name="custpais"  id="custpais" value="' . $_POST ['custpais'] . '" size=22 maxlength=50></td></tr>';
	echo '<tr><td>' . _ ( 'C.P.' ) . ':</td>
		<td><input ' . (in_array ( 'Address5', $Errors ) ? 'class="inputerror"' : '') . ' type="Text" id=Address5 name="Address5" size=42 maxlength=50 value="' . $_POST ['Address5'] . '"></td></tr>';
	if (Havepermission ( $_SESSION ['UserID'], 2000, $db ) == 1) {
		echo '<tr><td>' . _ ( 'Num Ext' ) . ':</td>
			<td><input type="Text" name="brnumext"  value="' . $_POST ['brnumext'] . '" size=22 maxlength=50></td></tr>';
		echo '<tr><td>' . _ ( 'Num Int' ) . ':</td>
			<td><input type="Text" name="brnumint"  value="' . $_POST ['brnumint'] . '" size=22 maxlength=50></td></tr>';
	} else {
		echo '<input type="hidden" name="brnumext"  value="' . $_POST ['brnumext'] . '" size=22 maxlength=50 />';
		echo '<td><input type="hidden" name="brnumint"  value="' . $_POST ['brnumint'] . '" size=22 maxlength=50 />';
	}
	echo '<tr><td>' . _ ( 'Direccion Extra' ) . ':</td>
		<td><input ' . (in_array ( 'Address6', $Errors ) ? 'class="inputerror"' : '') . ' type="Text" name="Address6" size=42 maxlength=40 value="' . $_POST ['Address6'] . '"></td></tr>';
	
	echo '<tr><td>' . _ ( 'Extra 1' ) . ':</td>
		<td><input ' . (in_array ( 'Address6', $Errors ) ? 'class="inputerror"' : '') . ' type="Text" name="BrPostAddr1" size=42 maxlength=40 value="' . $_POST ['BrPostAddr1'] . '"></td></tr>';
	
	echo '<tr><td>' . _ ( $_SESSION ['NameExtra2'] ) . ':</td>
	
		<td><input ' . (in_array ( 'Address6', $Errors ) ? 'class="inputerror"' : '') . ' type="Text" name="BrPostAddr2" size=42 maxlength=40 value="' . $_POST ['BrPostAddr2'] . '"></td></tr>';
	echo '<tr><td>' . _ ( 'Sector Comercial' ) . ':</td>';
	echo '<td><select  name="SectComClId" id="SectComClId">';
	$SQL = "Select SectComClId, SectComClNom
			From SectComercialCl";
	$resulttag = DB_query ( $SQL, $db );
	echo '<option selected value="">' . _ ( 'Ninguno' ) . '</option>';
	while ( $myrowUN = DB_fetch_array ( $resulttag ) ) {
		if ($_POST ['SectComClId'] == $myrowUN ['SectComClId']) {
			echo '<option selected value="' . $myrowUN ['SectComClId'] . '">' . $myrowUN ['SectComClNom'] . '</option>';
		} else {
			echo '<option value="' . $myrowUN ['SectComClId'] . '">' . $myrowUN ['SectComClNom'] . '</option>';
		}
	}
	echo '</select></td></tr>';
	
	if (Havepermission ( $_SESSION ['UserID'], 1778, $db )==1) {
		$readonlydisccard = '';
	}else{
		$readonlydisccard = 'readonly';
	}
	
	
	echo '<tr><td>' . _('Membresia') . ':</td>
		<td><input type="Text" name="discountcard" size="22" maxlength="40" value="' . $_POST ['discountcard'] . '" ' . $readonlydisccard . ' ></td></tr>';
	
	/*
	 * echo '<tr><td colspan=2><hr color="Darkblue" width=80%></td></tr>'; echo '<tr><td>'._('Fax').':</td>'; echo '<td><input tabindex=10 type="Text" name="FaxNo" size=22 maxlength=20 value="'. $_POST['FaxNo'].'"></td></tr>';
	 */
	echo '<tr><td colspan=2><hr color="Darkblue" width=80%>';
	echo '</td></tr>';
	
	echo '<tr><td colspan="2" style="text-align:center;">' . _ ( 'DATOS VEHICULOS' ) . '</td></tr>';
	echo '<tr><td>' . _ ( 'No. Automoviles' ) . ':</td> 
        <td><input type="Text" name="custdata1" size=4 maxlength=2 class=number value="' . $_POST ['custdata1'] . '"></td></tr>';
	echo '<tr><td>' . _ ( 'No. Camionetas' ) . ':</td> 
        <td><input type="Text" name="custdata2" size=4 maxlength=2 class=number value="' . $_POST ['custdata2'] . '"></td></tr>';
	echo '<tr><td>' . _ ( 'No. Camiones' ) . ':</td> 
        <td><input type="Text" name="custdata3" size=4 maxlength=2 class=number value="' . $_POST ['custdata3'] . '"></td></tr>';
	echo '<tr><td>' . _ ( 'No. Agricolas' ) . ':</td> 
        <td><input type="Text" name="custdata4" size=4 maxlength=2 class=number value="' . $_POST ['custdata4'] . '"></td></tr>';
	echo '<tr><td>' . _ ( 'No. Industrial' ) . ':</td> 
        <td><input type="Text" name="custdata5" size=4 maxlength=2 class=number value="' . $_POST ['custdata5'] . '"></td></tr>';
	echo '<tr><td>' . _ ( 'No. Muevetierra' ) . ':</td> 
        <td><input type="Text" name="custdata6" size=4 maxlength=2 class=number value="' . $_POST ['custdata6'] . '"></td></tr>';
	echo '<tr><td colspan=2><hr color="Darkblue" width=80%></td></tr>';
	
	// *********************************//
	// ***INICIO DATOS DE SUCURSAL******//
	// *********************************//
	echo '<tr><td colspan="2" style="text-align:center;">' . _ ( 'DATOS SUCURSAL' ) . '</td></tr>';
	
	echo '<tr><td>' . _ ( 'Reenviar  Correo de Bienvenida' ) . ':</td>';
	echo '<td><select name="welcomeemail" >';
	if ($_POST ['welcomeemail'] == 1)
		echo '<option selected value="1">No</option>';
	else
		echo '<option value="1">No</option>';
	
	if ($_POST ['welcomeemail'] == 0)
		echo '<option selected value="0">Si</option>';
	else
		echo '<option value="0">Si</option>';
	
	echo '</select></td></tr>';
	
	echo '<tr><td>' . _ ( 'Tipo de Industria' ) . ':</td>';
	if (! isset ( $_POST ['giro'] )) {
		$_POST ['giro'] = '';
	}
	$qry = "Select * FROM giroscliente
			Where status=1";
	$rs = DB_query ( $qry, $db );
	echo '<td><select name="giro" >';
	echo '<option selected value="">' . _ ( 'Ninguno' ) . '</option>';
	while ( $regs = DB_fetch_array ( $rs ) ) {
		if ($_POST ['giro'] == $regs ['description'])
			echo '<option selected value="' . $regs ['description'] . '">' . $regs ['description'] . '</option>';
		else
			echo '<option value="' . $regs ['description'] . '">' . $regs ['description'] . '</option>';
	}
	echo '</select></td></tr>';
	
	// SQL to poulate account selection boxes
	// $sql = "SELECT salesmanname, salesmancode FROM salesman";
	$sql = "SELECT distinct salesmanname, salesmancode
		FROM salesman as sm
		LEFT JOIN areas as ar
		   ON sm.area=ar.areacode
	        LEFT JOIN tags as tg
		   ON ar.areacode=tg.areacode
		LEFT JOIN sec_unegsxuser as u
		   ON u.tagref = tg.tagref
		WHERE u.userid='" . $_SESSION ['UserID'] . "'
		and sm.type=1
		ORDER BY tg.tagref";
	$result = DB_query ( $sql, $db );
	
	if (DB_num_rows ( $result ) == 0) {
		echo '</table>';
		prnMsg ( _ ( 'No existe personal de ventas definido' ) . ' - ' . _ ( 'el cliente debe de contar con un vendedor predeterminado' ) . '. ' . _ ( 'Por favor utilice el enlace para dar de alta vendedores' ), 'error' );
		echo "<br><a href='$rootpath/SalesPeople.php?" . SID . "'>" . _ ( 'Definir personal de ventas' ) . '</a>';
		include ('includes/footer.inc');
		exit ();
	}
	$permisoMod = Havepermission ( $_SESSION ['UserID'], 1311, $db );
	
	//
	echo '<tr><td>' . _ ( 'Vendedor' ) . ':</td>';
	if ($_SESSION ['ShowAllSalesman'] == 1) {
		/* echo"entra1"; */
		$sql = "SELECT DISTINCT concat(area,' | ',salesmanname)  as salesmanname, salesmancode,usersales
	  FROM salesman
	  WHERE type = 1 and status='Active' 
	  ORDER BY salesmanname";
	} else {
		/* echo"entra2"; */
		$sql = "SELECT salesmanname,
						salesmancode
				FROM salesman
				WHERE salesmancode = '" . $_POST ['Salesman'] . "'";
	}
	
	if ($permisoMod == 1) {
		$sql = "SELECT salesmanname,
						salesmancode
				FROM salesman
				WHERE salesmancode = '" . $_POST ['Salesman'] . "'";
		$result = DB_query ( $sql, $db );
		$myrowemp = DB_fetch_array ( $result );
		echo "<td><input type=hidden name=Salesman value='" . $_POST ['Salesman'] . "'>" . $myrowemp ['salesmanname'] . "</td>";
	} else {
		// if (Havepermission ( $_SESSION ['UserID'], 1311, $db )) {
		// echo '<td><select tabindex=22 name="Salesman" disabled>';
		// } else {
		echo '<td><select tabindex=22 name="Salesman">';
		// }
		echo "<option VALUE='0'>" . _ ( 'SELECCIONA..' ) . "</option>";
		while ( $myrow = DB_fetch_array ( $result ) ) {
			if (isset ( $_POST ['Salesman'] ) and $myrow ['salesmancode'] == $_POST ['Salesman']) {
				echo '<option selected VALUE=';
			} else {
				echo '<option VALUE=';
			}
			echo $myrow ['salesmancode'] . '>' . $myrow ['salesmanname'];
		} // end while loop
		
		echo '</select>';
		// if (Havepermission ( $_SESSION ['UserID'], 1311, $db )) {
		// echo "<input type='hidden' name='Salesman' value='" . $_POST ['Salesman'] . "'>";
		// }
		echo '</td>';
	}
	
	echo '</tr>';
	
	DB_data_seek ( $result, 0 );
	if ($_SESSION ['DefaultArea'] == '0') {
		$sql = 'SELECT areacode, areadescription FROM areas';
	} else {
		$sql = "SELECT areacode, areadescription FROM areas where areacode= '" . $_SESSION ['DefaultArea'] . "'";
	}
	
	$result = DB_query ( $sql, $db );
	if (DB_num_rows ( $result ) == 0) {
		echo '</table>';
		prnMsg ( _ ( 'No hay areas en la empresa definidas' ) . ' - ' . _ ( 'el cliente debe contar con un area predefinida' ) . '. ' . _ ( 'Por favor utilice el enlace para definir areas de venta' ), 'error' );
		echo "<br><a href='$rootpath/Areas.php?" . SID . "'>" . _ ( 'Definir areas de venta' ) . '</a>';
		include ('includes/footer.inc');
		exit ();
	}
	
	echo '<tr><td>' . _ ( 'Area de Ventas' ) . ':</td>';
	echo '<td><select name="Area">';
	while ( $myrow = DB_fetch_array ( $result ) ) {
		if (isset ( $_POST ['Area'] ) and $myrow ['areacode'] == $_POST ['Area']) {
			echo '<option selected VALUE=';
		} else {
			echo '<option VALUE=';
		}
		echo $myrow ['areacode'] . '>' . $myrow ['areadescription'];
	} // end while loop
	
	echo '</select></td></tr>';
	DB_data_seek ( $result, 0 );
	
	$sql = 'SELECT loccode, locationname FROM locations';
	$result = DB_query ( $sql, $db );
	
	if (DB_num_rows ( $result ) == 0) {
		echo '</table>';
		prnMsg ( _ ( 'No existen almacenes de venta' ) . ' - ' . _ ( 'el cliente debe contar con un almacen predefinido' ) . '. ' . _ ( 'Por favor utilice el enlace para dar de alta almacenes' ), 'error' );
		echo "<br><a href='$rootpath/Locations.php?" . SID . "'>" . _ ( 'Define Stock Locations' ) . '</a>';
		include ('includes/footer.inc');
		exit ();
	}
	
	echo '<tr><td>' . _ ( 'Almacen' ) . ':</td>';
	echo '<td style=display:none;><select tabindex=21 name="DefaultLocation">';
	
	while ( $myrow = DB_fetch_array ( $result ) ) {
		if (isset ( $_POST ['DefaultLocation'] ) and $myrow ['loccode'] == $_POST ['DefaultLocation']) {
			echo '<option selected VALUE=';
		} else {
			echo '<option VALUE=';
		}
		echo $myrow ['loccode'] . '>' . $myrow ['locationname'];
	} // end while loop
	
	echo '</select></td></tr>';
	
	echo '<tr><td>' . _ ( 'Grupo de Impuestos' ) . ':</td>';
	echo '<td><select name="TaxGroup">';
	
	DB_data_seek ( $result, 0 );
	
	$sql = 'SELECT taxgroupid, taxgroupdescription FROM taxgroups';
	$result = DB_query ( $sql, $db );
	
	while ( $myrow = DB_fetch_array ( $result ) ) {
		if (isset ( $_POST ['TaxGroup'] ) and $myrow ['taxgroupid'] == $_POST ['TaxGroup']) {
			echo '<option selected VALUE=';
		} else {
			echo '<option VALUE=';
		}
		echo $myrow ['taxgroupid'] . '>' . $myrow ['taxgroupdescription'];
	} // end while loop
	
	echo '</select></td></tr>';
	$activar = '';
	if ($_POST ['prefer'] == 1) {
		$activar = 'checked';
	}
	echo '<tr><td colspan="2">
			<input tabindex=37 type="checkbox" name="prefer" value="1" ' . $activar . '> ' . _ ( 'Incluir a repecos' ) . '	
             </td></tr>';

     //razon social
	if($_SESSION['DatabaseName']=="erppisumma_DES" OR $_SESSION['DatabaseName']=="erppisumma_CAPA" OR $_SESSION['DatabaseName']=="erppisumma"){
 		echo '	<tr><td>&nbsp;</td></tr>';
		echo '	<tr>
					<th colspan=2>'. _('RAZON SOCIAL POR CLIENTE').'</th>
				</tr>';

		echo "	<tr>
					<td colspan=2 style=text-align:center>";//onClick=SelectCheckAuto(1,11)  onClick='DesSelectCheckAuto(1,11);'
		echo "		<input style='font-size:11px;' type=submit Name='SelectAllLegal'  Value='" . _('Sel. Todos') . "'>";
		echo "	<input style='font-size:11px;' type=submit Name='DeSelectAllLegal'  Value='" . _('Quitar Sel. a todos') . "'>
		        	</td>";
		echo "</tr>";

		echo '<tr><td colspan=2>';
		$sql = "SELECT  legalbusinessunit.legalid,legalbusinessunit.legalid as codsuc,t1.`debtorno` as codcliente, legalname as suc
				FROM legalbusinessunit  
				LEFT   JOIN  debtorsmaster_legalid t1 ON  legalbusinessunit.legalid =t1.legalid
				AND t1.debtorno='".$DebtorNo."' ORDER BY legalid ";
		$Result = DB_query($sql, $db);
		echo '<table width=80% align=center>';
		if (DB_num_rows($Result)>0 ) {
			echo "<tr><td ><hr></td></tr>";
		}
		$k=0; //row colour counter
		$j=0;
		$tagname = "";
		while($AvailRow = DB_fetch_array($Result)) {

			if ($tagname != $AvailRow['legalid']) {
			//	echo "<tr><th class='titulo_obj'>" . $AvailRow['legalid'] . "</th></tr>";
				$tagname = $AvailRow['legalname'];
			}

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}

			echo '<td nowrap style="font-weight:normal;">';
			$legalid=$AvailRow['legalid'];
			$codcliente=$AvailRow['codcliente'];
			$nombresuc=$AvailRow['suc'];
			if(is_null($codcliente)) {
				if(!isset($_POST['DeSelectAllLegal'])){
					if (((isset($_POST['LegalSel'.$j])) and  ($_POST['LegalSel'.$j]<> '') or isset($_POST['SelectAllLegal']))){
						echo '<INPUT type="checkbox" name="LegalSel' .$j . '" checked value="' . ($j+1) . '">';
					}else{
						echo '<INPUT type="checkbox" name="LegalSel' . $j . '" value="' . ($j+1). '">';
					}
				}else{
					echo '<INPUT type="checkbox" name="LegalSel' .$j .'" value="' . ($j+1). '">';
				}
					
				echo "<INPUT type='hidden' name='NameLegal" . $j . "' value='" . $legalid . "'>";
				echo ucwords(strtolower($nombresuc));
			} else{
				if(!isset($_POST['DeSelectAllLegal'])){
					echo '<INPUT type="checkbox" name="LegalSel' .$j . '" checked value="' . ($j+1) . '">';
					echo '<INPUT type="hidden" name="NameLegal' . $j . '" value="' . $legalid .'">';
					echo ucwords(strtolower($nombresuc));
				}else{
					echo '<INPUT type="checkbox" name="LegalSel' . $j . '" value="' . ($j+1) . '">';
					echo '<INPUT type="hidden" name="NameLegal' . $j . '" value=' . $legalid . '>';
					echo ucwords(strtolower($nombresuc));
				}
					
			}
			$j=$j+1;
			echo '</td>';
			echo '</tr>';
		}//
		echo '<tr><td colspan=2><input type="hidden" name="TotalLegal" value="' . $j . '"></td></tr>';
		echo '</table>';

		echo '</td></tr>';
	}
	/*Fin de asignación de razón social*/

	
	echo '</table></td><td valign=top><table>';
	
	echo '<tr><td colspan="2" style="text-align:center;">' . _ ( 'DATOS COMPRA' ) . '</td></tr>';

	// agregar USO DE CFDI POR debtorno

	$cfd = "SELECT usoCFDI FROM debtorsmaster WHERE debtorno = '".$DebtorNo."' AND usoCFDI IS NOT NULL";
	$rcfd = DB_query($cfd,$db);
	$usoCFDI = "";
	if(DB_num_rows($rcfd) > 0)
	{
		$cfdi = DB_fetch_array($rcfd);
		$usoCFDI = $cfdi['usoCFDI'];
	}

	echo "<tr>
			<td>Uso CFDI: </td>
			<td><select name='usoCFDI'>";
	
	DB_data_seek ( $result, 0 );
	
	$sql = 'SELECT * FROM sat_usocfdi WHERE invoiceuse = "1" and active = "1" ORDER BY c_UsoCFDI asc';
	$result = DB_query ( $sql, $db );
	
	while ( $myrow = DB_fetch_array ( $result ) ) {
		if ($usoCFDI == $myrow['c_UsoCFDI']) {
			echo '<option selected value="'.$myrow['c_UsoCFDI'].'" >'.$myrow['descripcion'].'</option>';
		} else {
			echo '<option  value="'.$myrow['c_UsoCFDI'].'" >'.$myrow['descripcion'].'</option>';
		}
	} // end while loop
	
	echo "</select>
			</td>
		  </tr>";
	// Select sales types for drop down list
	$result = DB_query ( "SELECT typeabbrev, sales_type, IFNULL(sec_pricelist.pricelist, -1) as asign
							FROM salestypes
								LEFT JOIN sec_pricelist ON salestypes.typeabbrev = sec_pricelist.pricelist and sec_pricelist.userid = '" . $_SESSION ['UserID'] . "'", $db );

	echo '<tr><td>' . _ ( 'Tipo Venta' ) . '/' . _ ( 'Precio de Lista' ) . ":</td>
		<td><select name='SalesType'>";
	while ( $myrow = DB_fetch_array ( $result ) ) {
		if ($myrow ['asign'] != '-1' or $_POST ['SalesType'] == $myrow ['typeabbrev']){
			if ($_POST ['SalesType'] == $myrow ['typeabbrev']) {
				echo "<option selected value='" . $myrow ['typeabbrev'] . "'>" . $myrow ['sales_type'];
			} else {
				echo "<option value='" . $myrow ['typeabbrev'] . "'>" . $myrow ['sales_type'];
			}
		}


		
	} // end while loop
	DB_data_seek ( $result, 0 );
	
	// Select Customer types for drop down list for SELECT/UPDATE
	$caption = $_SESSION ['DebtorTypeCaption'];
	if ($caption == "")
		$caption = "Tipo de Cliente";
	if ($_POST ['typeid'] == "" or ! isset ( $_POST ['typeid'] )) {
		//echo 'tipo' . $_SESSION ['TypeCustomerDefault'];
		$_POST ['typeid'] = $_SESSION ['TypeCustomerDefault'];
	}
	$result = DB_query ( 'SELECT debtortype.typeid, typename 
						FROM debtortype inner join sec_debtorxuser on sec_debtorxuser.typeid=debtortype.typeid
						and sec_debtorxuser.userid="' . $_SESSION ['UserID'] . '"
						 Order by orden desc', $db );
	
	if ($permisotipocliente == 1) {

		echo "<tr>";
		echo "<td>" . _ ( $caption );
		echo "<input type=hidden name=typeid value='" . $_POST ['typeid'] . "'>";
		echo "</td>";
		$sqltpocl = "SELECT typename
						FROM debtortype
						WHERE typeid = '" . $_POST ['typeid'] . "'";
		$restpocl = DB_query ( $sqltpocl, $db );
		$myrowtpocl = DB_fetch_array ( $restpocl );
		$nombretpcl = $myrowtpocl ['typename'];
		echo "<td>" . $nombretpcl . "</td>";
		echo "</tr>";
	} else {
		echo '<tr><td>' . _ ( $caption ) . ":</td>
                <td><select name='typeid'>";
		while ( $myrow = DB_fetch_array ( $result ) ) {
			if ($_POST ['typeid'] == $myrow ['typeid']) {
				echo "<option selected VALUE='" . $myrow ['typeid'] . "'>" . $myrow ['typename'] . '</option>';
			} else {
				echo "<option VALUE='" . $myrow ['typeid'] . "'>" . $myrow ['typename'] . '</option>';
			}
		} // end while loop
		DB_data_seek ( $result, 0 );
		
	}
	
	echo '	<tr><td>' . _ ( 'Cliente desde' ) . ' (' . $_SESSION ['DefaultDateFormat'] . '):</td>
			<td><input ' . (in_array ( 'ClientSince', $Errors ) ? 'class="inputerror"' : '') . ' type="Text" readonly name="ClientSince" size=12 maxlength=10 value=' . $_POST ['ClientSince'] . '></td></tr>';
	
	$result = DB_query ( 'SELECT reasoncode, reasondescription FROM holdreasons where reasoncode in (select reasoncode from sec_holdreasons where userid="' . $_SESSION ['UserID'] . '") order by orden desc', $db );
	if ($_POST ['HoldReason'] == "" or ! isset ( $_POST ['HoldReason'] )) {
		$_POST ['HoldReason'] = $_SESSION ['HistoryCustomerDefault'];
	}
	
	// $permisoMod = Havepermission ( $_SESSION ['UserID'], 167, $db );
	
	/*
	 * if ($permisoMod == 0) {
	 * echo '</select></td></tr><tr><td>' . _ ( 'Historial Crediticio' ) . ":</td>
	 * <td><select name='HoldReasonJustView' disabled=true>";
	 * while ( $myrow = DB_fetch_array ( $result ) ) {
	 * if ($_POST ['HoldReason'] == $myrow ['reasoncode']) {
	 * echo '<option selected VALUE=' . $myrow ['reasoncode'] . '>' . $myrow ['reasondescription'];
	 * } else {
	 * echo '<option VALUE=' . $myrow ['reasoncode'] . '>' . $myrow ['reasondescription'];
	 * }
	 * } // end while loop
	 *
	 * echo "<input type=hidden name='HoldReason' value='" . $_POST ['HoldReason'] . "'>";
	 * } else {
	 * echo '</select></td></tr><tr><td>' . _ ( 'Historial Crediticio' ) . ":</td>
	 * <td><select name='HoldReason'>";
	 * while ( $myrow = DB_fetch_array ( $result ) ) {
	 * if ($_POST ['HoldReason'] == $myrow ['reasoncode']) {
	 * echo '<option selected VALUE=' . $myrow ['reasoncode'] . '>' . $myrow ['reasondescription'];
	 * } else {
	 * echo '<option VALUE=' . $myrow ['reasoncode'] . '>' . $myrow ['reasondescription'];
	 * }
	 * } // end while loop
	 * }
	 */
	echo '</select></td></tr><tr><td>' . _ ( 'Historial Crediticio' ) . ":</td>";
	if (Havepermission ( $_SESSION ['UserID'], 167, $db ) == 0) { // 1515
		echo '<td><select tabindex=22 name="HoldReason" disabled>';
	} else {
		echo '<td><select tabindex=22 name="HoldReason">';
	}
	while ( $myrow = DB_fetch_array ( $result ) ) {
		if ($_POST ['HoldReason'] == $myrow ['reasoncode']) {
			echo '<option selected VALUE=' . $myrow ['reasoncode'] . '>' . $myrow ['reasondescription'];
		} else {
			echo '<option VALUE=' . $myrow ['reasoncode'] . '>' . $myrow ['reasondescription'];
		}
	}
	echo '</select>';
	if (Havepermission ( $_SESSION ['UserID'], 167, $db ) == 0) { // 1515
		echo "<input type='hidden' name='HoldReason' value='" . $_POST ['HoldReason'] . "'>";
	}
	echo '</td>';
	
	DB_data_seek ( $result, 0 );
	// $result=DB_query('SELECT terms, termsindicator,cashdiscount,daygrace FROM paymentterms',$db);
	
	if (Havepermission ( $_SESSION ['UserID'], 1127, $db ) == 1) {
		$result = DB_query ( "SELECT terms, paymentterms.termsindicator,cashdiscount,daygrace
			 FROM paymentterms, sec_paymentterms WHERE paymentterms.termsindicator = sec_paymentterms.termsindicator and sec_paymentterms.userid='" . $_SESSION ['UserID'] . "'", $db );
		
		echo '<tr><td>' . _ ( 'Terminos de Pago' ) . ":</td>
		<td nowrap ><select name='PaymentTerms'>";
		
		while ( $myrow = DB_fetch_array ( $result ) ) {
			if ($_POST ['PaymentTerms'] == $myrow ['termsindicator']) {
				if (($_POST ['DescClienteProPago'] != $myrow ['cashdiscount']) or strlen ( $_POST ['DescClienteProPago'] ) == 0) {
					$_POST ['DescClienteProPago'] = $myrow ['cashdiscount'];
					// echo 'entraaaaaa';
				}
				if (! isset ( $_POST ['daygrace'] )) {
					$_POST ['daygrace'] = $myrow ['daygrace'];
				}
				echo "<option selected VALUE=" . $myrow ['termsindicator'] . '>' . $myrow ['terms'];
			} else {
				echo '<option VALUE=' . $myrow ['termsindicator'] . '>' . $myrow ['terms'];
			}
		} // end while loop
		DB_data_seek ( $result, 0 );
		echo '</select>';
		// echo 'desceuento:'.$_POST['PymtDiscount'].'-'.$_POST['PaymentTerms'];
		echo '<input type="submit" value="->" onclick="NovalidaDatosObligatorios();" name="btnTag"></td></tr>';
	} else {
		echo '<tr><td>' . _ ( 'Terminos de Pago' ) . ":</td>"; //
		if ($_POST ['PaymentTerms'] == "" or ! isset ( $_POST ['PaymentTerms'] )) {
			$result = DB_query ( "SELECT terms, paymentterms.termsindicator,cashdiscount,daygrace FROM paymentterms, sec_paymentterms WHERE paymentterms.termsindicator = sec_paymentterms.termsindicator and sec_paymentterms.userid='admin'
					order by terms desc limit 1", $db );
			$myrowtermpago = DB_fetch_array ( $result );
			echo "<td>" . $myrowtermpago ['terms'] . "</td>";
			echo "<input type='hidden' name='PaymentTerms' value='" . $myrowtermpago ['termsindicator'] . "'></td></tr>";
		} else {
			$result = DB_query ( "SELECT terms FROM paymentterms WHERE paymentterms.termsindicator ='" . $_POST ['PaymentTerms'] . "'", $db );
			$myrowtermpago = DB_fetch_array ( $result );
			echo "<td>" . $myrowtermpago ['terms'] . "</td>";
			echo "<input type='hidden' name='PaymentTerms' value='" . $_POST ['PaymentTerms'] . "'></td></tr>";
		}
	}
	
	$result = DB_query ( 'SELECT currency, currabrev FROM currencies', $db );
	echo '
		<tr><td>' . _ ( 'Moneda del Cliente' ) . ":</td>
		<td><select name='CurrCode'>";
	while ( $myrow = DB_fetch_array ( $result ) ) {
		if ($_POST ['CurrCode'] == $myrow ['currabrev']) {
			echo '<option selected value=' . $myrow ['currabrev'] . '>' . $myrow ['currency'];
		} else {
			echo '<option value=' . $myrow ['currabrev'] . '>' . $myrow ['currency'];
		}
	} // end while loop
	DB_data_seek ( $result, 0 );
	echo '</select></td></tr>';
	
	/*
	 * echo '<tr><td>' . _('L�mite de Cr�dito') . ':</td> <td><input ' . (in_array('CreditLimit',$Errors) ? 'class="inputerror"' : '' ) .' type="Text" name="CreditLimit" size=16 maxlength=14 value=' . $_POST['CreditLimit'] . '></td></tr>'; //echo '<td><input type="hidden" name="CreditLimit" size=16 maxlength=14 value=' . $_POST['CreditLimit'] . '></td></tr>';
	 */
	
	$funcion = 166;
	$permiso = Havepermission ( $_SESSION ['UserID'], $funcion, $db );
	
	if ($permiso == 0) {
		// echo '<tr style=display:none;><td colspan=2><hr color="Darkblue" width=80%></td></tr>';
		echo '<tr style=display:none;><td>' . _ ( 'L&iacute;mite de Cr&eacute;dito General' ) . ':</td>
		<td>
			<input tabindex=32 type="text" class="number" name="CreditLimit" value=' . $_POST ['CreditLimit'] . ' size=16 maxlength=14>
			<input tabindex=33 type="hidden" class="number" name="CreditLimit2" value=' . $_POST ['CreditLimit'] . ' size=16 maxlength=14>
		</td></tr>';
	} else {
		// echo '<tr><td colspan=2><hr color="Darkblue" width=80%></td></tr>';
		echo '<tr><td>' . _ ( 'L&iacute;mite de Cr&eacute;dito General' ) . ':</td>
		<td><input tabindex=32 type="text" class="number" name="CreditLimit" value=' . $_POST ['CreditLimit'] . ' size=16 maxlength=14>
		   <input tabindex=33 type="hidden" class="number" name="CreditLimit2" value=' . $_POST ['CreditLimit'] . ' size=16 maxlength=14>
		</td></tr>';
	}
	
	echo '<tr><td>' . _ ( 'Tax Reference' ) . ':</td>
		<td><input type="Text" name="TaxRef" size=22 maxlength=20  value="' . $_POST ['TaxRef'] . '"></td></tr>';
	
	// PERMISO PARA PONER A UN CLIENTE EN LISTA NEGRA
	$permiso1 = Havepermission ( $_SESSION ['UserID'], 758, $db );
	
	// PERMISO PARA NO PONER A UN CLIENTE EN LISTA NEGRA
	$permiso2 = Havepermission ( $_SESSION ['UserID'], 759, $db );
	echo '<tr><td>' . _ ( 'Lista Negra' ) . ":</td>'";
	// echo $_POST['lista'];
	// echo $permiso1;
	// echo $permiso2;
	if ($permiso1 == 1 or $permiso2 == 1) {
		echo "<td><select name='lista' enabled>";
		if ($permiso2 == 1 and $_POST ['lista'] == 0) {
			// echo '<option value=1>' .'SI'. '</option>';
			echo '<option selected VALUE=0>' . _ ( 'NO' );
			if ($permiso1 == 1) {
				// echo '<option value=0>' .'NO'. '</option>';
				echo '<option VALUE=1>' . _ ( 'SI' );
			}
		}
		if ($permiso1 == 1 and $_POST ['lista'] == 1) {
			// echo '<option value=1>' .'SI'. '</option>';
			// echo '</option>';
			if ($permiso2 == 1) {
				// echo '<option value=0>' .'NO'. '</option>';
				echo '<option VALUE=0>' . _ ( 'NO' );
				// echo '</option>';
			}
			echo '<option selected VALUE=1>' . _ ( 'SI' );
		}
	} else {
		echo "<td><select name='lista' desabled>";
		if ($_POST ['lista'] == 1) {
			echo '<option selected VALUE=1>' . _ ( 'SI' );
			
			// echo '<option VALUE=1>' . _('Address to Branch');
		} else {
			// echo '<option VALUE=0>' . _('Address to HO');
			echo '<option selected VALUE=0>' . _ ( 'NO' );
		}
	}
	echo '</select></td></tr>';
	
	$SQL =   DB_query ( "SELECT * FROM debtorsmaster where debtorno = '".$DebtorNo."'" ,$db );
	$row = DB_fetch_array ( $SQL );

	if ($_POST ['rif'] == 'on'  or  $row['RIF'] == 1) {
		echo '<tr><td>' . _ ( 'R&eacute;gimen fiscal RIF' ) . ':</td> <td><input type="checkbox" id="rif" name="rif" checked /> </td></tr>';
	}else{
		echo '<tr><td>' . _ ( 'R&eacute;gimen fiscal RIF' ) . ':</td> <td><input type="checkbox" id="rif" name="rif" /> </td></tr>';
	}

	
 	if ($_SESSION['FacturaVersionComplemento'] == "3.3") {
		echo "<tr><td style='text-align:center;padding-top: 25px;'>" . _ ( 'DATOS BANCARIOS' ) . "</td> 
		<td style='padding-top: 25px;'><input type='button' name='btnBank' id='btnBank' value='Agregar'></td></tr>";
		echo '<tr><td colspan="2"><div id="dvBanks" style="display:none; padding-top: 15px;">
		<div><label style="min-width: 100px;display: inline-block;font-weight: bold;">Nombre corto: </label>
                <input type="text" name="txtBank_name" id="txtBank_name" value=""></div>';
//SELECCIÓN DEL BANCO
			$infobaks= array();
			$SQL = "SELECT  bank_id, bank_shortdescription, taxid
			FROM banks
			WHERE bank_active = 1  AND taxid IS NOT NULL
			ORDER BY bank_shortdescription ASC";
			$TransResult = DB_query ( $SQL, $db );

			while ( $myrow = DB_fetch_array ( $TransResult ) ) {
			array_push($infobaks, array( 'value' => $myrow ['bank_shortdescription'], 'bank_id' => $myrow ['bank_id'], 'Nombre' => utf8_encode($myrow ['bank_shortdescription']), 'Rfc' => utf8_encode($myrow ['taxid']) ));
			}
			echo '<br><div><label style="min-width: 100px;display: inline-block;font-weight: bold;">Nombre Banco: </label>
			                <select name="txtBank_id" id="txtBank_id" onchange="fnBanks(this,\'rfcbank\');">';
			echo "<option value='0' >Selecciona...</option>";
			foreach ($infobaks as $value) {
				$vrSelect ="";
				if(!empty($_POST['BanksB']) && $_POST['BanksB']==$value['bank_id']){
					$vrSelect ="selected";
				}
				echo '<option value="'.$value['bank_id'].'" title="'.$value['Rfc'].'" '.$vrSelect.'>'.$value['Nombre'].'</option>';
			}
			echo "</select></div>";
///////////LLENADO DEL RFC SEGÚN EL BANCO
			echo '<br><div><label style="min-width: 100px;display: inline-block; font-weight: bold;">RFC Banco: </label>
			                <input type="text" name="rfcbank" id="rfcbank" value="' . $_POST['rfcBanks'] . '" maxlength="12" readonly="readonly"></div>';
/////////TERMINO DE PAGO
			$SQL_TP = 'SELECT * 
				FROM paymentmethods 
				WHERE receiptuse = 1
				AND active = 1';
			$result_TP = DB_query ( $SQL_TP, $db );
			echo'<br><div><label style="min-width: 100px;display: inline-block; font-weight: bold;">M&eacute;todo de Pago: </label>
			             <select name="txtTermPago" id="txtTermPago"onchange="fnmtdpagos()">';
			echo "<option value='0' >Selecciona...</option>";
			while($myrow = DB_fetch_array ( $result_TP )){
					echo '<option value="' . $myrow ['paymentname'] . '" data-cdsat="'.$myrow ['codesat'].'">' . $myrow ['paymentname'] . '</option>';
			}
			echo'</select></div>';			
////////////////NÚMERO DE CUENTA
			echo'<br><div><label style="min-width: 100px;display: inline-block; font-weight: bold;">No. cuenta: </label>
			                <input type="text" name="accountbank" id="accountbank" maxlength="18" onchange="Validar1()"></div>';
			echo "<div>
			                
			                     <label class='clLeyenda' id='dvnoOrd' style='padding: 2px 5px; font-size: 11px; color: #ce2a1e;'><br></div>";
			echo "<div>
			                
			             <label class='mensaje1' id='mensaje1' style='padding: 2px 5px; color: #ce2a1e;'><br></div>";
///BOTON 
			echo '<br><div><input type="button" name="btnsavebank" id="btnsavebank" value="Guardar"></div>';
			echo '<tr><td colspan="2" style="text-align:center;"><div id="dvresbank">Cargando informaci&oacute;n...</div></td></tr>';
//TABLA DE DATOS BANCARIOS 
			echo '<tr><td colspan="2" style="text-align:center; display:none;" class="clInfbank">'. _ ('INFORMACI&Oacute;N BANCARIA'). '</td></tr>';
			echo '<tr><td colspan="2" style="text-align:center; display:none;" class="clInfbank">
			<table id="tbbanks" style="width:100%;">
			<thead>
			<tr><th>Nombre corto</th>
			<th>Banco</th>
			<th>RFC</th>
			<th>M&eacute;todo de Pago</th>
			<th>No. cuenta</th>
			</tr></thead><tbody></tbody></table></td></tr>';
		
			echo '<tr><td colspan=2><hr color="Darkblue"></td></tr>';
	}
	// insertar los campos nuevos para la version 2.2 de facturacion
	// ver si el cliente tiene asignado una cuenta y metodo de pago en custbranh
	$qry = "SELECT * FROM typeaddenda";
	$rs = DB_query ( $qry, $db );
	echo '<tr><td >' . _ ( 'Tipo Addenda (F.E.)' ) . ':</td>
		  <td><select  name="typeaddenda" id="typeaddenda">';
	echo '<option selected VALUE=0>' . _ ( 'Ninguna' ) . '</option>';
	while ( $myrows = DB_fetch_array ( $rs ) ) {
		if ($myrows ['id_addenda'] == $_POST ['typeaddenda']) {
			echo '<option selected  value="' . $myrows ['id_addenda'] . '">' . $myrows ['nameaddenda'] . '</option>';
		} else {
			echo '<option  value="' . $myrows ['id_addenda'] . '">' . $myrows ['nameaddenda'] . '</option>';
		}
	} // end while loop
	
	echo '</select></td></tr>';

	/*Agregar el numero de proveedor*/
	echo '<tr><td>' . _ ( 'No. Proveedor' ) . ': </td><td><input type="text" size=17  name="noproveedor" id="noproveedor" value="'.$_POST['noproveedor'].'"></td></tr>';
	/***/

	 /****/
        $qry = "SELECT * FROM typecomplement";
		$rs = DB_query ( $qry, $db );
		echo '<tr><td >' . _ ( 'Tipo de Complementos' ) . ':</td>
		  <td><select  name="typecomplement" id="typecomplement">';
		echo '<option selected VALUE=0>' . _ ( 'Ninguna' ) . '</option>';
		while ( $myrows = DB_fetch_array ( $rs ) ) {
			if ($myrows ['id'] == $_POST ['typecomplement']) {
				echo '<option selected  value="' . $myrows ['id'] . '">' . $myrows ['namecomplement'] . '</option>';
			} else {
				echo '<option  value="' . $myrows ['id'] . '">' . $myrows ['namecomplement'] . '</option>';
			}
		} // end while loop
		
		echo '</select></td></tr>';
                /****/
	$payname = "No identificado";
	$nocta = "";
	
	if ($_POST ['paymentname'])
		$payname = $_POST ['paymentname'];
	
	if ($_POST ['nocuenta'])
		$nocta = $_POST ['nocuenta'];
	
	$qry = "SELECT * FROM paymentmethods";
	$rs = DB_query ( $qry, $db );
	echo '<tr><td >' . _ ( 'Condiciones de Pago' ) . ':</td>
		  <td><select  name="paymentname" id="paymentname">';
	while ( $myrows = DB_fetch_array ( $rs ) ) {
		if ($myrows ['paymentname'] == $payname) {
			echo '<option selected  value="' . $myrows ['paymentname'] . '">' . $myrows ['paymentname'] . '</option>';
		} else {
			echo '<option  value="' . $myrows ['paymentname'] . '">' . $myrows ['paymentname'] . '</option>';
		}
	} // end while loop
	
	echo '</select></td></tr>';
	// echo $_POST['paymentname'] . ' val: ' .$payname ;
	
	echo '<tr><td >' . _ ( 'No de Cuenta' ) . ':</td>
		  <td><input type="text" size=17  name="nocuenta" id="nocuenta" value="' . $nocta . '"></td></tr>';
	
	// tipos de impuestos
	$qry = "SELECT sec_taxes.idtax,
			       sec_taxes.nametax,
			       ifnull(debtortaxes.idtax,0) AS CHECKED,
			       percent
			FROM sec_taxes
			LEFT JOIN debtortaxes ON sec_taxes.idtax = debtortaxes.idtax
			AND debtorno = '$DebtorNo'";
	$rs = DB_query ( $qry, $db );
	echo '<tr valign="top"><td >' . _ ( 'Tipo de Impuesto' ) . ':</td>
	  		<td> ';
	$listchecked = "";
	while ( $myrows = DB_fetch_array ( $rs ) ) {
		if ($myrows ['CHECKED'] != 0) {
			$listchecked .= $myrows ['idtax'] . "|";
			echo '<input onclick="updateList(this,' . $myrows ['idtax'] . ');" type="checkbox" value="' . $myrows ['idtax'] . '" checked> &nbsp;' . $myrows ['nametax'] . '&nbsp;&nbsp;-&nbsp;&nbsp;' . number_format ( $myrows ['percent'], 2 ) . '%<br>';
		} else {
			echo '<input onclick="updateList(this,' . $myrows ['idtax'] . ');" type="checkbox" value="' . $myrows ['idtax'] . '"> &nbsp;' . $myrows ['nametax'] . '&nbsp;&nbsp;-&nbsp;&nbsp;' . number_format ( $myrows ['percent'], 2 ) . '%<br>';
		}
	} // end while loop
	
	echo '</td></tr>';
	echo "<script>document.getElementById('taxclient').value = '" . $listchecked . "';</script>";
	echo '<tr><td colspan=2><hr color="Darkblue"></td></tr>';

	// complementos factura
	$qry = "SELECT cfdicomplement.id,
				       cfdicomplement.complement,
				       IFNULL(valuecomplement.fieldid,0) AS CHECKED
				FROM cfdicomplement
				LEFT JOIN valuecomplement ON valuecomplement.fieldid = cfdicomplement.id
				AND valuecomplement.debtorno='$DebtorNo'
				WHERE cfdicomplement.active = 1";
	$rs = DB_query ( $qry, $db );
	echo '<tr valign="top"><td >' . _ ( 'Complementos CFDI' ) . ':</td>
	  		<td> ';
	$listchecked = "";
	$vrInfcom="";
	while ( $myrows = DB_fetch_array ( $rs ) ) { 
		$vrBandA=0;
		if ($myrows ['CHECKED'] != 0) {
			$vrBandA=1;
			$listchecked .= $myrows ['id'] . "|";
			echo '<input onclick="updateListComplement(this,' . $myrows ['id'] . ');" type="checkbox" value="' . $myrows ['id'] . '" checked> &nbsp;' . $myrows ['complement'] . '<br>';
		} else {
			$vrBandA=0;
			echo '<input onclick="updateListComplement(this,' . $myrows ['id'] . ');" type="checkbox" value="' . $myrows ['id'] . '"> &nbsp;' . $myrows ['complement'] . '<br>';
		}
		$sqlcomcf="SELECT * FROM configcomplement
				   WHERE nametb='debtorsmaster' AND idcomplement='" . $myrows ['id'] ."'";
		$rscon=DB_query($sqlcomcf,$db);
		if(DB_num_rows($rscon)>0){
			$vrmostrar=0;
			while ($rwcon=DB_fetch_array($rscon)) {
				if($vrBandA==0){
					$vrcss='style="display:none;"';	
				}else{
					$vrcss="";
				}
				if($vrmostrar==0){
					$vrInfcom.='<tr class="complementf'.$myrows ['id'] .'" '.$vrcss.' ><td colspan=2 style="font-weight: bolder; padding: 10px 0;">' . $myrows ['complement'] . '<td></tr>';
				}
				
			   	$vrInfcom.='<tr class="complementf'.$myrows ['id'] .'" '.$vrcss.' ><td>' . _( $rwcon['nameform'] ) . ': </td><td><input type="text" size=20  name="'. $rwcon['namecolumn'].'" id="'.$rwcon['namecolumn'].'" value="'.$_POST[$rwcon['namecolumn']].'"></td></tr>';
			   	$vrmostrar=1;
			}
		}  
	} // end while loop
	
	echo '</td></tr>';
	echo "<script>document.getElementById('complementclient').value = '" . $listchecked . "';</script>";

	echo $vrInfcom;
	
	/* added lines 8/23/2007 by Morris Kelly to get po line parameter Y/N */
	echo '<tr><td>' . _ ( 'Require Customer PO Line on SO' ) . ":</td>
	<td><select name='CustomerPOLine' disabled>";
	if ($_POST ['CustomerPOLine'] == 0) {
		echo '<option selected value=0>' . _ ( 'No' );
		echo '<option value=1>' . _ ( 'Yes' );
	} else {
		echo '<option value=0>' . _ ( 'No' );
		echo '<option selected value=1>' . _ ( 'Yes' );
	}
	echo '</select></td></tr>';
	
	echo '<tr><td>' . _ ( 'Invoice Addressing' ) . ":</td>
		<td><select name='AddrInvBranch' disabled>";
	if ($_POST ['InvAddrBranch'] == 0) {
		echo '<option selected VALUE=0>' . _ ( 'Address to HO' );
		echo '<option VALUE=1>' . _ ( 'Address to Branch' );
	} else {
		echo '<option VALUE=0>' . _ ( 'Address to HO' );
		echo '<option selected VALUE=1>' . _ ( 'Address to Branch' );
	}
	// Inicio de Mostreo de descuentos
	
	// echo '<tr><th colspan=2><b>DESCUENTOS</b></th></tr>';
	echo '<tr><td colspan=2><hr color="Darkblue"></td></tr>';
	if (Havepermission ( $_SESSION ['UserID'], 727, $db ) == 1) {
		echo '<tr><td valign="top">' . ('Descuento Comercial(1)') . '</td>';
		echo '<td><input type="text" class="number" name="DescClienteComercial" size=5 maxlength=4 value="' . $_POST ['DescClienteComercial'] . '"></td></tr>';
	}
	echo '<tr><td>' . _ ( 'Descuento (2)' ) . ':</td>
		<td><input tabindex=29 type="textbox" class="number" name="Discount" value=0 size=5 maxlength=4></td></tr>';
	echo '<tr><td>' . _ ( 'Descuento (3)' ) . ':</td>
		 	  <td><input type="text" class="number" name="descclienteop" size=5 maxlength=4 value="' . $_POST ['descclienteop'] . '" ></td></tr>';
	if (Havepermission ( $_SESSION ['UserID'], 728, $db ) == 1) {
		echo '<tr><td valign="top">' . ('Descuento Pronto Pago') . '</td>';
		echo '<td><input type="text" class="number" name="DescClienteProPago" size=5 maxlength=4 value="' . $_POST ['DescClienteProPago'] . '"></td></tr>';
	}
	echo '<tr style=display:none;><td>' . _ ( 'Codigo de Descuento' ) . ':</td>
		<td><input tabindex=30 type="text" name="DiscountCode" size=3 maxlength=2></td></tr>';
	echo '<tr style=display:none;><td>' . _ ( 'Porcentaje de descuento de pago' ) . ':</td>
		<td><input tabindex=31 type="textbox" class ="number" name="PymtDiscount" value="' . $_POST ['PymtDiscount'] . '" size=5 maxlength=4></td></tr>';
	// Fin de Mostreo de descuentos
	
	echo '<tr><td colspan=2><hr color="Darkblue"></td></tr>';
	echo '<tr><td colspan=2>';
	echo "<br>";
	echo '<tr><td valign="top">' . _ ( 'Comentarios:' ) . '</td>';
	echo '<td colspan=2><textarea name=coments cols=30 rows=6 >' . $_POST ['coments'] . '</textarea></td></tr>';
	
	/* Dias de Revision */
	echo '<tr>';
	echo '<td>' . _ ( 'Dias de Revision' ) . ':</td>';
	echo '<td><select name="DiasRevicion">';
	$DiasSemana = array (
			1 => "Lunes",
			2 => "Martes",
			3 => "Miercoles",
			4 => "Jueves",
			5 => "Viernes",
			6 => "Sabado" 
	);
	for($i = 1; $i < 7; $i ++) {
		if ($i == $_POST ['DiasRevicion']) {
			echo '<option selected value="' . $i . '">' . $DiasSemana [$i] . '</option>';
		} else {
			echo '<option value="' . $i . '">' . $DiasSemana [$i] . '</option>';
		}
	}
	echo '</td></tr>';
	/* Dias de Pago */
	echo '<tr>';
	echo '<td>' . _ ( 'Dias de Pago' ) . ':</td>';
	echo '<td><select name="DiasPago">';
	for($i = 1; $i < 7; $i ++) {
		if ($i == $_POST ['DiasPago']) {
			echo '<option selected value="' . $i . '">' . $DiasSemana [$i] . '</option>';
		} else {
			echo '<option value="' . $i . '">' . $DiasSemana [$i] . '</option>';
		}
	}
	echo '</td></tr>';
	/* Razon de compra */
	echo "<tr>";
	echo '<td valign="top">' . _ ( 'Razon de compra:' ) . '</td>';
	echo '<td><textarea name=razoncompra cols=30 rows=6 >' . $_POST ['razoncompra'] . '</textarea></td>';
	echo "</tr>";
	
	/* Medio de contacto */
	/*
	 * echo "<tr>";
	 * echo '<td valign="top">'. _('Medio de contacto:') .'</td>';
	 * //echo '<td><textarea name=mediocontacto cols=30 rows=6 >'. $_POST['mediocontacto'] .'</textarea></td>';
	 * $result=DB_query('SELECT id, title FROM contactmeans WHERE active=1 ',$db);
	 * echo "<td><select name='mediocontacto'>";
	 * while ($myrow = DB_fetch_array($result))
	 * {
	 * if ($_POST['mediocontacto']==$myrow['id']){
	 * echo '<option selected value='. $myrow['id'] . '>' . $myrow['title'];
	 * } else {
	 * echo '<option value='. $myrow['id'] . '>' . $myrow['title'];
	 * }
	 * } //end while loop
	 * echo "</tr>";
	 */
	
	/* cumplea�os */
	echo '<tr>';
	echo '<td valign="top">' . _ ( 'Cumplea&ntilde;os/aniversario' ) . ' (' . $_SESSION ['DefaultDateFormat'] . '):</td>';
	echo '<td><select Name="FromDia">';
	$sql = "SELECT * FROM cat_Days";
	$dias = DB_query ( $sql, $db );
	while ( $myrowdia = DB_fetch_array ( $dias ) ) {
		if ($myrowdia ['DiaId'] == $FromDia) {
			echo '<option VALUE="' . $myrowdia ['DiaId'] . '  " selected>' . $myrowdia ['Dia'] . '</option>';
		} else {
			echo '<option VALUE="' . $myrowdia ['DiaId'] . '">' . $myrowdia ['Dia'] . '</option>';
		}
	}
	echo '</select>';
	echo '&nbsp;<select Name="FromMes">';
	$sql = "SELECT * FROM cat_Months";
	$Meses = DB_query ( $sql, $db );
	while ( $myrowMes = DB_fetch_array ( $Meses ) ) {
		if ($myrowMes ['u_mes'] == $FromMes) {
			echo '<option VALUE="' . $myrowMes ['u_mes'] . '  " selected>' . $myrowMes ['mes'] . '</option>';
		} else {
			echo '<option VALUE="' . $myrowMes ['u_mes'] . '" >' . $myrowMes ['mes'] . '</option>';
		}
	}
	
	echo '</select>';
	echo '&nbsp;<input name="FromYear" type="text" size="4" value=' . $FromYear . '></td>';
	echo '</tr>';
	
	/*
	 * echo "<tr>"; echo '<td valign="top">'. _('Cumplea&ntilde;os') .' (' . $_SESSION['DefaultDateFormat'] . '):</td>'; //$DateString2 = (isset($_POST['fechanacimiento']))?Date($_SESSION['DefaultDateFormat']):; echo '<td><input type="text" name="fechanacimiento" value="'.$_POST['fechanacimiento'].'" size="17" class=date alt="'.$_SESSION['DefaultDateFormat'].'"></td>'; //echo '<td><input type="text" name="fechanacimiento" value="'.$_POST['fechanacimiento'].'" size="17"></td>'; echo "</tr>";
	 */
	
	/* Pagina de internet */
	echo "<tr>";
	echo '<td valign="top">' . _ ( 'Pagina personal:' ) . '</td>';
	echo '<td><textarea name=pagpersonal cols=30 rows=6 >' . $_POST ['pagpersonal'] . '</textarea></td>';
	echo "</tr>";
	echo '<tr><td valign="top">' . _ ( 'codigo proveedor asignado por el cliente' ) . '</td>';
	echo '<td><input type=text name="NumeAsigCliente" value="' . $_POST ['NumeAsigCliente'] . '"></td></tr>';
	/*
	 * if(Havepermission($_SESSION['UserID'], 727, $db)== 1){ echo '<tr><td valign="top">'.('Descuento Comercial').'</td>'; echo '<td><input type="text" name="DescClienteComercial" value="'.$_POST['DescClienteComercial'].'"></td></tr>'; } if(Havepermission($_SESSION['UserID'], 728, $db)== 1){ echo '<tr><td valign="top">'.('Descuento Pronto Pago').'</td>'; echo '<td><input type="text" name="DescClienteProPago" value="'.$_POST['DescClienteProPago'].'"></td></tr>'; } echo '</select></td></tr>';
	 */
	
	// incluir campos para envio de recordatorio de estado de cuenta al cliente
	$qry = "SELECT * FROM debtorsreminder WHERE debtorno = '$DebtorNo'";
	$resr = DB_query ( $qry, $db );
	$chkactiva = "";
	$enabledias = "disabled";
	$chkdias = "";
	$chkmens = "";
	$enablemens = "disabled";
	$chkint = "";
	$chkrep = "";
	$enablerep = "disabled";
	
	
	if (DB_num_rows ( $resr ) > 0) {
		$chkactiva = "checked";
		$reg = DB_fetch_array ( $resr );
		if ($reg ['days'] != 0) {
			$chkdias = "checked";
			$enabledias = "";
		}
		if ($reg ['message'] != "") {
			$chkmens = "checked";
			$enablemens = "";
		}
		if ($reg ['interes'] == 1) {
			$chkint = "checked";
		}
		if ($reg ['repeatv'] > 0) {
			$chkrep = "checked";
			$enablerep = "";
		}
	}else{
		$reg ['days']	 = "";
		$reg ['message'] = "";
		$reg ['interes'] = "";
		$reg ['repeatv'] = "";
	}

	if($reg ['repeatv'] == $reg ['countRepeat'] ){
		$display = "";
	}else{
		$display = " display:none;";
	}

								
	
	echo '<tr><th colspan=2><b>ENVIO DE RECORDATORIO</b></th></tr>';
	echo '<tr><td colspan=2><input title="Habilitar o no el envio de reordatorio al cliente" ' . $chkactiva . ' type="checkbox" name="chkactivarecordatorio" id="chkactivarecordatorio">&nbsp;Habilitar envio</td></tr>';
	echo '<tr><td colspan=2><hr></td></tr>';
	echo '<tr>
			  <td><input ' . $chkdias . ' type="checkbox" name="chkdiasvence" id="chkdiasvence" onclick="Habilitar(this,\'diasvence\');" >&nbsp;Dias vencimiento:</td>
			  <td><input type="text" size="5" name="diasvence" id="diasvence" ' . $enabledias . ' value="' . $reg ['days'] . '"></td>			  
		  </tr>';
	echo '<tr valign="top">
			  <td><input ' . $chkmens . ' type="checkbox" name="chkmensaje" id="chkmensaje" onclick="Habilitar(this,\'mensaje\');">&nbsp;Mensaje:</td>
			  <td><textarea name="mensaje" id="mensaje" cols="30" rows="3" ' . $enablemens . '>' . $reg ['message'] . '</textarea></td>			  
		  </tr>';
	echo '<tr>
			  <td colspan="2"><input ' . $chkint . ' type="checkbox" name="chkmora" id="chkmora" onclick="if (this.checked) document.getElementById(\'chkactivarecordatorio\').checked=true;">&nbsp;Inclu&iacute;r inter&eacute;s moratorio</td>
		  </tr>';
	echo '<tr>
			  <td><input ' . $chkrep . ' type="checkbox" name="chkrepiteenvio" id="chkrepiteenvio" onclick="Habilitar(this,\'repiteenvio\');">&nbsp;Repite envio:<br> &nbsp;<br> &nbsp;</td>
				<td>
					<input type="text" size="5" name="repiteenvio" id="repiteenvio" ' . $enablerep . ' value="' . $reg ['repeatv'] . '">&nbsp;d&iacute;as
					&nbsp;&nbsp; <input  style="'.$display.'" type="checkbox" name="reiniciarEnvio" id="reiniciarEnvio"> <span style="'.$display.'">Reiniciar envio</span>
				</td>			  
			</tr>';
	echo "<tr style='$display'>
					<td colspan=2 style='text-align: right;'>
						<span style='color: red;'>(Los d&iacute;as de envio ya fueron concluidos, reinicie el envio autom&aacute;tico)</span>
					</td>
				</tr>";
	echo '<tr><td colspan=2><hr color="Darkblue"></td></tr>';
	
	if ($_SESSION ['CreditByDepto'] == 1) {
		echo '<tr><td colspan="2"><table border=1 width="100%"><tr><th colspan=3>' . _ ( 'ASIGNACION DE DIAS Y LIMITES DE CREDITO' ) . '</th></td>';
		// esta parte sirve para mostrar la primera tabla con todos los registros existentes
		$sqlrutas = "SELECT day.id_depto,day.numdias,day.id_cliente,day.limitecredit,day.id_usuario,d.department
			FROM diasxcliente as day
			INNER JOIN departments as d on day.id_depto=d.u_department
			WHERE id_cliente='" . $DebtorNo . "'";
		$ErrMsg = _ ( 'No se realizo bien la consulta' );
		$resultrutas = DB_query ( $sqlrutas, $db, $ErrMsg );
		echo "<tr><th>" . _ ( 'Departamento' ) . "</th>
			<th>" . _ ( 'Dias de Credito' ) . "</th>
			<th>" . _ ( 'Limite Credito' ) . "</th>
			</tr>";
		while ( $myrow = DB_fetch_array ( $resultrutas ) ) {
			printf ( "<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				</tr>", $myrow ['id_depto'] . '-' . ' ' . $myrow ['department'], $myrow ['numdias'], $myrow ['limitecredit'] );
		}
		if (Havepermission ( $_SESSION ['UserID'], 965, $db ) == 1) {
			if ($_SESSION ['CreditByDeptoDatos'] == 1) {
				$sqltermino = "SELECT terms,
									paymentterms.termsindicator,
									cashdiscount,daygrace,
									paymentterms.daysbeforedue
			 				FROM paymentterms, sec_paymentterms 
							WHERE paymentterms.termsindicator = sec_paymentterms.termsindicator 
							and sec_paymentterms.userid='" . $_SESSION ['UserID'] . "'
							and paymentterms.termsindicator = '" . $_POST ['PaymentTerms'] . "'";
				$resultterm = DB_query ( $sqltermino, $db );
				if (DB_num_rows ( $resultterm ) > 0) {
					$myrowterm = DB_fetch_array ( $resultterm );
					$limitediascredito = $myrowterm ['daysbeforedue'];
				} else {
					$limitediascredito = 0;
				}
				if ($_POST ['CreditLimit'] != "") {
					$limitecredito = $_POST ['CreditLimit'];
				} else {
					$limitecredito = 0;
				}
				echo '<tr><td COLSPAN=3 style="text-align:center;"><a href="' . $rootpath . '/ReporteLimitxCliente.php?' . SID . '&DebtorNo=' . $DebtorNo . '&CustName1=' . $_POST ['CustName1'] . '&diascredit=' . $limitediascredito . '&limitcredit=' . $limitecredito . '">CONFIGURACION DE AUTORIZACIONES DE CREDITO X DEPTO.</a></td></tr>';
			} else {
				echo '<tr><td COLSPAN=3 style="text-align:center;"><a href="' . $rootpath . '/ReporteLimitxCliente.php?' . SID . '&DebtorNo=' . $DebtorNo . '&CustName1=' . $_POST ['CustName1'] . '">CONFIGURACION DE AUTORIZACIONES DE CREDITO X DEPTO.</a></td></tr>';
			}
		}
		
		echo '</table></td></tr>';
	}
	
	echo '</table></td></tr>';
	echo '<tr><td colspan="2"><input tabindex=37 type="checkbox" name="uniquebranch" value="1" checked> ' . _ ( 'Sucursal Unica' ) . '
			<input type="hidden" name="identifier" value="' . $identifier . '">	
			</td></tr>';
	// fin recordatorio
	
	echo '</table>';
	$sql = 'SELECT * FROM custcontacts where debtorno="' . $DebtorNo . '" ORDER BY contid';
	$result = DB_query ( $sql, $db );
	
	echo '<table border=1 width="80%">';
	echo '<tr>
			<th>' . _ ( 'Nombre' ) . '</th>
			<th>' . _ ( 'Puesto' ) . '</th>
			<th>' . _ ( 'Tel&eacute;fono' ) . '</th>
			<th>' . _ ( 'Notas' ) . '</th>
			<th>' . _ ( 'Editar' ) . '</th>
			<th colspan=2><input type="Submit" name="addcontact" VALUE="' . _ ( 'Agregar Contacto' ) . '"></th></tr>';
	
	$k = 0; // row colour counter
	
	while ( $myrow = DB_fetch_array ( $result ) ) {
		if ($k == 1) {
			echo '<tr class="OddTableRows">';
			$k = 0;
		} else {
			echo '<tr class="EvenTableRows">';
			$k = 1;
		}
		printf ( '<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href="AddCustomerContacts.php?Id=%s&DebtorNo=%s">' . _ ( 'Edit' ) . '</a></td>
				<td><a href="%sID=%s&DebtorNo=%s&delete=1">' . _ ( 'Delete' ) . '</a></td>
				</tr>', $myrow [2], $myrow [3], $myrow [4], $myrow [5], $myrow [0], $myrow [1], $_SERVER ['PHP_SELF'] . "?" . SID, $myrow [0], $myrow [1] );
	} // END WHILE LIST LOOP
	echo '</table>';
	// echo "<input type='Submit' name='addcontact' VALUE='" . _('ADD Contact') . "'>";
	
	echo "</form>";
	
	echo "<form method='post' action=" . $_SERVER ['PHP_SELF'] . '?' . SID . '&DebtorNo="' . $DebtorNo . '"&ID=' . $ID . '&Edit' . $Edit . '>';
	if (isset ( $Edit ) and $Edit != '') {
		$SQLcustcontacts = 'SELECT * from custcontacts
							WHERE debtorno="' . $DebtorNo . '"
							and contid=' . $ID;
		$resultcc = DB_query ( $SQLcustcontacts, $db );
		$myrowcc = DB_fetch_array ( $resultcc );
		$_POST ['custname'] = $myrowcc ['contactname'];
		$_POST ['role'] = $myrowcc ['role'];
		$_POST ['phoneno'] = $myrowcc ['phoneno'];
		$_POST ['faxno'] = $myrowcc ['faxno'];
		$_POST ['email'] = $myrowcc ['email'];
		$_POST ['notes'] = $myrowcc ['notes'];
		echo '<table border=1>';
		echo "<tr>
				<td>" . _ ( 'Nombre' ) . "</td><td><input type=text name='custname' value='" . $_POST ['custname'] . "'></td></tr><tr>
				<td>" . _ ( 'Puesto' ) . "</td><td><input type=text name='role' value='" . $_POST ['role'] . "'></td></tr><tr>
				<td>" . _ ( 'Tel&eacute;fono' ) . "</td><td><input type='text' name='phoneno' value='" . $_POST ['phoneno'] . "'></td></tr><tr>
				<td>" . _ ( 'Notas' ) . "</td><td><textarea name='notes'>" . $_POST ['notes'] . "</textarea></td></tr>
				<tr><td colspan=2><input type=submit name=update value=update></td></tr></table>
				";
		echo "<form method='post' action=" . $_SERVER ['PHP_SELF'] . '?' . SID . '&DebtorNo="' . $DebtorNo . '"&ID"' . $ID . '">';
	}
	if (isset ( $_POST ['update'] )) {
		$SQLupdatecc = 'UPDATE custcontacts
							SET contactname="' . $_POST ['custname'] . '",
							role="' . $_POST ['role'] . '",
							phoneno="' . $_POST ['phoneno'] . '",
							notes="' . DB_escape_string ( $_POST ['notes'] ) . '"
							Where debtorno="' . $DebtorNo . '"
							and contid="' . $Edit . '"';
		$resultupcc = DB_query ( $SQLupdatecc, $db );
		echo '<br>' . $SQLupdatecc;
		echo '<meta http-equiv="Refresh" content="0; url="' . $_SERVER ['PHP_SELF'] . '?' . SID . '&DebtorNo=' . $DebtorNo . '&ID=' . $ID . '">';
	}
	if (isset ( $_GET ['delete'] )) {
		$SQl = 'DELETE FROM custcontacts where debtorno="' . $DebtorNo . '"
				and contid="' . $ID . '"';
		$resultupcc = DB_query ( $SQl, $db );
		echo '<meta http-equiv="Refresh" content="0; url=' . $_SERVER ['PHP_SELF'] . '?' . SID . '&DebtorNo=' . $DebtorNo . '">';
		echo '<br>' . $SQl;
		prnmsg ( 'Contact Deleted', 'success' );
	}
	echo '</td></tr></table>';
	if (isset ( $_POST ['New'] ) and $_POST ['New']) {
		echo "<div class='centre'><input type='Submit' name='submit' VALUE='" . _ ( 'Agregar Cliente' ) . "'><br><input type=submit name='reset' VALUE='" . _ ( 'Limpiar Campos' ) . "'></form>";
	} else {
		echo "<hr>";
		echo "<table border=0 width=40%><tr><td style=text-align:center>";
		echo '<input type="button" name="submit" onclick="validaDatosObligatorios();" value="'. _ ( 'Actualizar' ) .'"></td>';
		echo '<td style="text-align:center">';
		echo '<input type="hidden" name="delete" VALUE="'. _ ( 'Eliminar' ) .'" onclick=\"return confirm("'. _ ( 'Estas seguro?' )  . '");\">';
		echo '<input type="hidden" name="actualizar" VALUE="1" >';
		if($DebtorNo!=""){
			echo '<input type="hidden" name="DebtorNo" VALUE="'.$DebtorNo.'" id="DebtorNo">';
		}
		echo "</td></tr></table>";
	}
	if (isset ( $_POST ['addcontact'] ) and (isset ( $_POST ['addcontact'] ) != '')) {
		echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/AddCustomerContacts.php?' . SID . '&DebtorNo=' . $DebtorNo . '">';
	}
	echo '</div>';
} // end of main ifs
include ('includes/footer.inc');

function debug(){
	$show_debug=true;
	error_reporting(E_ALL);
	/*ini_set('display_errors', '1');
	 ini_set('log_errors', 1);
	 ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
	 */

	echo "<br/>---Sesion";
	$keys_sesion = array_keys($_SESSION);
	foreach ($keys_sesion as $key_sesion)
	{
		$$key_sesion = $_SESSION[$key_sesion];
		//error_log("variable $key_sesion");
		echo "<br/>$key_sesion:".$_SESSION[$key_sesion];
	}
	//*/
	/*
	echo "<br/>---Server";
	$keys_sesion = array_keys($_SERVER);
	foreach ($keys_sesion as $key_sesion)
	{
		$$key_sesion = $_SERVER[$key_sesion];
		//error_log("variable $key_sesion");
		echo "<br/>$key_sesion:".$_SERVER[$key_sesion];
	}
	//*/
	 echo "<br/>---POST";
	 $keys_sesion = array_keys($_POST);
	 foreach ($keys_sesion as $key_sesion)
	 {
	 $$key_sesion = $_POST[$key_sesion];
	 //error_log("variable $key_sesion");
	 echo "<br/>$key_sesion:".$_POST[$key_sesion];
	 }

	 echo "<br/>---GET";
	 $keys_sesion = array_keys($_GET);
	 foreach ($keys_sesion as $key_sesion)
	 {
	 $$key_sesion = $_GET[$key_sesion];
	 //error_log("variable $key_sesion");
	 echo "<br/>$key_sesion:".$_GET[$key_sesion];
	 }

	 //*/
}
?>
<script type="text/javascript">
	var NombresCode={};
	
	$(document).ready(function() {        
		getProductos();
		fnmtdpagos();
        fnBankscustomers();
        $('#btnBank').click(function(){
        	$('#dvBanks').fadeIn();
        });
        $('#btnsavebank').click(function(){
        	if($('#txtBank_id').val()!="" && $('#rfcbank').val()!=""){
				$('#dvresbank').html('guardando informaci&oacute;n...');

				var codigo=$("#txtTermPago option:selected").data('cdsat');
					var identi=$("#identificador").val();
					console.log(identi);
        		fnajax('fnSavebank',{bnk:$('#txtBank_id').val(), rfc:$('#rfcbank').val(), account:$('#accountbank').val(), idRegc:$('#DebtorNo').val(), nameBank:$('#txtBank_name').val(), codesat:codigo, identificador:identi},function(response){
	        		try{
	        			if(response==1){
	        				$('#dvresbank').html();
	        				fnBankscustomers();
	        				$('#rfcbank').html('');
	        				$('#accountbank').html('');
	        			}
	        		}catch(e){
	        			$('#dvresbank').html('Surgio un error, intente nuevamente.');
	        		}
	        	});
        	}else{
        		$('#dvresbank').html('Ingrese los datos.');
        	}
			
			$('#accountbank').val('');
			$('#txtBank_name').val('');
			$('#rfcbank').val('');
			$('.clLeyenda').html("");
			$('#mensaje1').html("");
        });

    });

    function getProductos(){
        $.ajax({
          method: "POST",
          dataType:"json",
          url: "CustomerReceiptcls4_Model.php",
          data:'option=allbanks'
        })
        .done(function( data ) {
            console.log(data);
            if(data.result)
            {
                infobanks = data.contenido.infobanks;
                
                $( "#inbank").autocomplete({
                    source: infobanks,
                    select: function( event, ui ) {
                        
                        $( this ).val( ui.item.Nombre );
                        $( "#txtBank_id" ).val( ui.item.bank_id );
                        $( "#rfcbank" ).val( ui.item.Rfc );
                        
                        //console.log(item);
                         NombresCode = { bank_id: ui.item.Rfc, Nombre: ui.item.Nombre};

                        return false;
                    }
                })
                .autocomplete( "instance" )._renderItem = function( ul, item ) {

					return $( "<li>" )
					.append( "<a>" + item.Nombre + "</a>" )
					.appendTo( ul );

                };  
            }
            //console.log(infounitsofmeasure);
        })
        .fail(function(result) {
            console.log( result );
        });
    }
    function fnBankscustomers(){
    	$('#dvresbank').html('Cargando informaci&oacute;n');
    	fnajax('fnBanks',{idRegc:$('#DebtorNo').val()},function(response){
    		try{
    			if(response!=0){
    				$('#dvresbank').html('');
    				$('#tbbanks tbody').html('');
    				$('.clInfbank').fadeIn();
    				//var datos=JSON.parse(response);
    				//console.log(datos);
    				for(var i=0; i<response.length; i++){
    					$('#tbbanks tbody').append(
							'<tr><td>'+response[i].namec+'</td><td>'+response[i].bnk+'</td><td>'+response[i].rfc+'</td><td>'+response[i].pago+'</td><td>'+response[i].numero+'</td><td><input type="button" class="btnDell" data-item="'+response[i].bnkid+'" value="Eliminar"></td></tr>');
    				}
    				fnEventos();
    			}else{
    				$('#dvresbank').html('No hay informaci&oacute;n.');
    			}
    		}catch(e){

    		}
    	});
    }
    function fnEventos(){
    	$('.btnDell').click(function(){
    		fnajax('fnItemdelet',{iditem:$(this).data('item')},function(response){
    			try{
    				if(response==1){
    					fnBankscustomers();
    				}
    			}catch(e){}
    		});
    	});
    }
    function fnajax(funcion, data, callback){
    	data.funcion=funcion;
    	$.ajax({
          method: "POST",
          dataType:"json",
          url: "Customer_Model.php",
          data:data
        })
        .done(function( data ) {
        	console.log(data);
            if(callback){callback(data);}
        })
        .fail(function(result) {
            console.log( result );
        });
    }
	function updateList(obj,valor){
		var lista = document.getElementById('taxclient').value;
		
		if (obj.checked)
			lista += valor+"|";
		else{
			var oldlist = lista.substring(0,lista.length-1);
			var arr = oldlist.split("|");
			lista = "";
			for(i=0;i<arr.length;i++)
				if (arr[i]!=valor)
					lista+=arr[i]+"|";
			
		}	
		
		document.getElementById('taxclient').value = lista;
		
	}
	function updateListComplement(obj, valor) {
		var lista = document.getElementById('complementclient').value;
		if (obj.checked){
			lista += valor+"|";
			$('.complementf'+valor).fadeIn();
		}else{
			$('.complementf'+valor).fadeOut();
			var oldlist = lista.substring(0,lista.length-1);
			var arr = oldlist.split("|");
			lista = "";
			for(i=0;i<arr.length;i++){
				if (arr[i]!=valor)
					lista+=arr[i]+"|";
			}
		}	
		document.getElementById('complementclient').value = lista;

	}
	function Habilitar(obj,nameobj){
		if (obj.checked){
			document.getElementById(nameobj).disabled = false;
			document.getElementById('chkactivarecordatorio').checked=true;
		}
		else	
			document.getElementById(nameobj).disabled = true;
	}
	function validaDatosObligatorios(){
		var noerror = 1;
		if (document.FDatos.CustName1.value.length==0){
			alert('Los campos marcados con * no pueden quedar vacios');
			document.FDatos.CustName1.focus();
			exit;
		}
		<?php if ($_SESSION['ForzarCapturaRFC'] == 1){?>
		if (document.FDatos.taxid.value.length==0){
			alert('Los campos marcados con * no pueden quedar vacios');
			document.FDatos.CustName1.focus();
			exit;
		}
		<?php }?>
		if (document.FDatos.PhoneNo.value.length==0){
			alert('Los campos marcados con * no pueden quedar vacios');
			document.FDatos.PhoneNo.focus();
			exit;
		}
		
		if (document.FDatos.Address1.value.length==0){
			alert('Los campos marcados con * no pueden quedar vacios');
			document.FDatos.Address1.focus();
			exit;
		}
		
		if (document.FDatos.Address2.value.length==0){
			alert('Los campos marcados con * no pueden quedar vacios');
			document.FDatos.Address2.focus();
			exit;
		}
		/*
		if (document.FDatos.brnumext.value.length==0){
			alert('Los campos marcados con * no pueden quedar vacios');
			document.FDatos.brnumext.focus();
			exit;
		}
		*/
		if (document.FDatos.Address3.value.length==0){
			alert('Los campos marcados con * no pueden quedar vacios');
			document.FDatos.Address3.focus();
			exit;
		}
		if(document.FDatos.Address4.value==0){
			alert('Los campos marcados con * no pueden quedar vacios');
			document.FDatos.Address4.focus();
			exit;	
		}
		
		document.FDatos.actualizar.value=1;
		document.forms['FDatos'].submit();
		
	}
	function NovalidaDatosObligatorios(){
		//alert('aaaa');
		document.FDatos.actualizar.value=0;
		document.forms['FDatos'].submit();
	}
	function validaDatosObligatorios1(){
		var noerror = 1;
		var vrOpn=<?php echo $vrOpn; ?>;
		if (document.FDatos.CustName1.value.length==0){
			alert('Los campos marcados con * no pueden quedar vacios');
			document.FDatos.CustName1.focus();
			return false;
		}
		<?php if ($_SESSION['ForzarCapturaRFC'] == 1){?>
		if (document.FDatos.taxid.value.length==0){
			alert('Los campos marcados con * no pueden quedar vacios');
			document.FDatos.CustName1.focus();
			return false;
		}
		<?php }?>
		if (document.FDatos.PhoneNo.value.length==0){
			alert('Los campos marcados con * no pueden quedar vacios');
			document.FDatos.PhoneNo.focus();
			return false;
		}
		
		if (document.FDatos.Address1.value.length==0){
			alert('Los campos marcados con * no pueden quedar vacios');
			document.FDatos.Address1.focus();
			return false;
		}
		
		if (document.FDatos.Address2.value.length==0){
			alert('Los campos marcados con * no pueden quedar vacios');
			document.FDatos.Address2.focus();
			return false;
		}
		/*
		if (document.FDatos.brnumext.value.length==0){
			alert('Los campos marcados con * no pueden quedar vacios');
			document.FDatos.brnumext.focus();
			return false;
		}
		*/
		if (document.FDatos.Address3.value.length==0){
			alert('Los campos marcados con * no pueden quedar vacios');
			document.FDatos.Address3.focus();
			return false;
		}
		if(vrOpn==2){
			if(document.FDatos.Address4a.value==0 && document.FDatos.Address4.value.length==0){
				alert('Los campos marcados con * no pueden quedar vacios');
				document.FDatos.Address4.focus();
				return false;
			}else if(document.FDatos.Address4a.value==0){
				alert('Los campos marcados con * no pueden quedar vacios');
				document.FDatos.Address4.focus();
				return false;
			}	
		}else{	
			if(document.FDatos.Address4.value.length==0){
				alert('Los campos marcados con * no pueden quedar vacios');
				document.FDatos.Address4.focus();
				return false;
			}
		}

		
		return true;
		
	}
	function fnBanks(sel,txtrfc){
        var txtRFC=sel.options[sel.selectedIndex].title;
        if(sel.value!=0){
            $('#'+txtrfc).val(txtRFC);
        }else{
            $('#'+txtrfc).val("");
        }
	}
	function fnmtdpagos(){
        var vrIfo = [
                ["02","Acepta 11 o 18 d&iacute;gitos", "Acepta 10,11,15,16 o 18 d&iacute;gitos num&eacute;ricos; o 10 o 50 d&iacute;gitos alfanum&eacute;ricos." ],
                ["03","Acepta 10,16 o 18 d&iacute;gitos", "Acepta 10 o 18 d&iacute;gitos"],
                ["04","Solo acepta 16 d&iacute;gitos", "Acepta 10,11,15,16,o 18 d&iacute;gitos num&eacute;ricos; o 10 o 50 d&iacute;gitos alfanum&eacute;ricos."],
                ["05","Acepta 10,11,15,16 o 18 d&iacute;gitos num&eacute;ricos; o 10 o 50 d&iacute;gitos alfanum&eacute;ricos.", "Acepta 10,11,15,16,o 18 d&iacute;gitos num&eacute;ricos; o 10 o 50 d&iacute;gitos alfanum&eacute;ricos."],
                ["06","Solo acepta 10 d&iacute;gitos", ""],
                ["28","Solo acepta 16 d&iacute;gitos", "Acepta 10,11,15,16,o 18 d&iacute;gitos num&eacute;ricos; o 10 o 50 d&iacute;gitos alfanum&eacute;ricos."],
                ["29","Acepta 15 o 16 d&iacute;gitos", "Acepta 10,11,15,16 o 18 d&iacute;gitos num&eacute;ricos; o 10 o 50 d&iacute;gitos alfanum&eacute;ricos." ],
                ["99","Solo acepta 10 d&iacute;gitos", "Solo acepta 10 d&iacute;gitos" ]
			];
			
            var vrCode=$( "#txtTermPago option:selected" ).data('cdsat');
            $('#trfechaD').fadeIn();
            if(vrCode=='02' || vrCode=='03' || vrCode=='04' || vrCode=='05' || vrCode=='06' || vrCode=='28' || vrCode=='29'  || vrCode=='99'){
                for(var i=0; i<vrIfo.length; i++){
                    if(vrIfo[i][0]==vrCode){
                        $('#dvnoOrd').html('* '+vrIfo[i][1]);
                        break;
                    }
                }
                $('.clmtdp').fadeIn();
            }else{
                $('#ncuentabenef').val('');
                $('.clmtdp').fadeOut();
                $('.clLeyenda').html("");
			}
   }

function Validar1(){
           var expres1=/(^[0-9]{11}$)|(^[0-9]{18}$)/;
		   var expres2=/(^[0-9]{10}$)|(^[0-9]{16}$)|(^[0-9]{18}$)/;
		   var expres3=/(^[0-9]{16}$)/;
		   var expres4=/(^[0-9]{10}$)|(^[0-9]{11}$)|(^[0-9]{15}$)|(^[0-9]{16}$)|(^[0-9]{18}$)|(^[A-Z0-9_]{10}$)|(^[A-Z0-9_]{50}$)/;
		   var expres5=/(^[0-9]{10}$)/;
		   var expres6=/(^[0-9]{16}$)/;
		   var expres7=/(^[0-9]{15}$)|(^[0-9]{16}$)/;
		   var expres8=/(^[0-9]{10}$)/;

	var cadena=$("#txtTermPago option:selected" ).data('cdsat');
	var textoc=$("#accountbank").val();
	console.log(cadena);
	console.log(textoc);

			if((expres1.test(textoc) && cadena=='02') || (expres2.test(textoc) && cadena=='03') || (expres3.test(textoc) && cadena=='04') || (expres4.test(textoc) && cadena=='05') || (expres5.test(textoc) && cadena=='06') || (expres6.test(textoc) && cadena=='28') || (expres7.test(textoc) && cadena=='29') || (expres8.test(textoc) && cadena=='99')||(cadena=='01')||(cadena=='08')||(cadena=='12')||(cadena=='13')||(cadena=='14')||(cadena=='15')||(cadena=='17')||(cadena=='23')||(cadena=='24')||(cadena=='25')||(cadena=='26')||(cadena=='27')||(cadena=='30')) {
			$('#mensaje1').html("");
			}else{
				$('#mensaje1').html('EL N&Uacute;MERO DE D&Iacute;GITOS DE CUENTA, NO CORRESPONDE CON LA ESTRUCTURA');
				}
}
</script>
