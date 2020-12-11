<?php
//
/* $Revision: 1.53 $ */
/*Cambios:
Se le agrego el  include('includes/SecurityFunctions.inc');

Elías:
23-May-2018: Se agrega el permiso 2115 y su validación para poder editar el campo contacto

*/

$PageSecurity = 3;
include ('includes/session.inc');
$title = _ ( 'Customer Branches' );
include ('includes/header.inc');
$funcion = 161;
include ('includes/SecurityFunctions.inc');
include ('includes/Functions.inc');

$SelectOrderItemsV5 = HavepermissionURL ( $_SESSION ['UserID'], 4, $db );
if (isset ( $_GET ['DebtorNo'] )) {
	$DebtorNo = strtoupper ( $_GET ['DebtorNo'] );
} else if (isset ( $_POST ['DebtorNo'] )) {
	$DebtorNo = strtoupper ( $_POST ['DebtorNo'] );
}

if (! isset ( $DebtorNo )) {
	prnMsg ( _ ( 'Esta p�gina debe ser llamada con el c�digo de deudor del cliente para el que desee editar las sucursales ' ) . '. <br>' . _ ( 'Cuando las p�ginas se les llama desde dentro del sistema, �ste ser� siempre el caso ' ) . ' <br>' . _ ( 'Seleccione un cliente primero y luego seleccione el enlace para a�adir / editar / borrar sucursales' ), 'warn' );
	include ('includes/footer.inc');
	exit ();
}
$codificacionJibe = 0;
if (preg_match("/erpjibe/", $_SESSION['DatabaseName']) and !preg_match("/erpjibe_DES/", $_SESSION['DatabaseName'])) {
	//la codificación no esla misma que las otras implementaciones
	$codificacionJibe = 1;
}
if (isset ( $_GET ['SelectedBranch'] )) {
	$SelectedBranch = strtoupper ( $_GET ['SelectedBranch'] );
} else if (isset ( $_POST ['SelectedBranch'] )) {
	$SelectedBranch = strtoupper ( $_POST ['SelectedBranch'] );
}

if (isset ( $_GET ['from'] )) {
	$_SESSION ['frompage'] = $_GET ['from'];
}
// DATOS DE COTIZACION O DE ORDEN DE VENTA
if (isset ( $_GET ['identifier'] )) {
	$_SESSION ['identifier'] = $_GET ['identifier'];
}

if (isset ( $_POST ['identifier'] )) {
	$identifier = $_POST ['identifier'];
} else {
	if (isset ( $_GET ['identifier'] )) {
		$identifier = $_GET ['identifier'];
	}
}

if (empty($_POST ['DefaultShipVia'])) {
	$_POST ['DefaultShipVia']= "0";
}
// This link is already available on the menu on this page
// echo "<a href='" . $rootpath . '/SelectCustomer.php?' . SID . "'>" . _('Back to Customers') . '</a><br>';

if (isset ( $Errors )) {
	unset ( $Errors );
}

// initialise no input errors assumed initially before we test
$Errors = array ();
$InputError = 0;

if (isset ( $_POST ['submit'] )) {
	
	$i = 1;
	
	/*
	 * actions to take once the user has clicked the submit button ie the page has called itself with some user input
	 */
	$sql = "SELECT * FROM custbranch WHERE branchcode = '" . $SelectedBranch . "'";
	if($codificacionJibe==1){
		DB_query("SET NAMES 'utf8';",$db);
	}
	$rs_custbranch = DB_query ( $sql, $db );
	$sucursal_actual = DB_fetch_array ( $rs_custbranch );
	// first off validate inputs sensible
	
	$_POST ['BranchCode'] = strtoupper ( $_POST ['BranchCode'] );
	
	if (strstr ( $_POST ['BranchCode'], "'" ) or strstr ( $_POST ['BranchCode'], '"' ) or strstr ( $_POST ['BranchCode'], '&' )) {
		$InputError = 1;
		prnMsg ( _ ( 'El Codigo de la sucursal no puede contener ninguno de los siguientes caracteres' ) . " -  & \'", 'error' );
		$Errors [$i] = 'BranchCode';
		$i ++;
	}
	if (strlen ( $_POST ['BranchCode'] ) == 0) {
		$InputError = 1;
		prnMsg ( _ ( 'El Codigo de la sucursal debe tener al menos un caracter de longitud' ), 'error' );
		$Errors [$i] = 'BranchCode';
		$i ++;
	}
	if (strlen ( $_POST ['BranchCode'] ) > 10) {
		$InputError = 1;
		prnMsg ( _ ( 'El Codigo de la sucursal debe tener maximo 10 caracteres' ), 'error' );
		$Errors [$i] = 'BranchCode';
		$i ++;
	}
	
	if (strlen ( trim ( $_POST ['taxid'] ) ) > 0 and validaRFC ( $_POST ['taxid'] ) == 0) {
		$InputError = 1;
		prnMsg ( _ ( 'El RFC no tiene una estructura valida. ('.$_POST ['taxid'].')' ), 'error' );
		$Errors [$i] = 'taxid';
		if($_POST ['actualizar']==1){
			$_POST ['taxid']=$sucursal_actual['taxid'];
		}else{
			$_POST ['taxid']="";
		}
		$i ++;
	}
	if (strlen ( $_POST ['taxid'] ) == 0 and empty ( $_SESSION ['ForzarCapturaRFC'] ) == false) {
		$InputError = 1;
		prnMsg ( _ ( 'Capturar RFC de la Sucursal...' ), 'error' );
		$Errors [$i] = 'taxid';
		$i ++;
	}
	if (! is_numeric ( $_POST ['FwdDate'] )) {
		$InputError = 1;
		prnMsg ( _ ( 'La fecha a partir de la cual las facturas se pagan el siguiente mes se espera que sea un numero y un numero reconocido que no este actualmente disponible' ), 'error' );
		$Errors [$i] = 'FwdDate';
		$i ++;
	}
	if ($_POST ['FwdDate'] > 30) {
		$InputError = 1;
		prnMsg ( _ ( 'La fecha (en el mes) despues del cual las facturas se pagan al mes siguiente debe ser un numero inferior a 31' ), 'error' );
		$Errors [$i] = 'FwdDate';
		$i ++;
	}
	if (! is_numeric ( $_POST ['EstDeliveryDays'] )) {
		$InputError = 1;
		prnMsg ( _ ( 'Se espera que los dias de entrega estimados en un numero y un numero reconocido que no este actualmente disponible' ), 'error' );
		$Errors [$i] = 'EstDeliveryDays';
		$i ++;
	}
	if ($_POST ['EstDeliveryDays'] > 60) {
		$InputError = 1;
		prnMsg ( _ ( 'Los dias de entrega estimados deben ser un numero de d�as inferior a 60' ) . '. ' . _ ( 'Un paquete puede ser entregado por el transporte maritimo en todo el mundo, normalmente en menos de 60 dias' ), 'error' );
		$Errors [$i] = 'EstDeliveryDays';
		$i ++;
	}
	if (! isset ( $_POST ['EstDeliveryDays'] )) {
		$_POST ['EstDeliveryDays'] = 1;
	}
	if (! isset ( $latitude )) {
		$latitude = 0.0;
		$longitude = 0.0;
	}
	if ($_POST ['taller'] == true) {
		$_POST ['taller'] = 1;
	} else {
		$_POST ['taller'] = 0;
	}
	if ($_SESSION ['geocode_integration'] == 1) {
		// Get the lat/long from our geocoding host
		$sql = "SELECT * FROM geocode_param WHERE 1";
		$ErrMsg = _ ( 'Se ha producido un error al recuperar la informaci�n' );
		$resultgeo = DB_query ( $sql, $db, $ErrMsg );
		$row = DB_fetch_array ( $resultgeo );
		$api_key = $row ['geocode_key'];
		$map_host = $row ['map_host'];
		define ( "MAPS_HOST", $map_host );
		define ( "KEY", $api_key );
		if ($map_host == "") {
			// check that some sane values are setup already in geocode tables, if not skip the geocoding but add the record anyway.
			echo '<div class="warn">' . _ ( 'ADVERTENCIA - Integracion Geocode esta activada, pero no hay hosts configurados. Vaya a Configuracion Geocode' ) . '</div>';
		} else {
			
			$address = $_POST ["BrAddress1"] . ", " . $_POST ["BrAddress2"] . ", " . $_POST ["BrAddress3"] . ", " . $_POST ["BrAddress4"];
			
			$base_url = "http://" . MAPS_HOST . "/maps/geo?output=xml" . "&key=" . KEY;
			$request_url = $base_url . "&q=" . urlencode ( $address );
			$xml = simplexml_load_string ( utf8_encode ( file_get_contents ( $request_url ) ) ) or die ( "url not loading" );
			// $xml = simplexml_load_file($request_url) or die("url not loading");
			
			$coordinates = $xml->Response->Placemark->Point->coordinates;
			$coordinatesSplit = split ( ",", $coordinates );
			// Format: Longitude, Latitude, Altitude
			$latitude = $coordinatesSplit [1];
			$longitude = $coordinatesSplit [0];
			
			$status = $xml->Response->Status->code;
			if (strcmp ( $status, "200" ) == 0) {
				// Successful geocode
				$geocode_pending = false;
				$coordinates = $xml->Response->Placemark->Point->coordinates;
				$coordinatesSplit = split ( ",", $coordinates );
				// Format: Longitude, Latitude, Altitude
				$latitude = $coordinatesSplit [1];
				$longitude = $coordinatesSplit [0];
			} else {
				// failure to geocode
				$geocode_pending = false;
				echo '<div class="page_help_text"><b>Geocode Aviso:</b> Direcci�n: ' . $address . ' failed to geocode. ';
				echo 'Received status ' . $status . '</div>';
			}
		}
	}
	if (isset ( $SelectedBranch ) and  $SelectedBranch != "" and $InputError != 1) {
		
		/* SelectedBranch could also exist if submit had not been clicked this code would not run in this case cos submit is false of course see the 	delete code below */
		
		$sql = "UPDATE custbranch SET brname = '" . $_POST ['BrName'] . "',
						braddress1 = '" . $_POST ['BrAddress1'] . "',
						braddress2 = '" . $_POST ['BrAddress2'] . "',
						braddress3 = '" . $_POST ['BrAddress3'] . "',
						braddress4 = '" . $_POST ['BrAddress4'] . "',
						braddress5 = '" . $_POST ['BrAddress5'] . "',
						braddress6 = '" . $_POST ['BrAddress6'] . "',
						braddress7 = '" . $_POST ['BrAddress7'] . "',
						brnumext = '".$_POST['brnumext']."',
						brnumint = '".$_POST['brnumint']."',
						custpais = '" . $_POST ['BrAddress7'] . "',
						lat = '" . $latitude . "',
						lng = '" . $longitude . "',
						specialinstructions = '" . $_POST ['specialinstructions'] . "',
						phoneno='" . $_POST ['PhoneNo'] . "',
						faxno='" . $_POST ['FaxNo'] . "',
						fwddate= " . $_POST ['FwdDate'] . ",
						contactname='" . $_POST ['ContactName'] . "',
						salesman= '" . $_POST ['Salesman'] . "',
						area='" . $_POST ['Area'] . "',
						estdeliverydays =" . $_POST ['EstDeliveryDays'] . ",
						email='" . $_POST ['Email'] . "',
						taxgroupid=" . $_POST ['TaxGroup'] . ",
						defaultlocation='" . $_POST ['DefaultLocation'] . "',
						brpostaddr1 = '" . $_POST ['BrPostAddr1'] . "',
						brpostaddr2 = '" . $_POST ['BrPostAddr2'] . "',
						brpostaddr3 = '" . $_POST ['BrPostAddr3'] . "',
						brpostaddr4 = '" . $_POST ['BrPostAddr4'] . "',
						brpostaddr5 = '" . $_POST ['BrPostAddr5'] . "',
						brpostaddr6 = '" . $_POST ['BrPostAddr6'] . "',
						disabletrans=" . $_POST ['DisableTrans'] . ",
						defaultshipvia=" . $_POST ['DefaultShipVia'] . ",
						custbranchcode='" . $_POST ['CustBranchCode'] . "',
						deliverblind=" . $_POST ['DeliverBlind'] . ",
						custdata1='" . $_POST ['custdata1'] . "',
						custdata2='" . $_POST ['custdata2'] . "',
						custdata3='" . $_POST ['custdata3'] . "',
						custdata4='" . $_POST ['custdata4'] . "',
						custdata5='" . $_POST ['custdata5'] . "',
						custdata6='" . $_POST ['custdata6'] . "',
						taxid='" . $_POST ['taxid'] . "',
						lineofbusiness='" . htmlspecialchars_decode ( $_POST ['giro'], ENT_NOQUOTES ) . "',
						flagworkshop='" . htmlspecialchars_decode ( $_POST ['taller'], ENT_NOQUOTES ) . "',
						ruta='" . $_POST ['ruta'] . "',
						paymentname='" . $_POST ['metodoPago'] . "',
						nocuenta='" . $_POST ['numCuenta'] . "',
						typeaddenda='" . $_POST ['adenda'] . "',
						DiasRevicion = '" . $_POST ['DiasRevicion'] . "',
						DiasPago = '" . $_POST ['DiasPago'] . "'
					WHERE branchcode = '$SelectedBranch' AND debtorno='$DebtorNo'";
		// echo $sql;
		$msg = $_POST ['BrName'] . ' ' . _ ( 'branch has been updated.' );
		$BranchCodeenvia = $SelectedBranch;
	} else if ($InputError != 1) {
		
		/* Selected branch is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Customer Branches form */
		
		$sql = "INSERT INTO custbranch (branchcode,
						debtorno,
						brname,
						braddress1,
						braddress2,
						braddress3,
						braddress4,
						braddress5,
						braddress6,
        				braddress7,
						brnumext,
						brnumint,
        				custpais,
						lat,
						lng,
 						specialinstructions,
						estdeliverydays,
						fwddate,
						salesman,
						phoneno,
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
						lineofbusiness,
						flagworkshop,
						ruta,
        				paymentname, 
        				nocuenta,
        				typeaddenda,
        				DiasRevicion,
        				DiasPago  
						)
				VALUES ('" . str_replace("-",".",$_POST ['BranchCode'])  . "',
					'" . $DebtorNo . "',
					'" . $_POST ['BrName'] . "',
					'" . $_POST ['BrAddress1'] . "',
					'" . $_POST ['BrAddress2'] . "',
					'" . $_POST ['BrAddress3'] . "',
					'" . $_POST ['BrAddress4'] . "',
					'" . $_POST ['BrAddress5'] . "',
					'" . $_POST ['BrAddress6'] . "',
					'" . $_POST ['BrAddress7'] . "',
					'" . $_POST ['BrAddress7'] . "',
					'".$_POST['brnumext']."',
					'".$_POST['brnumint']."',
					'" . $latitude . "',
					'" . $longitude . "',
					'" . $_POST ['specialinstructions'] . "',
					" . $_POST ['EstDeliveryDays'] . ",
					" . $_POST ['FwdDate'] . ",
					'" . $_POST ['Salesman'] . "',
					'" . $_POST ['PhoneNo'] . "',
					'" . $_POST ['FaxNo'] . "',
					'" . $_POST ['ContactName'] . "',
					'" . $_POST ['Area'] . "',
					'" . $_POST ['Email'] . "',
					" . $_POST ['TaxGroup'] . ",
					'" . $_POST ['DefaultLocation'] . "',
					'" . $_POST ['BrPostAddr1'] . "',
					'" . $_POST ['BrPostAddr2'] . "',
					'" . $_POST ['BrPostAddr3'] . "',
					'" . $_POST ['BrPostAddr4'] . "',
					'" . $_POST ['BrPostAddr5'] . "',
					'" . $_POST ['BrPostAddr6'] . "',
					" . $_POST ['DisableTrans'] . ",
					'" . $_POST ['DefaultShipVia'] . "',
					'" . $_POST ['CustBranchCode'] . "',
					" . $_POST ['DeliverBlind'] . ",
					'" . $_POST ['taxid'] . "',
					'" . $_POST ['custdata1'] . "',
					'" . $_POST ['custdata2'] . "',
					'" . $_POST ['custdata3'] . "',
					'" . $_POST ['custdata4'] . "',
					'" . $_POST ['custdata5'] . "',
					'" . $_POST ['custdata6'] . "',
					'" . htmlspecialchars_decode ( $_POST ['giro'], ENT_NOQUOTES ) . "',
					'" . htmlspecialchars_decode ( $_POST ['taller'], ENT_NOQUOTES ) . "',
					'" . $_POST ['ruta'] . "',
					'" . $_POST ['metodoPago'] . "',
					'" . $_POST ['numCuenta'] . "',
					'" . $_POST ['adenda'] . "',
					'" . $_POST ['DiasRevicion'] . "',
					'" . $_POST ['DiasPago'] . "'
					)";
		$BranchCodeenvia = $_POST ['BranchCode'];
	}
	echo '<br>';
	$msg = _ ( 'La sucursal del cliente<b>' ) . ' ' . $_POST ['BranchCode'] . ': ' . $_POST ['BrName'] . ' ' . _ ( '</b>se ha agregado, agregar otra sucursal, o volver a <a href=index.php>menu principal</a>' );
	
	// run the SQL from either of the above possibilites
	
	$ErrMsg = _ ( 'El registro de la sucursal no se puede insertar o actualizar porque' );
	if ($InputError == 0) {
		if($codificacionJibe==1){
			DB_query("SET NAMES 'utf8';",$db);
		}
		$result = DB_query ( $sql, $db, $ErrMsg );
	}
	
	if (DB_error_no ( $db ) == 0 and $InputError == 0) {
		prnMsg ( $msg, 'Exito' );
		if ($_SESSION ['frompage'] == 'selectorderitems') {
			$_SESSION ['ExistingBranchCoder'] = $BranchCodeenvia;
			$BranchCode = $BranchCodeenvia;
			$_SESSION ['frompage'] = '';
			unset ( $_SESSION ['ExistingBranchCoder'] );
			if ($_SESSION ['Items' . $identifier]->OrderNo != 0) {
				echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/" . $SelectOrderItemsV5 . "?Select=" . $_POST ['DebtorNo'] . ' - ' . $BranchCode . '|Auto:' . $SelectedVehicle . "&identifier=" . $identifier . "'>";
				echo '<div class="centre">' . _ ( 'Tu deberas automaticamente  ser redireccionado a la pagina para dar de alta una Sucursal del Cliente' ) . '. ' . _ ( 'Si esto no sucede' ) . ' (' . _ ( 'Si tu explorador no soporta META Refresh' ) . ') ' . "<a href='" . $rootpath . "/" . $SelectOrderItemsV5 . "?" . SID . "&Select=" . $DebtorNo . ' - ' . $BranchCode . '.</div>';
				exit ();
			} else {
				echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/" . $SelectOrderItemsV5 . "?Select=" . $_POST ['DebtorNo'] . ' - ' . $BranchCode . '|Auto:' . $SelectedVehicle . "&identifier=" . $identifier . "'>";
				echo '<div class="centre">' . _ ( 'Tu deberas automaticamente  ser redireccionado a la pagina para dar de alta una Sucursal del Cliente' ) . '. ' . _ ( 'Si esto no sucede' ) . ' (' . _ ( 'Si tu explorador no soporta META Refresh' ) . ') ' . "<a href='" . $rootpath . "/" . $SelectOrderItemsV5 . "?" . SID . "NewOrder=Yes&Select=" . $DebtorNo . ' - ' . $BranchCode . '.</div>';
				exit ();
			}
		}
		
		unset ( $_POST ['BranchCode'] );
		unset ( $_POST ['BrName'] );
		unset ( $_POST ['BrAddress1'] );
		unset ( $_POST ['BrAddress2'] );
		unset ( $_POST ['BrAddress3'] );
		unset ( $_POST ['BrAddress4'] );
		unset ( $_POST ['BrAddress5'] );
		unset ( $_POST ['BrAddress6'] );
		unset ( $_POST ['BrAddress7'] );
		unset ( $_POST ['specialinstructions'] );
		unset ( $_POST ['EstDeliveryDays'] );
		unset ( $_POST ['FwdDate'] );
		unset ( $_POST ['Salesman'] );
		unset ( $_POST ['PhoneNo'] );
		unset ( $_POST ['FaxNo'] );
		unset ( $_POST ['ContactName'] );
		unset ( $_POST ['Area'] );
		unset ( $_POST ['Email'] );
		unset ( $_POST ['TaxGroup'] );
		unset ( $_POST ['DefaultLocation'] );
		unset ( $_POST ['DisableTrans'] );
		unset ( $_POST ['BrPostAddr1'] );
		unset ( $_POST ['BrPostAddr2'] );
		unset ( $_POST ['BrPostAddr3'] );
		unset ( $_POST ['BrPostAddr4'] );
		unset ( $_POST ['BrPostAddr5'] );
		unset ( $_POST ['BrPostAddr6'] );
		unset ( $_POST ['DefaultShipVia'] );
		unset ( $_POST ['CustBranchCode'] );
		unset ($_POST['brnumext']);
		unset ($_POST['brnumint']);
		unset ( $_POST ['DeliverBlind'] );
		unset ( $_POST ['custdata1'] );
		unset ( $_POST ['custdata2'] );
		unset ( $_POST ['custdata3'] );
		unset ( $_POST ['custdata4'] );
		unset ( $_POST ['custdata5'] );
		unset ( $_POST ['custdata6'] );
		unset ( $_POST ['taxid'] );
		unset ( $_POST ['ruta'] );
		unset ( $SelectedBranch );
		unset ( $_POST ['metodoPago'] );
		unset ( $_POST ['numCuenta'] );
		unset ( $_POST ['adenda'] );
	}
} else if (isset ( $_GET ['delete'] )) {
	// the link to delete a selected record was clicked instead of the submit button
	
	// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'
	
	$sql = "SELECT COUNT(*) FROM debtortrans WHERE debtortrans.branchcode='$SelectedBranch' AND debtorno = '$DebtorNo'";
	$result = DB_query ( $sql, $db );
	$myrow = DB_fetch_row ( $result );
	if ($myrow [0] > 0) {
		prnMsg ( _ ( 'No se puede eliminar esta sucursal ya que las transacciones de los clientes se han creado para esta sucursal' ) . '<br>' . _ ( 'Existen' ) . ' ' . $myrow [0] . ' ' . _ ( 'transacciones con este Codigo Sucursal' ), 'error' );
	} else {
		$sql = "SELECT COUNT(*) FROM salesanalysis WHERE salesanalysis.custbranch='$SelectedBranch' AND salesanalysis.cust = '$DebtorNo'";
		
		$result = DB_query ( $sql, $db );
		
		$myrow = DB_fetch_row ( $result );
		if ($myrow [0] > 0) {
			prnMsg ( _ ( 'No se puede eliminar esta sucursal porque existen registros de an�lisis de ventas' ), 'error' );
			echo '<br>' . _ ( 'Existen' ) . ' ' . $myrow [0] . ' ' . _ ( 'registros de an�lisis de ventas con este codigo de sucursal / cliente' );
		} else {
			
			$sql = "SELECT COUNT(*) FROM salesorders WHERE salesorders.branchcode='$SelectedBranch' AND salesorders.debtorno = '$DebtorNo'";
			$result = DB_query ( $sql, $db );
			
			$myrow = DB_fetch_row ( $result );
			if ($myrow [0] > 0) {
				prnMsg ( _ ( 'No se puede eliminar esta sucursal porque existen ordenes de venta' ) . '. ' . _ ( 'Ordenes de venta Purge antiguos primero' ), 'warn' );
				echo '<br>' . _ ( 'Existen' ) . ' ' . $myrow [0] . ' ' . _ ( 'Ordenes de venta para esta sucursal / cliente' );
			} else {
				// Check if there are any users that refer to this branch code
				$sql = "SELECT COUNT(*) FROM www_users WHERE www_users.branchcode='$SelectedBranch' AND www_users.customerid = '$DebtorNo'";
				
				$result = DB_query ( $sql, $db );
				$myrow = DB_fetch_row ( $result );
				
				if ($myrow [0] > 0) {
					prnMsg ( _ ( 'No se puede eliminar esta sucursal, puesto que existen usuarios que se refieren a ella' ) . '. ' . _ ( 'Purge las mayores primero' ), 'warn' );
					echo '<br>' . _ ( 'Existen' ) . ' ' . $myrow [0] . ' ' . _ ( 'los usuarios se refieren a esta Sucursal / cliente' );
				} else {
					
					$sql = "DELETE FROM custbranch WHERE branchcode='" . $SelectedBranch . "' AND debtorno='" . $DebtorNo . "'";
					$ErrMsg = _ ( 'El registro de sucursal no se pudo eliminar' ) . ' - ' . _ ( 'el servidor SQL ha devuelto el siguiente mensaje' );
					$result = DB_query ( $sql, $db, $ErrMsg );
					if (DB_error_no ( $db ) == 0) {
						prnMsg ( _ ( 'Sucursal Borrada' ), 'success' );
					}
				}
			}
		}
	} // end ifs to test if the branch can be deleted
}
if (! isset ( $SelectedBranch )) {
	
	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedBranch will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then none of the above are true and the list of branches will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records */
	
	$sql = "SELECT debtorsmaster.name,
			custbranch.branchcode,
			brname,
			salesman.salesmanname,
			areas.areadescription,
			contactname,
			phoneno,
			faxno,
			email,
			taxgroups.taxgroupdescription,
			custbranch.branchcode,
			custbranch.disabletrans,
			rutas.ruta,
			custbranch.braddress1,
			custbranch.braddress2,
			custbranch.braddress3,
			custbranch.braddress4,
			custbranch.braddress5,
			custbranch.braddress6,
			custbranch.brpostaddr1,
			custbranch.brpostaddr2,
			custbranch.brpostaddr3,
			custbranch.brpostaddr4,
			custbranch.brpostaddr5,
			custbranch.paymentname,
			custbranch.nocuenta,
			custbranch.typeaddenda
		FROM debtorsmaster,
			areas,
			taxgroups,
			custbranch left join salesman ON custbranch.salesman=salesman.salesmancode
			left join rutas ON rutas.rutaid=custbranch.ruta
		WHERE custbranch.debtorno=debtorsmaster.debtorno
		AND custbranch.area=areas.areacode
		AND custbranch.taxgroupid=taxgroups.taxgroupid
		AND custbranch.debtorno = '" . $DebtorNo . "'";
	// echo $sql;
	if($codificacionJibe==1){
		DB_query("SET NAMES 'utf8';",$db);
	}
	$result = DB_query ( $sql, $db );
	$myrow = DB_fetch_array ( $result );
	$TotalEnable = 0;
	$TotalDisable = 0;
	
	if ($myrow) {
		echo '<p Class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/customer.png" title="' . _ ( 'Customer' ) . '" alt="">' . ' ' . _ ( 'Sucursales definidas para: ' ) . ' <span style=font-weight:normal;>' . $DebtorNo . ' - ' . $myrow [0] . '</span></p>';
		echo '<table border=1>';
		echo "<tr><th>" . _ ( 'Codigo' ) . "</th>
			<th>" . _ ( 'Sucursal' ) . "</th>
			<th>" . _ ( 'Contacto' ) . "</th>
			<th>" . _ ( 'Vendedor' ) . "</th>
			<th>" . _ ( 'Area' ) . "</th>";

		if($codificacionJibe==1){
			echo "<th>" . regexDecode( 'Teléfono' ) . "</th>";
		}else{
			echo "<th>" . utf8_decode( 'Teléfono' ) . "</th>";
		}
		echo "<th>" . _ ( 'Fax' ) . "</th>
			<th>" . _ ( 'Email' ) . "</th>
			<th>" . _ ( 'Direccion Sucursal' ) . "</th>
			<th>" . _ ( 'Direccion Entrega' ) . "</th>
			<th>" . _ ( 'Grupo de Impuestos' ) . "</th>
			<th>" . _ ( 'Habilitado?' ) . "</th>
			<th>" . _ ( 'Ruta' ) . "</th>
			<th></th>
			<th></th></tr>";
		
		do {
			
			$custAddressArray = array ();
			for($i = 1; $i <= 5; $i ++) {
				if (empty ( $myrow ["braddress$i"] ) == FALSE) {
					$custAddressArray [] = strtoupper ( $myrow ["braddress$i"] );
				}
			}
			$custAddress = implode ( ', ', $custAddressArray );
			/*
			 * if(empty($custAddress) == FALSE) { $custAddress = _('DIRECCION') . ': ' . $custAddress; }
			 */
			$custproAddresArray = array ();
			for($i = 1; $i <= 5; $i ++) {
				if (empty ( $myrow ["brpostaddr$i"] ) == FALSE) {
					$custproAddresArray [] = strtoupper ( $myrow ["brpostaddr$i"] );
				}
			}
			$custproAddress = implode ( ', ', $custproAddresArray );
			printf ( "<tr><td><font size=2>%s</td>
			       <td><font size=2>%s</td>
				<td><font size=2>%s</font></td>
				<td><font size=2>%s</font></td>
				<td><font size=2>%s</font></td>
				<td><font size=2>%s</font></td>
				<td><font size=2>%s</font></td>
				<td><font size=2><a href='Mailto:%s'>%s</a></font></td>
				<td><font size=2>%s</font></td>
				<td><font size=2>%s</font></td>
				<td><font size=2>%s</font></td>
				<td><font size=2>%s</td>
				<td><font size=2>%s</td>
				<td><font size=2><a href='%s?DebtorNo=%s&SelectedBranch=%s'>%s</font></td>
				<td><font size=2><a href='%s?DebtorNo=%s&SelectedBranch=%s&delete=yes' onclick=\"return confirm('" . _ ( 'Esta seguro de que desea eliminar esta sucursal?' ) . "');\">%s</font></td></tr>", $myrow [10], $myrow [2], $myrow [5], $myrow [3], $myrow [4], $myrow [6], $myrow [7], $myrow [8], $myrow [8], $custAddress, $custproAddress, $myrow [9], ($myrow [11] ? _ ( 'No' ) : _ ( 'Si' )), $myrow [12], $_SERVER ['PHP_SELF'], $DebtorNo, urlencode ( $myrow [1] ), _ ( 'Editar' ), $_SERVER ['PHP_SELF'], $DebtorNo, urlencode ( $myrow [1] ), _ ( 'Eliminar' ) );
			if ($myrow [11]) {
				$TotalDisable ++;
			} else {
				$TotalEnable ++;
			}
		} while ( $myrow = DB_fetch_array ( $result ) );
		// END WHILE LIST LOOP
		echo '</table>';
		echo '<table border=0 width=70%><tr>';
		echo '<td><b>' . $TotalEnable . '</b> ' . _ ( 'Sucursales Habilitadas.' ) . '</td>';
		echo '<td><b>' . $TotalDisable . '</b> ' . _ ( 'Sucursales no Habilitadas.' ) . '</td>';
		echo '<td><b>' . ($TotalEnable + $TotalDisable) . '</b> ' . _ ( 'Total Sucursales' ) . '</td>';
		echo '</tr></table>';
	} else {
		$sql = "SELECT brname,
				braddress1,
				braddress2,
				braddress3,
				braddress4,
				braddress5,
				braddress6,
				braddress7,
				brnumext,
				brnumint,
				braddress7,
				brpostaddr1,
				brpostaddr2,
				brpostaddr3,
				brpostaddr4,
				brpostaddr5,
				brpostaddr6,
				DiasRevicion,
				DiasPago
			FROM custbranch
			WHERE debtorno = '" . $DebtorNo . "'
			And branchcode  = '" . $DebtorNo . "'";
		if($codificacionJibe==1){
			DB_query("SET NAMES 'utf8';",$db);
		}
		$result = DB_query ( $sql, $db );
		$myrow = DB_fetch_row ( $result );
		echo '<br><div class="page_help_text">' . _ ( 'Sucursales definidas para' ) . ' - ' . $myrow [0] . '. ' . _ ( 'Usted debe tener un minimo de una sucursal para cada cliente. Por favor, agregue una sucursal ahora.' ) . '</div>';
		$_POST ['BranchCode'] = substr ( $DebtorNo, 0, 10 );
		$_POST ['BrName'] = $myrow ['brname'];
		$_POST ['BrAddress1'] = $myrow ['braddress1'];
		$_POST ['BrAddress2'] = $myrow ['braddress2'];
		$_POST ['BrAddress3'] = $myrow ['braddress3'];
		$_POST ['BrAddress4'] = $myrow ['braddress4'];
		$_POST ['BrAddress5'] = $myrow ['braddress5'];
		$_POST ['BrAddress6'] = $myrow ['braddress6'];
		$_POST ['BrAddress7'] = $myrow ['braddress7'];
		
		$_POST ['BrPostAddr1'] = $myrow ['brpostaddr1'];
		$_POST ['BrPostAddr2'] = $myrow ['brpostaddr2'];
		$_POST ['BrPostAddr3'] = $myrow ['brpostaddr3'];
		$_POST ['BrPostAddr4'] = $myrow ['brpostaddr4'];
		$_POST ['BrPostAddr5'] = $myrow ['brpostaddr5'];
		$_POST ['BrPostAddr6'] = $myrow ['brpostaddr6'];
		$_POST ['DiasRevicion'] = $myrow ['DiasRevicion'];
		$_POST ['DiasPago'] = $myrow ['DiasPago'];

		$_POST ['brnumext'] = $myrow ['brnumext'];
		$_POST ['brnumint'] = $myrow ['brnumint'];
		unset ( $myrow );
	}
}

// end of ifs and buts!

if (isset ( $SelectedBranch )) {
	echo '<div class="centre"><a href=' . $_SERVER ['PHP_SELF'] . '?' . SID . 'DebtorNo=' . $DebtorNo . '>' . _ ( 'Mostrar las sucursales para: ' ) . ' ' . $DebtorNo . '</a></div>';
}
echo '<br>';

if (! isset ( $_GET ['delete'] )) {
	
	echo "<form method='post' action=" . $_SERVER ['PHP_SELF'] . '?' . SID . '>';
	
	if (isset ( $SelectedBranch )) {
		// editing an existing branch
		
		$sql = "SELECT branchcode,
				brname,
				braddress1,
				braddress2,
				braddress3,
				braddress4,
				braddress5,
				braddress6,
	            braddress7,
	            brnumext,
	            brnumint,
	            specialinstructions,
				estdeliverydays,
				fwddate,
				salesman,
				area,
				phoneno,
				faxno,
				contactname,
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
				ruta,
				lineofbusiness,
				flagworkshop,
				paymentname,
				nocuenta,
				typeaddenda,
				DiasRevicion,
				DiasPago,
				custpais

			FROM custbranch
			WHERE branchcode='$SelectedBranch'
			AND debtorno='$DebtorNo'";
		if($codificacionJibe==1){
			DB_query("SET NAMES 'utf8';",$db);
		}
		$result = DB_query ( $sql, $db );
		$myrow = DB_fetch_array ( $result );
		if($codificacionJibe==1){
			if($codificacionJibe==1){
				DB_query("SET NAMES 'utf8';",$db);
			}
		}
		if ($InputError == 0) {
			$_POST ['BranchCode'] = $myrow ['branchcode'];
			$_POST ['BrName'] = $myrow ['brname'];
			$_POST ['BrAddress1'] = $myrow ['braddress1'];
			$_POST ['BrAddress2'] = $myrow ['braddress2'];
			$_POST ['BrAddress3'] = $myrow ['braddress3'];
			$_POST ['BrAddress4'] = $myrow ['braddress4'];
			$_POST ['BrAddress5'] = $myrow ['braddress5'];
			$_POST ['BrAddress6'] = $myrow ['braddress6'];
			$_POST ['BrAddress7'] = $myrow ['braddress7'];
			$_POST ['specialinstructions'] = $myrow ['specialinstructions'];
			$_POST ['BrPostAddr1'] = $myrow ['brpostaddr1'];
			$_POST ['BrPostAddr2'] = $myrow ['brpostaddr2'];
			$_POST ['BrPostAddr3'] = $myrow ['brpostaddr3'];
			$_POST ['BrPostAddr4'] = $myrow ['brpostaddr4'];
			$_POST ['BrPostAddr5'] = $myrow ['brpostaddr5'];
			$_POST ['BrPostAddr6'] = $myrow ['brpostaddr6'];
			$_POST ['EstDeliveryDays'] = $myrow ['estdeliverydays'];
			$_POST ['FwdDate'] = $myrow ['fwddate'];
			$_POST ['ContactName'] = $myrow ['contactname'];
			$_POST ['Salesman'] = $myrow ['salesman'];
			$_POST ['Area'] = $myrow ['area'];
			$_POST ['PhoneNo'] = $myrow ['phoneno'];
			$_POST ['FaxNo'] = $myrow ['faxno'];
			$_POST ['Email'] = $myrow ['email'];
			$_POST ['TaxGroup'] = $myrow ['taxgroupid'];
			$_POST ['DisableTrans'] = $myrow ['disabletrans'];
			$_POST ['DefaultLocation'] = $myrow ['defaultlocation'];
			$_POST ['DefaultShipVia'] = $myrow ['defaultshipvia'];
			$_POST ['CustBranchCode'] = $myrow ['custbranchcode'];
			$_POST ['DeliverBlind'] = $myrow ['deliverblind'];
			$_POST ['taxid'] = $myrow ['taxid'];
			$_POST ['custdata1'] = $myrow ['custdata1'];
			$_POST ['custdata2'] = $myrow ['custdata2'];
			$_POST ['custdata3'] = $myrow ['custdata3'];
			$_POST ['custdata4'] = $myrow ['custdata4'];
			$_POST ['custdata5'] = $myrow ['custdata5'];
			$_POST ['custdata6'] = $myrow ['custdata6'];
			$_POST ['ruta'] = $myrow ['ruta'];
			$_POST ['giro'] = $myrow ['lineofbusiness'];
			$_POST ['taller'] = $myrow ['flagworkshop'];
			$_POST ['metodoPago'] = $myrow ['paymentname'];
			$_POST ['numCuenta'] = $myrow ['nocuenta'];
			$_POST ['adenda'] = $myrow ['typeaddenda'];
			$_POST ['DiasRevicion'] = $myrow ['DiasRevicion'];
			$_POST ['DiasPago'] = $myrow ['DiasPago'];
			$_POST ['brnumext'] = $myrow ['brnumext'];
			$_POST ['brnumint'] = $myrow ['brnumint'];
			$_POST['custpais'] = $myrow ['custpais'];
			
		}
		
		echo "<input type=hidden name='SelectedBranch' VALUE='" . $SelectedBranch . "'>";
		echo '<input type="hidden" name="SelectedVehicle" value="' . $SelectedVehicle . '">';
		echo "<input type=hidden name='BranchCode'  VALUE='" . $_POST ['BranchCode'] . "'>";
		echo "<div class='centre'><b>" . _ ( 'Modificar Datos Sucursal' ) . "</b><br>";
		echo "<table border=1 width=100%><tr><td><table border=0 width=100%> <tr><td>" . _ ( 'Codigo Sucursal' ) . ':</td><td>' . $_POST ['BranchCode'] . '</td></tr>';
	} elseif (isset ( $_GET ['add_branch'] )) {
		
		// if (isset($SelectedBranch)) {
		// editing an existing branch
		
		$sql = "SELECT branchcode,
			brname,
			braddress1,
			braddress2,
			braddress3,
			braddress4,
			braddress5,
			braddress6,
			braddress7,
			specialinstructions,
			estdeliverydays,
			fwddate,
			salesman,
			area,
			phoneno,
			faxno,
			contactname,
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
			brnumext,
			brnumint,
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
			ruta,
			lineofbusiness,
			flagworkshop,
			paymentname,
			nocuenta,
			typeaddenda,
			DiasRevicion,
			DiasPago,
			custpais
			FROM custbranch
			WHERE branchcode='$DebtorNo'
			AND debtorno='$DebtorNo'";

		if($codificacionJibe==1){
			DB_query("SET NAMES 'utf8';",$db);
		}
		$result = DB_query ( $sql, $db );
		$myrow = DB_fetch_array ( $result );
		
		if ($InputError == 0) {
			// $_POST['BranchCode'] = $myrow['branchcode'];
			$_POST ['BrName'] = $myrow ['brname'];
			$_POST ['BrAddress1'] = $myrow ['braddress1'];
			$_POST ['BrAddress2'] = $myrow ['braddress2'];
			$_POST ['BrAddress3'] = $myrow ['braddress3'];
			$_POST ['BrAddress4'] = $myrow ['braddress4'];
			$_POST ['BrAddress5'] = $myrow ['braddress5'];
			$_POST ['BrAddress6'] = $myrow ['braddress6'];
			$_POST ['BrAddress7'] = $myrow ['braddress7'];
			$_POST ['specialinstructions'] = $myrow ['specialinstructions'];
			$_POST ['BrPostAddr1'] = $myrow ['brpostaddr1'];
			$_POST ['BrPostAddr1'] = $myrow ['brpostaddr1'];
			$_POST ['BrPostAddr2'] = $myrow ['brpostaddr2'];
			$_POST ['BrPostAddr3'] = $myrow ['brpostaddr3'];
			$_POST ['BrPostAddr4'] = $myrow ['brpostaddr4'];
			$_POST ['BrPostAddr5'] = $myrow ['brpostaddr5'];
			$_POST ['BrPostAddr6'] = $myrow ['brpostaddr6'];
			$_POST ['EstDeliveryDays'] = $myrow ['estdeliverydays'];
			$_POST ['FwdDate'] = $myrow ['fwddate'];
			$_POST ['ContactName'] = $myrow ['contactname'];
			$_POST ['Salesman'] = $myrow ['salesman'];
			$_POST ['Area'] = $myrow ['area'];
			$_POST ['PhoneNo'] = $myrow ['phoneno'];
			$_POST ['FaxNo'] = $myrow ['faxno'];
			$_POST ['Email'] = $myrow ['email'];
			$_POST ['TaxGroup'] = $myrow ['taxgroupid'];
			$_POST ['DisableTrans'] = $myrow ['disabletrans'];
			$_POST ['DefaultLocation'] = $myrow ['defaultlocation'];
			$_POST ['DefaultShipVia'] = $myrow ['defaultshipvia'];
			$_POST ['CustBranchCode'] = $myrow ['custbranchcode'];
			$_POST ['DeliverBlind'] = $myrow ['deliverblind'];
			$_POST ['taxid'] = $myrow ['taxid'];
			$_POST ['custdata1'] = $myrow ['custdata1'];
			$_POST ['custdata2'] = $myrow ['custdata2'];
			$_POST ['custdata3'] = $myrow ['custdata3'];
			$_POST ['custdata4'] = $myrow ['custdata4'];
			$_POST ['custdata5'] = $myrow ['custdata5'];
			$_POST ['custdata6'] = $myrow ['custdata6'];
			$_POST ['ruta'] = $myrow ['ruta'];
			$_POST ['giro'] = $myrow ['lineofbusiness'];
			$_POST ['taller'] = $myrow ['flagworkshop'];
			$_POST ['metodoPago'] = $myrow ['paymentname'];
			$_POST ['numCuenta'] = $myrow ['nocuenta'];
			$_POST ['adenda'] = $myrow ['typeaddenda'];
			$_POST ['DiasRevicion'] = $myrow ['DiasRevicion'];
			$_POST ['DiasPago'] = $myrow ['DiasPago'];
			$_POST ['brnumext'] = $myrow ['brnumext'];
			$_POST ['brnumint'] = $myrow ['brnumint'];
			$_POST ['custpais'] = $myrow ['custpais'];
		}
		
		// echo "<input type=hidden name='SelectedBranch' VALUE='" . $SelectedBranch . "'>";
		echo '<input type="hidden" name="SelectedVehicle" value="' . $SelectedVehicle . '">';
		// echo "<input type=hidden name='BranchCode' VALUE='" . $_POST['BranchCode'] . "'>";
		echo "<div class='centre'><b>" . _ ( 'Agregar Datos Sucursal' ) . "</b><br>";
		// echo "<table border=1 width=100%><tr><td><table border=0 width=100%> <tr><td>"._('Branch Code').':</td><td>' . $_POST['BranchCode'] . '</td></tr>';
		echo "<table border=1 width=100%><tr><td><table border=0 width=100%> <tr><td>" . _ ( 'Codigo Sucursal' ) . ':</td>';
		echo "<td><input type=text name='BranchCode' maxlenght='10' VALUE='" . $_POST ['BranchCode'] . "'><p style='color:red font-size:9px'>*Maximo 10 caracteres. </p>";
		echo '<input type="hidden" name="add_branch" value="' . $_GET ['add_branch'] . '"></td></tr>';
		
		// echo '<td>' . $_POST['BranchCode'] . '</td></tr>';
		
		// }
	} else { // end of if $SelectedBranch only do the else when a new record is being entered
		
		/*
		 * SETUP ANY $_GET VALUES THAT ARE PASSED. This really is just used coming from the Customers.php when a new customer is created. Maybe should only do this when that page is the referrer?
		 */
		if (isset ( $_GET ['BranchCode'] )) {
			$sql = "SELECT name,
					address1,
					address2,
					address3,
					address4,
					address5,
					address6,
					DiasRevicion,
					DiasPago
					FROM
					debtorsmaster
					WHERE debtorno='" . $_GET ['BranchCode'] . "'";
			if($codificacionJibe==1){
				DB_query("SET NAMES 'utf8';",$db);
			}
			$result = DB_query ( $sql, $db );
			$myrow = DB_fetch_array ( $result );
			$_POST ['BranchCode'] = $_GET ['BranchCode'];
			$_POST ['BrName'] = $myrow ['name'];
			$_POST ['BrAddress1'] = $myrow ['address1'];
			$_POST ['BrAddress2'] = $myrow ['address2'];
			$_POST ['BrAddress3'] = $myrow ['address3'];
			$_POST ['BrAddress4'] = $myrow ['address4'];
			$_POST ['BrAddress5'] = $myrow ['address5'];
			$_POST ['BrAddress6'] = $myrow ['address6'];
			$_POST ['BrAddress7'] = $myrow ['address7'];
			$_POST ['DiasRevicion'] = $myrow ['DiasRevicion'];
			$_POST ['DiasPago'] = $myrow ['DiasPago'];
		}
		if (! isset ( $_POST ['BranchCode'] )) {
			$_POST ['BranchCode'] = '';
		}
		echo '<p Class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/customer.png" title="' . _ ( 'Cliente' ) . '" alt="">' . ' ' . _ ( 'Agregar Sucursal' ) . '</p>';
		echo '<table border=1 width=100%>
				<tr><td>
				<table width=100%>
					<tr align="left">
						<td>' . _ ( 'Codigo Sucursal' ) . ':</td>
						<td>
							<input ' . (in_array ( 'BranchCode', $Errors ) ? 'class="inputerror"' : '') . " tabindex=1 type='Text' name='BranchCode' size=10 maxlength=10 value=" . $_POST ['BranchCode'] . '>
							<font color="red" size="1">*Maximo 10 caracteres.</font>
						</td>
					</tr>';
		$_POST ['DeliverBlind'] = $_SESSION ['DefaultBlindPackNote'];
	}
	
	// SQL to poulate account selection boxes
	$sql = "SELECT salesmanname, salesmancode FROM salesman where type=1";
	if($codificacionJibe==1){
		DB_query("SET NAMES 'utf8';",$db);
	}
	$result = DB_query ( $sql, $db );
	
	if (DB_num_rows ( $result ) == 0) {
		echo '</table>';
		prnMsg ( _ ( 'No hay personal de ventas definidos a�n' ) . ' - ' . _ ( 'sucursales de los clientes deben ser asignadas a un vendedor' ) . '. ' . _ ( 'Utilice el siguiente enlace para definir al menos un vendedor' ), 'error' );
		echo "<br><a href='$rootpath/SalesPeople.php?" . SID . "'>" . _ ( 'Definir personas Ventas' ) . '</a>';
		include ('includes/footer.inc');
		exit ();
	}
	
	echo '<input type=hidden name="DebtorNo" value="' . $DebtorNo . '">';
	if(isset($_GET['from'])){
		
            echo '<tr><td>' . _ ( 'Sucursal' ) . ':</td>';
            if (! isset ( $_POST ['BrName'] )) {
                $_POST ['BrName'] = '';
            }
            echo '<td>';
            echo '<input tabindex=2 type="hidden" name="BrName" size=41 maxlength=100 value="' . $_POST ['BrName'] . '">';
            echo $_POST ['BrName'];
            echo'</td></tr>';
           
            echo '<tr><td>' . _ ( 'RFC' ) . ':</td>';
            if (! isset ( $_POST ['taxid'] )) {
				$_POST ['taxid'] = '';
            }
            echo '<td>';
            echo '<input tabindex=4 type="hidden" name="taxid" size=15 maxlength=15 value="' . $_POST ['taxid'] . '">';
            echo $_POST ['taxid']; 
            echo'</td></tr>';
            $dirsepomex = '<img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _ ( 'Direcciones Sepomex' ) . '" alt=""> ';
            $trabsel = 1;
            $dirsepomex = $dirsepomex . "<a style='display:inline' href='#' onclick='javascript:var win = window.open(\"SepomexSearch_branchcode.php?idOpener=$trabsel\", \"sepomex\", \"width=500,height=500,scrollbars=1,left=200,top=150\"); win.focus();'>" . _ ( "Seleccionar direccion" ) . "</a>";
	
            echo '<tr><td>' . _ ( 'Calle' ) . ':</td>';
            if (! isset ( $_POST ['BrAddress1'] )) {
				$_POST ['BrAddress1'] = '';
            }
            echo '<td>';
            echo '<input tabindex=5 type="hidden" id="BrAddress1" name="BrAddress1" size=41 maxlength=150 value="' . $_POST ['BrAddress1'] . '">'; 
            echo $_POST ['BrAddress1'];
            echo'</td></tr>';

            //----------------------
            echo '<tr><td>' . _ ( 'Num Ext' ) . ':</td>';
            if (! isset ( $_POST ['brnumext'] )) {
				$_POST ['brnumext'] = '';
            }
            echo '<td><input tabindex=5 type="Text" id="brnumext" name="brnumext" size=22 maxlength=50 value="con' . $_POST ['brnumext'] . '"></td></tr>';
            echo '<tr><td>' . _ ( 'Num Int' ) . ':</td>';
            if (! isset ( $_POST ['brnumint'] )) {
				$_POST ['brnumint'] = '';
            }
            echo '<td><input tabindex=5 type="Text" id="brnumint" name="brnumint" size=22 maxlength=50 value="' . $_POST ['brnumint'] . '"></td></tr>';
            //----------------------
            
            echo '<tr><td>' . _ ( 'Colonia' ) . ':</td>';
            if (! isset ( $_POST ['BrAddress6'] )) {
				$_POST ['BrAddress6'] = '';
            }
            echo '<td>';
            echo '<input tabindex=10 type="hidden" id="BrAddress6" name="BrAddress6" size=41 maxlength=100 value="' . $_POST ['BrAddress6'] . '">';
            echo $_POST ['BrAddress6'];
            echo'</td></tr>';
            echo '<tr><td>' . _ ( 'Ciudad' ) . ':</td>';
            if (! isset ( $_POST ['BrAddress2'] )) {
				$_POST ['BrAddress2'] = '';
            }
            echo '<td>';
            echo '<input tabindex=6 type="hidden" id="BrAddress2" name="BrAddress2" size=41 maxlength=80 value="' . $_POST ['BrAddress2'] . '">';
            echo $_POST ['BrAddress2'];
            echo'</td></tr>';
            echo '<tr><td>' . _ ( 'Estado' ) . ':</td>';
            if (! isset ( $_POST ['BrAddress3'] )) {
				$_POST ['BrAddress3'] = '';
            }
            echo "<td><input type='hidden' name='BrAddress3' id='BrAddress3' size=41 maxlength=41 value='".$_POST ['BrAddress3']."'>";
            echo $_POST ['BrAddress3']."</td>";
            /*echo '<td><select name="BrAddress3" >';
	
            $qry = "Select * FROM states";
            $rss = DB_query ( $qry, $db );
            while ( $rows = DB_fetch_array ( $rss ) ) {
			if ($_POST ['BrAddress3'] == $rows ['state'])
	                    echo "<option selected value='" . $rows ['state'] . "'>" . $rows ['state'] . "</option>";
			else
	                    echo "<option value='" . $rows ['state'] . "'>" . $rows ['state'] . "</option>";
	            }
	
            echo '</select></td>';*/
            echo'</tr>';
            echo '<tr><td>' . _ ( 'Pais' ) . ':</td>';
            if (! isset ( $_POST ['BrAddress7'] )) {
				$_POST ['BrAddress7'] = '';
            }

            if ($_POST ['BrAddress7'] == '' and $_POST ['custpais'] != ''){
            	$_POST ['BrAddress7'] = $_POST ['custpais'];	
            }
            echo '<td>';
            echo '<input tabindex=8 type="hidden" name="BrAddress7" size=31 maxlength=100 value="' . utf8_decode($_POST ['BrAddress7']) . '">';
            echo $_POST ['BrAddress7'];
            echo'</td></tr>';
            echo '<tr><td>' . _ ( 'Codigo Postal' ) . ':</td>';
            if (! isset ( $_POST ['BrAddress4'] )) {
				$_POST ['BrAddress4'] = '';
            }
            echo '<td>';
            echo '<input tabindex=8 type="hidden" id="BrAddress4" name="BrAddress4" size=31 maxlength=50 value="' . $_POST ['BrAddress4'] . '">';
            echo $_POST ['BrAddress4'];
            echo'</td></tr>';
            echo '<tr><td>' . _ ( 'Direccion 5' ) . ':</td>';
            if (! isset ( $_POST ['BrAddress5'] )) {
				$_POST ['BrAddress5'] = '';
            }
            echo '<td>';
            echo '<input tabindex=9 type="hidden" name="BrAddress5" size=21 maxlength=20 value="' . $_POST ['BrAddress5'] . '">';
            echo $_POST ['BrAddress5'];
            echo'</td></tr>';
            echo '<tr><td>' . _ ( 'Giro de Cliente' ) . ':</td>';
            if (! isset ( $_POST ['giro'] )) {
				$_POST ['giro'] = '';
            }
            echo '<td>';
            echo '<input tabindex=18 type="hidden" name="giro" size=35 value="' . $_POST ['giro'] . '">';
            echo $_POST ['giro'];
            echo'</td></tr>';
            
            $sql = "SELECT * from paymentmethods order by paymentname";
            if($codificacionJibe==1){
				DB_query("SET NAMES 'utf8';",$db);
			}
            $result = DB_query ( $sql, $db, $ErrMsg, $DbgMsg, true );
            echo '<tr><td>' . _ ( 'Formas de Pago' ) . ':</td>';
            echo "<td><input type='hidden' name='metodoPago' id='metodoPago' value='".$_POST['metodoPago']."' >".$_POST['metodoPago']."</td>";
	        /*echo '<td>
			  <select style="width:200px" name="metodoPago" id="metodoPago">';
	            while ( $myrow = DB_fetch_array ( $result ) ) {
			if ($myrow ['paymentname'] == $_POST ['metodoPago']) {
				echo '<option selected VALUE="';
			} else {
				echo '<option VALUE="';
			}
			echo $myrow ['paymentname'] . '">' . $myrow ['paymentname'];
	            } // end while loop
	            echo '</select></td>';*/
            echo'</tr>';
	
            echo '<tr><td>' . _ ( 'Numero de Cuenta' ) . ':</td>';
            if (! isset ( $_POST ['numCuenta'] )) {
				$_POST ['numCuenta'] = '';
            }
            echo '<td>';
            echo '<input tabindex=18 type="hidden" name="numCuenta" size=35 value="' . $_POST ['numCuenta'] . '">';
            echo $_POST ['numCuenta'];
            echo'</td></tr>';
            echo '<tr><td>' . _ ( 'Addenda' ) . ':</td>';
            
            if (! isset ( $_POST ['adenda'] )) {
				$_POST ['adenda'] = '';
            }
            
            $addenda= $_POST ['adenda'];
            if(empty($_POST ['adenda']) == false){
                $qryad = "SELECT *
							FROM typeaddenda
							WHERE id_addenda = '".$_POST ['adenda']."'";
            	if($codificacionJibe==1){
					DB_query("SET NAMES 'utf8';",$db);
				}
                $radd = DB_query ( $qryad, $db );

                //echo '<option selected VALUE=0>' . _ ( 'Ninguna ' ) . '</option>';
                while ( $rowadd = DB_fetch_array ( $radd ) ) {
                    $addenda = $rowadd ['nameaddenda'];
                } 
            }
           
            echo "<td><input type='hidden' name='adenda' id='adenda' value='".$addenda."'>".$addenda."</td>";
            /*echo '<td><input tabindex=18 type="hidden" name="adenda1" size=35 value="' . $_POST ['adenda'] . '">
			<select name="adenda">';
            $qryad = "select * from typeaddenda";
            $radd = DB_query ( $qryad, $db );
            echo '<option selected VALUE=0>' . _ ( 'Ninguna ' ) . '</option>';
            while ( $rowadd = DB_fetch_array ( $radd ) ) {
				if ($rowadd ['id_addenda'] == '0') {
		                    echo "<option selected value='0'>" . _ ( 'Seleccionar Adenda' ) . "</option>";
				} elseif ($_POST ['adenda'] == $rowadd ['id_addenda'] && $rowadd ['id_addenda'] != '0') {
		                    echo "<option selected value='" . $rowadd ['id_addenda'] . "'>" . $rowadd ['nameaddenda'] . "</option>";
				} else
                    echo "<option value='" . $rowadd ['id_addenda'] . "'>" . $rowadd ['nameaddenda'] . "</option>";
            }
            echo '</select>';
            echo'</td>';*/
            echo '</tr>';
	
            // cambios
            echo '<tr><td>' . _ ( 'Cuenta con Taller ' ) . ':</td>';
            // if (!isset($_POST['taller'])) {$_POST['taller']='';}else{$_POST['taller']="checked";}
            if ($_POST ['taller'] == 1) {
				$_POST ['taller'] = "checked";
                $taller = "Si";
            }
            //echo "<td>".$taller."</td>";
            echo '<td><input tabindex=18 type="checkbox" name="taller" size=35 value="' . $taller . '" '.$_POST ['taller'].'>'.$taller.'</td>';
            echo'</tr>';
        }else{
        	$disabled = 'readonly';
        	$disabled2 = 'readonly';
        	
            echo '<tr><td>' . _ ( 'Sucursal' ) . ':</td>';
            if (! isset ( $_POST ['BrName'] )) {
            	$disabled = '';
            	$disabled2 = '';
            	
				$_POST ['BrName'] = '';
            }
            echo '<td><input tabindex=2 type="Text" '.$disabled.' name="BrName" size=41 maxlength=100 value="' . $_POST ['BrName'] . '"></td></tr>';
            
            echo '<tr><td>' . _ ( 'Contacto' ) . ':</td>';
          /*  if (! isset ( $_POST ['ContactName'] )) {
            	$disabled = '';
				$_POST ['ContactName'] = '';
            }*/

            //obtener permiso para modificar el campo contacto
            $PermisoModContacto = "readonly";
            if (Havepermission ( $_SESSION ['UserID'], 2115, $db ) == 1) {
            	if ($DebtorNo != $SelectedBranch) {
            		$PermisoModContacto = "";
            	}
            }

            echo '<td><input tabindex=3 type="Text" '.$PermisoModContacto.' name="ContactName" size=41 maxlength=80 value="' . $_POST ['ContactName'] . '"></td></tr>';
            echo '<tr><td>' . _ ( 'RFC' ) . ':</td>';
            if (! isset ( $_POST ['taxid'] )) {
            	$disabled = '';
				$_POST ['taxid'] = '';
            }
            echo '<td><input tabindex=4 type="Text" '.$disabled.' name="taxid" size=15 maxlength=15 value="' . $_POST ['taxid'] . '"></td></tr>';
            $dirsepomex = '<img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _ ( 'Direcciones Sepomex' ) . '" alt=""> ';
            $trabsel = 1;
            $dirsepomex = $dirsepomex . "<a style='display:inline' href='#' onclick='javascript:var win = window.open(\"SepomexSearch_branchcode.php?idOpener=$trabsel\", \"sepomex\", \"width=500,height=500,scrollbars=1,left=200,top=150\"); win.focus();'>" . _ ( "Seleccionar direccion" ) . "</a>";
            if(isset($_GET['SelectedBranch'])){
            	$dirsepomex = '';    
            }
            //obtener permiso para modificar direccion
            $PermisoModDir = "readonly";
            if (Havepermission ( $_SESSION ['UserID'], 1962, $db ) == 1) {
            	if ($DebtorNo != $SelectedBranch) {
            		$PermisoModDir = "";
            	}
            }

            echo '<tr><td>' . _ ( 'Calle' ) . ':</td>';
            if (! isset ( $_POST ['BrAddress1'] )) {
				$_POST ['BrAddress1'] = '';
            }
            echo '<td><input tabindex=5 type="Text" id="BrAddress1" '.$PermisoModDir.' name="BrAddress1" size=41 maxlength=150 value="' . $_POST ['BrAddress1'] . '">' . $dirsepomex . '</td></tr>';
            //----------------------
            echo '<tr><td>' . _ ( 'Num Ext' ) . ':</td>';
            if (! isset ( $_POST ['brnumext'] )) {
				$_POST ['brnumext'] = '';
            }
            echo '<td><input tabindex=5 type="Text" id="brnumext" '.$PermisoModDir.' name="brnumext" size=22 maxlength=50 value="' . $_POST ['brnumext'] . '"></td></tr>';

            echo '<tr><td>' . _ ( 'Num Int' ) . ':</td>';
            if (! isset ( $_POST ['brnumint'] )) {
				$_POST ['brnumint'] = '';
            }
            echo '<td><input tabindex=5 type="Text" id="brnumint" '.$PermisoModDir.' name="brnumint" size=22 maxlength=50 value="' . $_POST ['brnumint'] . '"></td></tr>';
            //----------------------
            echo '<tr><td>' . _ ( 'Colonia' ) . ':</td>';
            if (! isset ( $_POST ['BrAddress6'] )) {
				$_POST ['BrAddress6'] = '';
            }
            echo '<td><input tabindex=10 type="Text" id="BrAddress6" '.$PermisoModDir.' name="BrAddress6" size=41 maxlength=100 value="' . $_POST ['BrAddress6'] . '"></td></tr>';
            echo '<tr><td>' . _ ( 'Ciudad' ) . ':</td>';
            if (! isset ( $_POST ['BrAddress2'] )) {
				$_POST ['BrAddress2'] = '';
            }
            echo '<td><input tabindex=6 type="Text" id="BrAddress2"  '.$PermisoModDir.' name="BrAddress2" size=41 maxlength=80 value="' . $_POST ['BrAddress2'] . '"></td></tr>';
            echo '<tr><td>' . _ ( 'Estado' ) . ':</td>';
            if (! isset ( $_POST ['BrAddress3'] )) {
				//$_POST ['BrAddress3'] = '';
            }
            echo '<td>';
            $qslq = "Select * FROM states WHERE UPPER(state)=UPPER('".$_POST ['BrAddress3']."')";
            if($codificacionJibe==1){
				DB_query("SET NAMES 'utf8';",$db);
			}
            $rslq = DB_query ( $qslq, $db );
            if ( $rowslq = DB_fetch_array ( $rslq ) ) {
            	$vrauxEd=true;
            }else{
            	$vrauxEd=false;
            }
            if (! isset ( $_POST ['BrAddress3'] )) {
            	if($vrauxEd==true){
    				echo '<select  name="BrAddress3" '.$PermisoModDir.' id="BrAddress3"  >';
            	}else{
            		$_POST ['BrAddress3'] = '';
            	}
            }else{
            	echo '<input type="hidden" name="BrAddress3" value="' . $_POST ['BrAddress3'] . '">';
            	if($vrauxEd==true){
            		echo '<select  name="BrAddress31" ' . $PermisoModDir . ' id="BrAddress31"  >';
            	}
            }
    		if($vrauxEd==true){
    			$qry = "Select * FROM states";
    			if($codificacionJibe==1){
					DB_query("SET NAMES 'utf8';",$db);
				}
	            $rss = DB_query ( $qry, $db );
	            while ( $rows = DB_fetch_array ( $rss ) ) {
					if (strtoupper($_POST ['BrAddress3']) == strtoupper($rows ['state']))
						echo "<option selected value='" . $rows ['state'] . "'>" . strtoupper($rows ['state']) . "</option>";
					else
						echo "<option value='" . $rows ['state'] . "'>" . strtoupper($rows ['state']) . "</option>";
	            }
	            echo '</select>';
    		}else{
    			echo '<input tabindex=7 type="Text" id="BrAddress3"  '.$PermisoModDir.' name="BrAddress3" size=41 maxlength=80 value="' . $_POST ['BrAddress3'] . '">';
    		}    
            
            echo '</td></tr>';
            echo '<tr><td>' . _ ( 'Pais' ) . ':</td>';
            if (! isset ( $_POST ['BrAddress7'] )) {
				$_POST ['BrAddress7'] = '';
				if ($_POST ['BrAddress7'] == '' and $_POST ['custpais'] != ''){
            		$_POST ['BrAddress7'] = $_POST ['custpais'];	
            	}
            }
            echo '<td><input tabindex=8 type="Text" '.$PermisoModDir.' name="BrAddress7" id="BrAddress7" size=31 maxlength=100 value="'. $_POST ['BrAddress7'] . '"></td></tr>';
            echo '<tr><td>' . _ ( 'Codigo Postal' ) . ':</td>';
            if (! isset ( $_POST ['BrAddress4'] )) {
				$_POST ['BrAddress4'] = '';
            }
            echo '<td><input tabindex=8 type="Text" id="BrAddress4" '.$PermisoModDir.' name="BrAddress4" size=31 maxlength=50 value="' . $_POST ['BrAddress4'] . '"></td></tr>';
            echo '<tr><td>' . _ ( 'Direccion 5' ) . ':</td>';
            if (! isset ( $_POST ['BrAddress5'] )) {
				$_POST ['BrAddress5'] = '';
            }
            echo '<td><input tabindex=9 type="Text" '.$PermisoModDir.' name="BrAddress5" size=21 maxlength=20 value="' . $_POST ['BrAddress5'] . '"></td></tr>';
            echo '<tr><td>' . _ ( 'Giro de Cliente' ) . ':</td>';
            if (! isset ( $_POST ['giro'] )) {
				$_POST ['giro'] = '';
            }
            echo '<td><input tabindex=18 type="Text" '.$disabled.' name="giro" size=35 value="' . $_POST ['giro'] . '"></td></tr>';
        
        $sqlwhere = "";
        if (!empty($disabled2)) {
        	//No modificar poner el valor por default
        	$sqlwhere = " WHERE paymentname = '".$_POST['metodoPago']."' ";
        }
        $sql = "SELECT * from paymentmethods ".$sqlwhere." order by paymentname ";
        if($codificacionJibe==1){
			DB_query("SET NAMES 'utf8';",$db);
		}
        $result = DB_query ( $sql, $db, $ErrMsg, $DbgMsg, true );
	
        echo '<tr><td>' . _ ( 'Formas de Pago' ) . ':</td>';
        	echo '<td>';
        	//echo "<br>1: " . $SelectedBranch. " - " . $DebtorNo;
        		if (Havepermission ($_SESSION ['UserID'], 1911, $db ) AND ($SelectedBranch != $DebtorNo)){
        			echo '<select style="width:200px" name="metodoPago" id="metodoPago">';
        		}else{
        			echo '<select style="width:200px" ' . $disabled2 . ' name="metodoPago" id="metodoPago">';
        		}
	            	while ( $myrow = DB_fetch_array ( $result ) ) {
						if ($myrow ['paymentname'] == $_POST ['metodoPago']) {
							echo '<option selected VALUE="';
						} else {
							echo '<option VALUE="';
						}
						echo $myrow ['paymentname'] . '">' . $myrow ['paymentname'];
	            	} // end while loop
            	echo '</select></td></tr>';
        echo '<tr><td>' . _ ( 'Numero de Cuenta' ) . ':</td>';
        if (! isset ( $_POST ['numCuenta'] )) {
        	$_POST ['numCuenta'] = '';
        }
        	if (Havepermission ($_SESSION ['UserID'], 1911, $db ) AND ($SelectedBranch != $DebtorNo)){
        		echo '<td><input tabindex=18 type="Text" name="numCuenta" size=35 value="' . $_POST ['numCuenta'] . '"></td></tr>';
        	}else{
        		echo '<td><input tabindex=18 type="Text" '.$disabled.' name="numCuenta" size=35 value="' . $_POST ['numCuenta'] . '"></td></tr>';
        	}
            


            echo '<tr><td>' . _ ( 'Addenda' ) . ':</td>';
            if (! isset ( $_POST ['adenda'] )) {
            	//$disabled = '';
				$_POST ['adenda'] = '';
            }
            echo '<td><input tabindex=18 type="hidden"  name="adenda1" size=35 value="' . $_POST ['adenda'] . '">
			<select name="adenda" '.$disabled2.'>';
			
			$sqlwhere = "";
	        if (!empty($disabled2)) {
	        	//No modificar poner el valor por default
	        	$sqlwhere = " WHERE id_addenda = '".$_POST['adenda']."' ";
	        }
            $qryad = "SELECT * FROM typeaddenda ".$sqlwhere;
            if($codificacionJibe==1){
				DB_query("SET NAMES 'utf8';",$db);
			}
				
            $radd = DB_query ( $qryad, $db );
            echo '<option selected VALUE=0>' . _ ( 'Ninguna ' ) . '</option>';
            while ( $rowadd = DB_fetch_array ( $radd ) ) {
				if ($rowadd ['id_addenda'] == '0') {
					echo "<option selected value='0'>" . _ ( 'Seleccionar Adenda' ) . "</option>";
				} elseif ($_POST ['adenda'] == $rowadd ['id_addenda'] && $rowadd ['id_addenda'] != '0') {
					echo "<option selected value='" . $rowadd ['id_addenda'] . "'>" . $rowadd ['nameaddenda'] . "</option>";
				} else
					echo "<option value='" . $rowadd ['id_addenda'] . "'>" . $rowadd ['nameaddenda'] . "</option>";
            }
            echo '</select>';
            echo'</td>';
            echo'</tr>';
	
            // cambios
            echo '<tr><td>' . _ ( 'Cuenta con Taller ' ) . ':</td>';
            // if (!isset($_POST['taller'])) {$_POST['taller']='';}else{$_POST['taller']="checked";}
            if ($_POST ['taller'] == 1) {
            	//$disabled = '';
				$_POST ['taller'] = "checked";
            }
            echo '<td><input tabindex=18 type="checkbox" '.$disabled2.' name="taller" size=35 ' . $_POST ['taller'] . '></td></tr>';
        }

	if ($_SESSION ['ShowVehicles'] == 1) {
		echo '<tr><td>' . _ ( 'No. Automoviles' ) . ':</td>';
		if (! isset ( $_POST ['custdata1'] )) {
			$_POST ['custdata1'] = '';
		}
		echo '<td><input tabindex=11 type="Text" name="custdata1" size=4 maxlength=2 class=number value="' . $_POST ['custdata1'] . '"></td></tr>';
		echo '<tr><td>' . _ ( 'No. Camionetas' ) . ':</td>';
		if (! isset ( $_POST ['custdata2'] )) {
			$_POST ['custdata2'] = '';
		}
		echo '<td><input tabindex=12 type="Text" name="custdata2" size=4 maxlength=2 class=number value="' . $_POST ['custdata2'] . '"></td></tr>';
		echo '<tr><td>' . _ ( 'No. Camiones' ) . ':</td>';
		if (! isset ( $_POST ['custdata3'] )) {
			$_POST ['custdata3'] = '';
		}
		echo '<td><input tabindex=13 type="Text" name="custdata3" size=4 maxlength=2 class=number value="' . $_POST ['custdata3'] . '"></td></tr>';
		echo '<tr><td>' . _ ( 'No. Agricolas' ) . ':</td>';
		if (! isset ( $_POST ['custdata4'] )) {
			$_POST ['custdata4'] = '';
		}
		echo '<td><input tabindex=14 type="Text" name="custdata4" size=4 maxlength=2 class=number value="' . $_POST ['custdata4'] . '"></td></tr>';
		echo '<tr><td>' . _ ( 'No. Industrial' ) . ':</td>';
		if (! isset ( $_POST ['custdata4'] )) {
			$_POST ['custdata5'] = '';
		}
		echo '<td><input tabindex=15 type="Text" name="custdata5" size=4 maxlength=2 class=number value="' . $_POST ['custdata5'] . '"></td></tr>';
		echo '<tr><td>' . _ ( 'No. Muevetierra' ) . ':</td>';
		if (! isset ( $_POST ['custdata4'] )) {
			$_POST ['custdata6'] = '';
		}
		echo '<td><input tabindex=16 type="Text" name="custdata6" size=4 maxlength=2 class=number value="' . $_POST ['custdata6'] . '"></td></tr>';
	}
	echo '</table></td><td valign=top><table border=0 bordercolor=red>';
        if(isset($_GET['from'])){
             echo '<tr><td>' . _ ( 'Contacto' ) . ':</td>';
            if (! isset ( $_POST ['ContactName'] )) {
		$_POST ['ContactName'] = '';
            }
            echo '<td><input tabindex=3 type="Text" name="ContactName" size=41 maxlength=80 value="' . $_POST ['ContactName'] . '"></td></tr>';
        }
	echo '<tr><td>' . _ ( 'Instrucciones Especiales' ) . ':</td>';
	if (! isset ( $_POST ['specialinstructions'] )) {
		$_POST ['specialinstructions'] = '';
	}
	echo '<td><input tabindex=15 type="Text" name="specialinstructions" size=56 value="' . $_POST ['specialinstructions'] . '"></td></tr>';
	
	// ******************************INICIO NOTA**********************************************//
	// ********SE OCULTARON ESTOS CAMPOS POR QUE PARA ESTA VERSION NO SON DE UTILIDAD*******//
	// ********SE USO LA SIGUIENTE LINEA DE CODIGO PARA OCULTAR style=display:none; ********//
	echo '<tr style=display:none;><td>' . _ ( 'Dias predeterminados para entregar' ) . ':</td>';
	if (! isset ( $_POST ['EstDeliveryDays'] )) {
		$_POST ['EstDeliveryDays'] = 0;
	}
	echo '<td><input ' . (in_array ( 'EstDeliveryDays', $Errors ) ? 'class="inputerror"' : '') . ' tabindex=16 type="text" class=number name="EstDeliveryDays" size=4 maxlength=2 value=' . $_POST ['EstDeliveryDays'] . '></td></tr>';
	echo '<tr style=display:none;><td>' . _ ( 'Mover fecha despues del (dia en el mes)' ) . ':</td>';
	if (! isset ( $_POST ['FwdDate'] )) {
		$_POST ['FwdDate'] = 0;
	}
	echo '<td><input ' . (in_array ( 'FwdDate', $Errors ) ? 'class="inputerror"' : '') . ' tabindex=17 type="text" class=number name="FwdDate" size=4 maxlength=2 value=' . $_POST ['FwdDate'] . '></td></tr>';
	// ************************************************************************************//
	// ******************************FIN NOTA**********************************************//
	// ************************************************************************************//
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
	// echo $sql;
	if($codificacionJibe==1){
		DB_query("SET NAMES 'utf8';",$db);
	}
	$result = DB_query ( $sql, $db );
        
        
        $permisoMod = Havepermission ( $_SESSION ['UserID'], 1311, $db );
	
	echo '<tr><td>' . _ ( 'Vendedor' ) . ':</td>';
	
	if ($permisoMod == 1) {
		$sql = "SELECT salesmanname,
						salesmancode
				FROM salesman
				WHERE salesmancode = '" . $_POST ['Salesman'] . "'";
		if($codificacionJibe==1){
			DB_query("SET NAMES 'utf8';",$db);
		}
		$result = DB_query ( $sql, $db );
		$myrowemp = DB_fetch_array ( $result );
		echo "<td><input type=hidden name=Salesman value='" . $_POST ['Salesman'] . "'>" . $myrowemp ['salesmanname'] ."</td>";
	} else {
            echo '<td><select tabindex=18 name="Salesman">';
	while ( $myrow = DB_fetch_array ( $result ) ) {
		if (isset ( $_POST ['Salesman'] ) and $myrow ['salesmancode'] == $_POST ['Salesman']) {
			echo '<option selected VALUE=';
		} else {
			echo '<option VALUE=';
		}
		echo $myrow ['salesmancode'] . '>' . $myrow ['salesmanname'];
	} // end while loop
        echo '</select></td></tr>';
        }
	
	
	DB_data_seek ( $result, 0 );
	
	$sql = "SELECT DISTINCT areas.areacode, areas.areadescription FROM tags 
		INNER JOIN areas ON tags.areacode = areas.areacode
		INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
		AND sec_unegsxuser.userid = '{$_SESSION['UserID']}'
		UNION SELECT areas.areacode, areas.areadescription FROM areas WHERE areacode = '{$_POST['Area']}'";
	if($codificacionJibe==1){
		DB_query("SET NAMES 'utf8';",$db);
	}
	$result = DB_query ( $sql, $db );
	if (DB_num_rows ( $result ) == 0) {
		echo '</table>';
		prnMsg ( _ ( 'No existen �reas definidas todav�a' ) . ' - ' . _ ( 'sucursales de los clientes se deben asignar a un �rea' ) . '. ' . _ ( 'Utilice el siguiente enlace para definir al menos una area de ventas' ), 'error' );
		echo "<br><a href='$rootpath/Areas.php?" . SID . "'>" . _ ( 'Definir Zonas de Venta' ) . '</a>';
		include ('includes/footer.inc');
		exit ();
	}
	
	echo '<tr><td>' . _ ( 'Area de venta' ) . ':</td>';
	echo '<td><select tabindex=19 name="Area">';
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
	
	$sql = "SELECT locations.loccode, locationname FROM locations
		INNER JOIN sec_loccxusser ON sec_loccxusser.loccode = locations.loccode 
		AND sec_loccxusser.userid = '{$_SESSION['UserID']}' UNION
		SELECT loccode, locationname FROM locations WHERE loccode = '{$_POST['DefaultLocation']}'";
	if($codificacionJibe==1){
		DB_query("SET NAMES 'utf8';",$db);
	}
	$result = DB_query ( $sql, $db );
	
	if (DB_num_rows ( $result ) == 0) {
		echo '</table>';
		prnMsg ( _ ( 'No existen lugares de valores definidos hasta ahora' ) . ' - ' . _ ( 'cliente debe hacer referencia a una ubicaci�n predeterminada en la sucursal' ) . '. ' . _ ( 'Utilice el siguiente enlace para definir al menos una ubicaci�n' ), 'error' );
		echo "<br><a href='$rootpath/Locations.php?" . SID . "'>" . _ ( 'Defina las ubicaciones de archivo' ) . '</a>';
		include ('includes/footer.inc');
		exit ();
	}
	
	echo '<tr><td>' . _ ( 'Almacen' ) . ':</td>';
	echo '<td><select tabindex=20 name="DefaultLocation" style="font-size:8pt;">';
	
	while ( $myrow = DB_fetch_array ( $result ) ) {
		if (isset ( $_POST ['DefaultLocation'] ) and $myrow ['loccode'] == $_POST ['DefaultLocation']) {
			echo '<option selected VALUE=';
		} else {
			echo '<option VALUE=';
		}
		echo $myrow ['loccode'] . '>' . $myrow ['locationname'];
	} // end while loop
	
	echo '</select></td></tr>';
	if($codificacionJibe==1){
		echo '<tr><td>' . regexDecode( 'Teléfono' ) . ':</td>';	
	}else{
		echo '<tr><td>' . utf8_decode( 'Teléfono' ) . ':</td>';
	
	}
	if (! isset ( $_POST ['PhoneNo'] )) {
		$_POST ['PhoneNo'] = '';
	}
	echo '<td><input tabindex=21 type="Text" name="PhoneNo" size=22 maxlength=20 value="' . $_POST ['PhoneNo'] . '"></td></tr>';
	
	echo '<tr><td>' . _ ( 'Fax' ) . ':</td>';
	if (! isset ( $_POST ['FaxNo'] )) {
		$_POST ['FaxNo'] = '';
	}
	echo '<td><input tabindex=22 type="Text" name="FaxNo" size=22 maxlength=20 value="' . $_POST ['FaxNo'] . '"></td></tr>';
	
	if (! isset ( $_POST ['Email'] )) {
		$_POST ['Email'] = '';
	}
	echo '<tr><td>' . (($_POST ['Email']) ? '<a href="Mailto:' . $_POST ['Email'] . '">' . _ ( 'Email' ) . ':</a>' : _ ( 'Email' ) . ':') . '</td>';
	// only display email link if there is an email address
	echo '<td><input tabindex=23 type="Text" name="Email" size=56 maxlength=55 value="' . $_POST ['Email'] . '"></td></tr>';
	
	echo '<tr><td>' . _ ( 'Grupo de Impuestos' ) . ':</td>';
	echo '<td><select tabindex=24 name="TaxGroup">';
	
	DB_data_seek ( $result, 0 );
	
	$sql = 'SELECT taxgroupid, taxgroupdescription FROM taxgroups';
	if($codificacionJibe==1){
		DB_query("SET NAMES 'utf8';",$db);
	}
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
	echo '<tr><td>' . _ ( 'Las transacciones en esta sucursal' ) . ":</td><td><select tabindex=25 name='DisableTrans'>";
	if ($_POST ['DisableTrans'] == 0) {
		echo '<option selected VALUE=0>' . _ ( 'Habilitado' );
		echo '<option VALUE=1>' . _ ( 'Deshabilitado ' );
	} else {
		echo '<option selected VALUE=1>' . _ ( 'Deshabilitado' );
		echo '<option VALUE=0>' . _ ( 'Habilitado' );
	}
	echo '<tr><td>' . _ ( 'Ruta' ) . ":</td>
		<td><select name='ruta'>
			<option value='0' selected>SIN RUTA</option>";
	
	$sql1 = "SELECT rutaid, ruta
			FROM rutas";
	if($codificacionJibe==1){
		DB_query("SET NAMES 'utf8';",$db);
	}
	$result1 = DB_query ( $sql1, $db );
	while ( $myrow = DB_fetch_array ( $result1 ) ) {
		if ($_POST ['ruta'] == $myrow ['rutaid']) {
			echo "<option selected VALUE=" . $myrow ['rutaid'] . '>' . $myrow ['ruta'];
		} else {
			echo '<option VALUE=' . $myrow ['rutaid'] . '>' . $myrow ['ruta'];
		}
	} // end while loop
	DB_data_seek ( $result1, 0 );
	echo '	</select></td></tr>';
	
	// ******************************INICIO NOTA**********************************************//
	// ********SE OCULTARON ESTOS CAMPOS POR QUE PARA ESTA VERSION NO SON DE UTILIDAD*******//
	// ********SE USO LA SIGUIENTE LINEA DE CODIGO PARA OCULTAR style=display:none; ********//
	echo '<tr style=display:none;><td>' . _ ( 'Cargar por defecto/metodo de carga' ) . ":</td><td><select tabindex=26 name='DefaultShipVia'>";
	
	$SQL = 'SELECT shipper_id, shippername FROM shippers';
	if($codificacionJibe==1){
		DB_query("SET NAMES 'utf8';",$db);
	}
	$ShipperResults = DB_query ( $SQL, $db );

	if (DB_num_rows($ShipperResults) == 0){
		echo '<option selected VALUE="0">Vacio';
	}

	while ( $myrow = DB_fetch_array ( $ShipperResults ) ) {
		if (isset ( $_POST ['DefaultShipVia'] ) and $myrow ['shipper_id'] == $_POST ['DefaultShipVia']) {
			echo '<option selected VALUE=' . $myrow ['shipper_id'] . '>' . $myrow ['shippername'];
		} else {
			echo '<option VALUE=' . $myrow ['shipper_id'] . '>' . $myrow ['shippername'];
		}
	}
	
	echo '</select></td></tr>';
	
	/*
	 * This field is a default value that will be used to set the value on the sales order which will control whether or not to display the company logo and address on the packlist
	 */
	echo '<tr style=display:none;><td>' . _ ( 'Por defecto Packlist' ) . ":</td><td><select tabindex=27 name='DeliverBlind'>";
	for($p = 1; $p <= 2; $p ++) {
		echo '<option VALUE=' . $p;
		if ($p == $_POST ['DeliverBlind']) {
			echo ' selected>';
		} else {
			echo '>';
		}
		switch ($p) {
			case 1 :
				echo _ ( 'Mostrar los detalles de la compania y logotipo' );
				break;
			case 2 :
				echo _ ( 'Ocultar detalles de la compania y logotipo' );
				break;
		}
	}
	echo '</select></td></tr>';
	$dirsepomex = '<img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _ ( 'Direcciones Sepomex' ) . '" alt=""> ';
	$trabsel = 1;
	$dirsepomex = $dirsepomex . "<a style='display:inline' href='#' onclick='javascript:var win = window.open(\"SepomexSearch_branchcodeEntrega.php?idOpener=$trabsel\", \"sepomex\", \"width=500,height=500,scrollbars=1,left=200,top=150\"); win.focus();'>" . _ ( "Seleccionar direccion" ) . "</a>";
	
	echo '<tr ><td>' . _ ( 'Calle Sucursal' ) . ':</td>';
	if (! isset ( $_POST ['BrPostAddr1'] )) {
		$_POST ['BrPostAddr1'] = '';
	}
	echo '<td><input tabindex=28 type="Text" id="BrPostAddr1" name="BrPostAddr1" size=41 maxlength=150 value="' . $_POST ['BrPostAddr1'] . '">' . $dirsepomex . '</td></tr>';
	
	
	echo '<tr ><td>' . _ ( 'Num Ext: ' ) . ':</td>';
	if (! isset ( $_POST ['brnumext'] )) {
		$_POST ['brnumext'] = '';
	}
	echo '<td><input tabindex=28 type="Text" id="brnumext" name="brnumext" size=41 maxlength=150 value="' . $_POST ['brnumext'] . '"></td></tr>';

	echo '<tr ><td>' . _ ( 'Num Int: ' ) . ':</td>';
	if (! isset ( $_POST ['brnumint'] )) {
		$_POST ['brnumint'] = '';
	}
	echo '<td><input tabindex=28 type="Text" id="brnumint" name="brnumint" size=41 maxlength=150 value="' . $_POST ['brnumint'] . '"></td></tr>';
	
	
	echo '<tr ><td>' . _ ( 'Colonia Sucursal' ) . ':</td>';
	if (! isset ( $_POST ['BrPostAddr2'] )) {
		$_POST ['BrPostAddr2'] = '';
	}
	echo '<td><input tabindex=29 type="Text" id="BrPostAddr2" name="BrPostAddr2" size=41 maxlength=150 value="' . $_POST ['BrPostAddr2'] . '"></td></tr>';
	echo '<tr><td>' . _ ( 'Municipio Sucursal' ) . ':</td>';
	if (! isset ( $_POST ['BrPostAddr3'] )) {
		$_POST ['BrPostAddr3'] = '';
	}
	echo '<td><input tabindex=30 type="Text" id="BrPostAddr3" name="BrPostAddr3" size=31 maxlength=150 value="' . $_POST ['BrPostAddr3'] . '"></td></tr>';

	echo '<tr ><td>' . _ ( 'Estado Sucursal' ) . ':</td>';

	if (! isset ( $_POST ['BrPostAddr4'] )) {
		$_POST ['BrPostAddr4'] = '';
	}

	$qslq = "Select * FROM states WHERE UPPER(state)=UPPER('".$_POST ['BrPostAddr4']."')";
	if($codificacionJibe==1){
		DB_query("SET NAMES 'utf8';",$db);
	}
    $rslq = DB_query ( $qslq, $db );
    if ( $rowslq = DB_fetch_array ( $rslq ) ) {
    	$vrauxEd=true;
    }else{
    	$vrauxEd=false;
    }
    echo '<td>';
	    
    if($_SESSION['DatabaseName']=="erppisumma_DES" OR $_SESSION['DatabaseName']=="erppisumma_CAPA" OR $_SESSION['DatabaseName']=="erppisumma"){
    	
	    if($vrauxEd==true){
			echo '<select name="BrPostAddr4" id="BrPostAddr4" >';
			echo "<option value='0'>Elige un estado </option>";
			$qry = "Select * FROM states";
			$rss = DB_query ( $qry, $db );
			while ( $rows = DB_fetch_array ( $rss ) ) {
				if ($_POST ['BrPostAddr4'] == $rows ['state'])
					echo "<option selected value='" . $rows ['state'] . "'>" . $rows ['state'] . "</option>";
				else
					echo "<option value='" . $rows ['state'] . "'>" . $rows ['state'] . "</option>";
			}       
	        echo '</select>';
		}else{
			echo '<input tabindex=31 type="Text" id="BrPostAddr4" name="BrPostAddr4" size=21 maxlength=150 value="' . $_POST ['BrPostAddr4'] . '">';
		}
	}else{
		echo '<select name="BrPostAddr4" id="BrPostAddr4" >';
		echo "<option value='0'>Elige un estado </option>";
		$qry = "Select * FROM states";
		if($codificacionJibe==1){
			DB_query("SET NAMES 'utf8';",$db);
		}
		$rss = DB_query ( $qry, $db );
		while ( $rows = DB_fetch_array ( $rss ) ) {
			if ($_POST ['BrPostAddr4'] == $rows ['state'])
				echo "<option selected value='" . $rows ['state'] . "'>" . $rows ['state'] . "</option>";
			else
				echo "<option value='" . $rows ['state'] . "'>" . $rows ['state'] . "</option>";
		}       
        echo '</select>';
		
	}
	echo '</td></tr>';

	echo '<tr ><td>' . _ ( 'Codigo Postal Sucursal' ) . ':</td>';
	if (! isset ( $_POST ['BrPostAddr5'] )) {
		$_POST ['BrPostAddr5'] = '';
	}
	echo '<td><input tabindex=32 type="Text" id="BrPostAddr5" name="BrPostAddr5" size=21 maxlength=150 value="' . $_POST ['BrPostAddr5'] . '"></td></tr>';

	echo '<tr ><td>' . _ ( 'Pais' ) . ':</td>';
	if (! isset ( $_POST ['custpais'] )) {
		$_POST ['custpais'] = '';
	}
	echo '<td><input tabindex=33 type="Text" name="custpais" id="custpais" size=21 maxlength=20 value="' . $_POST ['custpais'] . '"></td></tr>';
	
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
	// <input tabindex=31 type="Text" name="BrPostAddr5" size=21
	// maxlength=20 value="'. $_POST['BrPostAddr5'].'"></td></tr>';
	
	// echo '<tr><td>'._('Customers Internal Branch Code (EDI)').':</td>';
	// if (!isset($_POST['CustBranchCode'])) {$_POST['CustBranchCode']='';}
	// echo '<td><input tabindex=32 type="Text" name="CustBranchCode" size=31 maxlength=30 value="'. $_POST['CustBranchCode'].'"></td></tr>';
	// ***************************************************************************************//
	// *********************************FIN NOTA**********************************************//
	// ***************************************************************************************//
	echo '</table>';
	echo '</td></tr></table>';
	echo '<div class="centre"><input tabindex=33 type="Submit" name="submit" value="' . _ ( 'Procesar' ) . '"></div>';
	echo "<input type='hidden' name='identifier' value='" . $identifier . "'>";
	echo '</form>';
} // end if record deleted no point displaying form to add record

include ('includes/footer.inc');
?>
