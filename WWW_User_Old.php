<?php
/**
 * Pagina para dar mantenimiento a usuarios
 *
 * @category ABC
 * @package ap_grp
 * @author Armando Barrientos Martinez <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creaci�n: 03/08/2017
 * Fecha Modificaci�n: 04/08/2017
 */
/////////
$funcion=90;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
include 'includes/mail.php';
$title= traeNombreFuncion($funcion, $db);

include 'includes/header.inc';
include 'includes/SQL_CommonFunctions.inc';
include 'includes/SecurityFunctions.inc';

$procesoterminado= false;
$fechaHoy        = date( "Y-m-d H:i:s" ); 
if (isset($_GET['SelectedUser'])){
	$SelectedUser = $_GET['SelectedUser'];
} elseif (isset($_POST['SelectedUser'])){
	$SelectedUser = $_POST['SelectedUser'];
}

if (isset($_GET['totalfunciones'])){
	$Selectedtotal = $_GET['totalfunciones'];
} elseif (isset($_POST['totalfunciones'])){
	$Selectedtotal = $_POST['totalfunciones'];
}else{
	$Selectedtotal=0; 
}

if($_SESSION['SERVER_URL']==''){
	$SERVER_URL="http://erp.tecnoaplicada.com";
}else{
	$SERVER_URL=$_SESSION['SERVER_URL'];
}

if (!isset($_POST['filtag'])) {
	$_POST['filtag']= "";
} 

if (isset($_POST['submit']) and $Selectedtotal == 0) {
	$InputError = 0;

	if (strlen($_POST['UserID'])<3){
		$InputError = 1;
		prnMsg(_('La longitud del login de usuario debe ser mayor a 4 caracteres'),'error');
	} elseif (ContainsIllegalCharacters($_POST['UserID'])) {
		$InputError = 1;
		prnMsg(_('El nombre de usuario no puede contener los siguientes caracteres ') . " - ' & + \" \\ " . _('o espacios'),'error');
	} elseif (strlen($_POST['Password'])<5){
		if (!$SelectedUser){
			$InputError = 1;
			prnMsg(_('El password debe ser mayor a 5 caracteres'),'error');
		}
	} elseif (strstr($_POST['Password'],$_POST['UserID'])!= False){
		$InputError = 1;
		prnMsg(_('El password debe ser diferente al login de usuario'),'error');
	} elseif ((strlen($_POST['Cust'])>0) AND (strlen($_POST['BranchCode'])==0)) {
		$InputError = 1;
		prnMsg(_('Si introduce el codigo de cliente, tambien debe introducir el codigo de sucursal'),'error');
	} elseif ($_POST['DefaultLocation'] == '-1') {
		$InputError = 1;
		prnMsg(_('Seleccionar Almacen por Default'),'error');
	}
	
	if ((strlen($_POST['BranchCode'])>0) AND ($InputError !=1)) {
		// check that the entered branch is valid for the customer code
		$sql = "SELECT custbranch.debtorno
				FROM custbranch
				WHERE custbranch.debtorno='" . $_POST['Cust'] . "'
				AND custbranch.branchcode='" . $_POST['BranchCode'] . "'";
		$ErrMsg = _('La validacion del codigo de sucursal fallo por que');
		$DbgMsg = _('El SQL usado en la validacion de la sucursal fue:');
		// Se comenta para aligerar carga de pagina
		// $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		if (DB_num_rows($result)==0){
			prnMsg(_('El codigo de Sucursal No es valido'),'error');
			$InputError = 1;
		}
	}

	/* Make a comma separated list of modules allowed ready to update the database*/
	$i=0;
	$ModulesAllowed = '';
	/* AL PARECER ESTO NO HACE NADA, EN NINGUN LADO EXISTE LA ASIGNACION PARA $ModuleList
	while ($i < count($ModuleList)){
		$FormVbl = "Module_" . $i;
		$ModulesAllowed .= $_POST[($FormVbl)] . ',';
		$i++;
	}
	*/
	
	$_POST['ModulesAllowed']= $ModulesAllowed;
	
	//echo $ModuleList;
	
	if (!isset($_POST['creditlimit']) or $_POST['creditlimit'] == ""){
		$_POST['creditlimit'] = 0;
	}

	if (!isset($_POST['displayrecordsmax']) or $_POST['displayrecordsmax'] == ""){
		$_POST['displayrecordsmax'] = 0;
	}

	if (empty($_POST['DefaultUnidad'])) {
		$_POST['DefaultUnidad'] = 0;
	}
	if (empty($_POST['Salesman'])) {
		$_POST['Salesman'] = 0;
	}

	if ($SelectedUser AND $InputError !=1) {
		/*******************************************Agrega el Archivo de Pie de Pagina *****************************/
		if (isset($_FILES['ItemPicture']) AND $_FILES['ItemPicture']['name'] !='') {
		
			$result    = $_FILES['ItemPicture']['error'];
			$UploadTheFile = 'Yes'; //Assume all is well to start off with
			$filename = $_SESSION['part_pics_dir'] . '/' . $_POST['UserID'] . '.jpg';
		
			//But check for the worst
			if (strtoupper(substr(trim($_FILES['ItemPicture']['name']),strlen($_FILES['ItemPicture']['name'])-3))!='JPG'){
				prnMsg(_('Solo archivos jpg son soportados - un archivo con terminacion jpg es esperado'),'warn');
				$UploadTheFile ='No';
			} elseif ( $_FILES['ItemPicture']['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
				prnMsg(_('El tamano de archivo esta sobre el maximo permitido. El tama�o maximo en KB es') . ' ' . $_SESSION['MaxImageSize'],'warn');
				$UploadTheFile ='No';
			} elseif ( $_FILES['ItemPicture']['type'] == "text/plain" ) {  //File Type Check
				prnMsg( _('Solo archivos de tipo graficos pueden ser subidos'),'warn');
				$UploadTheFile ='No';
			} elseif (file_exists($filename)){
				prnMsg(_('Intentando sobreescribir una archivo de imagen'),'warn');
				$result = unlink($filename);
				if (!$result){
					prnMsg(_('La imagen actual no puede ser reemplazada'),'error');
					$UploadTheFile ='No';
				}
			}
		
			if ($UploadTheFile=='Yes'){
				$result  =  move_uploaded_file($_FILES['ItemPicture']['tmp_name'], $filename);
				$message = ($result)?_('File url') ."<a href='". $filename ."'>" .  $filename . '</a>' : _('Something is wrong with uploading a file');
			}
			/* EOR Add Image upload for New Item  - by Ori */
			//echo 'file'.$filename;
		}
		/*******************************************Agrega el Archivo de Pie de Pagina *****************************/
		/*SelectedUser could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		if (!isset($_POST['Cust']) OR $_POST['Cust']==NULL OR $_POST['Cust']==''){
			$_POST['Cust']='';
			$_POST['BranchCode']='';
		}
		
		$UpdatePassword = "";
		if ($_POST['Password'] != ""){
			$UpdatePassword = "password='" . CryptPass($_POST['Password']) . "',";
		}

		$sql = "UPDATE www_users SET realname='" . $_POST['RealName'] . "',
						customerid='" . $_POST['Cust'] ."',
						phone='" . $_POST['Phone'] ."',
						email='" . $_POST['Email'] ."',
						department='".$_POST['department']."',		
						" . $UpdatePassword . "
						branchcode='" . $_POST['BranchCode'] . "',
						salesman='" . $_POST['Salesman'] . "',
						pagesize='" . $_POST['PageSize'] . "',
						fullaccess=8,
						theme='" . $_POST['Theme'] . "',
						language ='" . $_POST['UserLanguage'] . "',
						defaultarea='" . $_POST['DefaultLocation'] ."',
						defaultunidadNegocio='" . $_POST['DefaultUnidad'] ."',
						blocked=" . $_POST['Blocked'] . ",
						discount1=" . $_POST['discount1']/100 . ",
						discount2=" . $_POST['discount2']/100 . ",
						discount3=" . $_POST['discount3']/100 . ",
						creditlimit=" . $_POST['creditlimit'] . ",
						displayrecordsmax=" . $_POST['displayrecordsmax'] . ",
						ImagenUsuario = '".$filename."',
						ShowIndex=".$_POST['ShowIndex'].",
						defaultlocation = '".$_POST['DefaultLocation']."'
					WHERE userid = '$SelectedUser'";

		$msg = _('El usuario seleccionado ha sido actualizado');
		$usuarioseleccionado=$SelectedUser;
	} elseif ($InputError !=1) {


		$sql = "INSERT INTO www_users (userid,
						realname,
						customerid,
						branchcode,
						password,
						phone,
						email,
						department,
						pagesize,
						fullaccess,
						defaultarea,
						modulesallowed,
						displayrecordsmax,
						theme,
						language,
						defaultunidadNegocio,
						discount1,
						discount2,
						discount3,
						creditlimit,
						ShowIndex,
						ImagenUsuario,
						userap,
						defaultlocation
						)
					VALUES ('" . $_POST['UserID'] . "',
						'" . $_POST['RealName'] ."',
						'" . $_POST['Cust'] ."',
						'" . $_POST['BranchCode'] ."',
						'" . CryptPass($_POST['Password']) ."',
						'" . $_POST['Phone'] . "',
						'" . $_POST['Email'] ."',
						'" . $_POST['department'] ."',		
						'" . $_POST['PageSize'] ."',
						8,
						'" . $_POST['DefaultLocation'] ."',
						'" . $ModulesAllowed . "',
						" . $_POST['displayrecordsmax'] . ",
						'" . $_POST['Theme'] . "',
						'" . $_POST['UserLanguage'] . "',
						'". $_POST['DefaultUnidad'] ."',
						". $_POST['discount1']/100 .",
						". $_POST['discount2']/100 .",
						". $_POST['discount3']/100 .",
						". $_POST['creditlimit'].",
						". $_POST['ShowIndex'].",
						'".$filename."',
						0,
						'".$_POST['DefaultLocation']."')";
		$msg = _('Registro de usuario Exitoso');
		
		//Envio de datos de cuenta al usuario
		if(IsEmailAddress($_POST['Email']) && 1 == 2){
			$emailFrom= $_SESSION['SMTP_emailSENDER'];
			$emailTo=$_POST['Email'];
			$emailToName=$_POST['RealName'];
			$subject = "Datos de Acceso al Sistema";
			$message = "Se ha creado el usuario para " . $_POST['RealName'] .".\r\n\r\n
			Servidor: ".$SERVER_URL." \r\n
			Usuario: ".$_POST['UserID']."
			Contrase�a: ".$_POST['Password']."\r\n\r\n
			Le sugerimos modificar su contrase�a dando click en su nombre cuando ingrese al sistema. ";
		
			$from_name = "Sistema tecnoaplicada";
			$from_mail = $emailFrom;
			$replyto = $from_mail;
			//echo $message;
			$mail = new Mail();
			$mail->protocol = 'smtp';
			$mail->hostname = 'localhost';
			$mail->port = 25;
			$mail->timeout = 3000;
			$mail->setTo($emailTo);
			$mail->setFrom($from_mail);
			$mail->setSender($from_name);
			$mail->setSubject($subject);
			$mail->setText($message);
			//echo ".createEmail";
			$envio= $mail->send();
			if($envio){
					prnMsg(_('Datos de Acceso enviados:').$emailTo, 'success');
				}else{
					prnMsg(_('Fallo en el envio: ')." server:".$SERVER_URL." to:".$emailTo." from:".$from_mail." sender:".$from_name." subject:".$subject." smtp:".$mail->hostname, 'error');
				}
		}else{
			prnMsg(_("Se ha creado el usuario para " . $_POST['RealName'] .".\r\n\r\n
			Servidor: ".$SERVER_URL." \r\n
			Usuario: ".$_POST['UserID']."
			Contrase&ntilde;a: ".$_POST['Password']."\r\n\r\n
			Le sugerimos modificar su contrase&ntilde;a dando click en su nombre cuando ingrese al sistema. "), 'success');
		}
		
		
		$usuarioseleccionado=$_POST['UserID'];
		//echo '<td colspan=3 style="text-align:center;"> <a href="'. $rootpath .'/ReporteLimitxUsuario.php?' . SID .'&SelectedUser=' .$SelectedUser.'" title="Asignar L�mites de Cr�dito">Asignar L�mites de Cr�dito</a></td>';
		header('Location:' . $rootpath . '/ReporteLimitxUsuario.php?'. SID .'&SelectedUser=' .$usuarioseleccionado.'');


	}
	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		$ErrMsg = _('Las operaciones sobre el registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		/*eliminamos los permisos para sucursal para este usuario*/
		$sql="Delete from sec_loccxusser WHERE userid = '".$usuarioseleccionado."'";
		$ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		/* Agregar permisos de sucursales por usuario*/
		if (isset($_POST['TotalSucursal'])){
			$totalsucursales=$_POST['TotalSucursal'];
			for ( $suc = 0 ; $suc <= $totalsucursales ; $suc++) {
				if ($_POST['SucursalSel'.$suc]==TRUE){
					$namesucursal=$_POST['Namesucursal'.$suc];
					$sql="insert into sec_loccxusser (userid,loccode)";
					$sql=$sql." values('".$usuarioseleccionado."','".$namesucursal."')";
					$ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
					$DbgMsg = _('El SQL utilizado es:');
					$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}
			
		}
		
		/*eliminamos los permisos para sucursal requisicion para este usuario*/
		$sql="Delete from sec_loccxusserrequisition WHERE userid = '".$usuarioseleccionado."'";
		$ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		// Se comenta para aligerar carga de pagina
		// $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		/* Agregar permisos de sucursales por usuario*/
		if (isset($_POST['TotalSucursalREQ'])){
			$totalsucursales=$_POST['TotalSucursalREQ'];
			for ( $suc = 0 ; $suc <= $totalsucursales ; $suc++) {
				if ($_POST['REQSucursalSel'.$suc]==TRUE){
					$namesucursal=$_POST['NamesucursalREQ'.$suc];
					$sql="insert into sec_loccxusserrequisition (userid,loccode)";
					$sql=$sql." values('".$usuarioseleccionado."','".$namesucursal."')";
					$ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
					$DbgMsg = _('El SQL utilizado es:');
					// Se comenta para aligerar carga de pagina
					// $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}
			
		}
		
		/*eliminamos los permisos para unidades de negocio para este usuario*/
		$sql="Delete from sec_unegsxuser WHERE userid = '".$usuarioseleccionado."'";
		$ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		/* Agregar permisos de unidad de negocio por usuario*/
		if (isset($_POST['TotalUnidades'])){
			$TotalUnidades=$_POST['TotalUnidades'];
			for ( $unidad = 0 ; $unidad <= $TotalUnidades ; $unidad ++) {
				if ($_POST['UNSel'.$unidad]==TRUE){
					$NameUnidad=$_POST['NameUnidad'.$unidad];
					$sql="insert into sec_unegsxuser (userid,tagref)";
					$sql=$sql." values('".$usuarioseleccionado."','".$NameUnidad."')";
					$ErrMsg = _('Las operaciones sobre las unidades de negocio para este registro no han sido posibles por que ');
					$DbgMsg = _('El SQL utilizado es:');
					$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}
		}

		/*eliminamos los permisos para unidades ejecutoras para este usuario*/
		$sql = "DELETE FROM `tb_sec_users_ue` WHERE `userid` = '".$usuarioseleccionado."'";
		$ErrMsg = _('Las operaciones sobre las unidades ejecutoras para este registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		/* Agregar permisos de unidad ejecutora por usuario*/
		$_POST['URS'] = explode(",", $_POST['URS']);
		if(is_array($_POST['URS'])&&count($_POST['URS'])){
			foreach($_POST['URS'] AS $UR){
				if(isset($_POST["TotalUE-$UR"])){
					for($c=0;$c<=$_POST["TotalUE-$UR"];$c++){
						if($_POST["UE-$UR$c"]==true){
							$UE = $_POST["Nombre-UE-$UR$c"];
							$sql = "INSERT INTO `tb_sec_users_ue` (`userid`, `tagref`, `ue`, `ln_aux1`, `ind_activo`, `dtm_fecha_alta`, `dtm_fecha_actualizacion`) VALUES 
									('$usuarioseleccionado','$UR','$UE','$UR$UE','1','".date( "Y-m-d H:i:s" )."','".date( "Y-m-d H:i:s" )."');";
							$ErrMsg = _('Las operaciones sobre las unidades ejecutoras para este registro no han sido posibles por que ');
							$DbgMsg = _('El SQL utilizado es:');
							$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
						}
					}
				}
			}
		}
		
		/*eliminamos los perfiles para este usuario*/
		$sql="Delete from sec_profilexuser WHERE userid = '$usuarioseleccionado'";
		$ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		/* Agregar permisos de sucursales por usuario*/
		if (isset($_POST['TotalPerfiles'])){
			$TotalPerfiles=$_POST['TotalPerfiles'];
			for ( $perfil = 0 ; $perfil <= $TotalPerfiles ; $perfil ++) {
				
				if ($_POST['PerfilSel'.$perfil]==TRUE){
					
					$NameProfile=$_POST['NameProfile'.$perfil];
					$sql="insert into sec_profilexuser (userid,profileid)";
					$sql=$sql." values('".$usuarioseleccionado."',".$NameProfile.")";
					
					$ErrMsg = _('Las operaciones sobre los perfiles para este registro no han sido posibles por que ');
					$DbgMsg = _('El SQL utilizado es:');
					$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}
		}

		/*eliminamos los Capitulos para este usuario*/
		$sql="DELETE FROM sec_capituloxuser WHERE sn_userid = '$usuarioseleccionado'";
		$ErrMsg = _('No se actualiz� la configuraci�n de los Capitulos por que');
		$DbgMsg = _('El SQL utilizado es:');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		/* Agregar permisos de capitulos por usuario*/
		if (isset($_POST['TotalCapitulos'])){
			$TotalCapitulos=$_POST['TotalCapitulos'];
			for ( $perfil = 0 ; $perfil <= $TotalCapitulos ; $perfil ++) {
				
				if ($_POST['CapituloSel'.$perfil]==TRUE){
					
					$NameCapitulo=$_POST['NameCapitulo'.$perfil];
					$sql="INSERT INTO sec_capituloxuser (sn_userid,sn_capitulo)";
					$sql=$sql." values('".$usuarioseleccionado."',".$NameCapitulo.")";
					
					$ErrMsg = _('Las operaciones sobre los Capitulos para este registro no han sido posibles por que ');
					$DbgMsg = _('El SQL utilizado es:');
					$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}
		}
		
		/*eliminamos los ROLES para este usuario*/
		$sql="Delete from sec_ROLxuser WHERE userid = '$usuarioseleccionado'";
		$ErrMsg = _('Las operaciones sobre los ROLES para este registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		/* Agregar permisos de sucursales por usuario*/
		if (isset($_POST['TotalROL'])){
			$TotalPerfiles=$_POST['TotalROL'];
			for ( $perfil = 0 ; $perfil <= $TotalPerfiles ; $perfil ++) {
				
				if ($_POST['ROLSel'.$perfil]==TRUE){
					
					$NameProfile=$_POST['NameROL'.$perfil];
					$sql="insert into sec_ROLxuser (userid,profileid)";
					$sql=$sql." values('".$usuarioseleccionado."',".$NameProfile.")";
					
					$ErrMsg = _('Las operaciones sobre los ROLES para este registro no han sido posibles por que ');
					$DbgMsg = _('El SQL utilizado es:');
					$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}
		}
		
		/*eliminamos los terminos de pago para este usuario*/
		$sql="Delete from sec_paymentterms WHERE userid = '".$usuarioseleccionado."'";
		$ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		// Se comenta para aligerar carga de pagina
		// $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		/* Agregar permisos de terminos de pago por usuario*/
		if (isset($_POST['TotalPayterms'])){
			$totalsucursales=$_POST['TotalPayterms'];
			for ( $suc = 0 ; $suc <= $totalsucursales ; $suc++) {
				if ($_POST['PaymenttermsSel'.$suc]==TRUE){
					$namesucursal=$_POST['Namepaymentterms'.$suc];
					$sql="insert into sec_paymentterms (userid,termsindicator)";
					$sql=$sql." values('".$usuarioseleccionado."','".$namesucursal."')";
					$ErrMsg = _('Las operaciones sobre los Terminos de Pago para este registro no han sido posibles por que ');
					$DbgMsg = _('El SQL utilizado es:');
					// Se comenta para aligerar carga de pagina
					// $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}			
		}

		/*eliminamos los estatus de credito para este usuario*/
		$sql="Delete from sec_holdreasons WHERE userid = '".$usuarioseleccionado."'";
		$ErrMsg = _('Las operaciones sobre los estatus de credito para este registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		// sec_holdreasons
		// $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		/* Agregar permisos de terminos de pago por usuario*/
		if (isset($_POST['TotalHoldreasons'])){
			$totalestatus=$_POST['TotalHoldreasons'];
			for ( $estcreds = 0 ; $estcreds <= $totalestatus ; $estcreds++) {
				if ($_POST['holdreasonsSel'.$estcreds]==TRUE){
					$xholdreason=$_POST['Nameholdreason'.$estcreds];
					$sql="insert into sec_holdreasons (userid,reasoncode)";
					$sql=$sql." values('".$usuarioseleccionado."','".$xholdreason."')";
					$ErrMsg = _('Las operaciones sobre los estatus de credito para este registro no han sido posibles por que ');
					$DbgMsg = _('El SQL utilizado es:');
					// sec_holdreasons
					// $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}			
		}

		/*eliminamos las categorias de inventario para este usuario*/
		/*$sql="Delete from sec_stockcategory WHERE userid = '".$usuarioseleccionado."'";
		$ErrMsg = _('Las operaciones sobre las categorias de inventario para este registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);*/
		
		/* Agregar permisos de terminos de pago por usuario*/
		/*if (isset($_POST['TotalCategorys'])){
			$totalcategory=$_POST['TotalCategorys'];
			for ( $cats = 0 ; $cats <= $totalcategory ; $cats++) {
				if ($_POST['categorysSel'.$cats]==TRUE){
					$xcategory=$_POST['Namecategory'.$cats];
					$sql="insert into sec_stockcategory (userid,categoryid)";
					$sql=$sql." values('".$usuarioseleccionado."','".$xcategory."')";
                                        
					$ErrMsg = _('Las operaciones sobre las categorias de inventario para este registro no han sido posibles por que ');
					$DbgMsg = _('El SQL utilizado es:');
					$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}			
		}*/


		/*eliminamos la lista de precios para este usuario*/
		$sql="Delete from sec_pricelist WHERE userid = '".$usuarioseleccionado."'";
		$ErrMsg = _('Las operaciones sobre las listas de precios para este registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		// Se comenta para aligerar carga de pagina
		// $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		/* Agregar permisos de terminos de pago por usuario*/
		if (isset($_POST['TotalPrices'])){
			$totalsucursales=$_POST['TotalPrices'];
			for ( $suc = 0 ; $suc <= $totalsucursales ; $suc++) {
				if ($_POST['PrecioSel'.$suc]==TRUE){
					$namesucursal=$_POST['Nameprecio'.$suc];
					$sql="insert into sec_pricelist (userid,pricelist)";
					$sql=$sql." values('".$usuarioseleccionado."','".$namesucursal."')";
					$ErrMsg = _('Las operaciones sobre los Terminos de Pago para este registro no han sido posibles por que ');
					$DbgMsg = _('El SQL utilizado es:');
					// Se comenta para aligerar carga de pagina
					// $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}			
		}
		
		
		/*eliminamos los proyectos para este usuario*/
		$sql="Delete from sec_proyectoxuser WHERE userid = '$usuarioseleccionado'";
		$ErrMsg = _('Las operaciones sobre los proyectos para este registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		// Se comenta para aligerar carga de pagina
		// $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		/* Agregar permisos de proyectos por usuario*/
		if (isset($_POST['TotalProyectos'])){
			$TotalProyectos=$_POST['TotalProyectos'];
			for ( $proyecto = 0 ; $proyecto <= $TotalProyectos ; $proyecto ++) {
				
				if ($_POST['ProyectoSel'.$proyecto]==TRUE){
					$NameProyecto=$_POST['NameProyecto'.$proyecto];
					$sql="insert into sec_proyectoxuser (userid,idproyecto)";
					$sql=$sql." values('".$usuarioseleccionado."',".$NameProyecto.")";
					$ErrMsg = _('Las operaciones sobre los proyectos para este registro no han sido posibles por que ');
					$DbgMsg = _('El SQL utilizado es:');
					// Se comenta para aligerar carga de pagina
					// $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}
		}
		
		/********************************************************************/
		
		/*eliminamos los clientes para este usuario*/
		$sql="Delete from sec_debtorxuser WHERE userid = '$usuarioseleccionado'";
		$ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		// Se comenta para aligerar carga de pagina
		// $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		/* Agregar permisos de sucursales por usuario*/
		if (isset($_POST['TotalTypedeb'])){
			$TotalPerfiles=$_POST['TotalTypedeb'];
			for ( $perfil = 0 ; $perfil <= $TotalPerfiles ; $perfil ++) {
				
				if ($_POST['TypedebSel'.$perfil]==TRUE){
					
					$NameProfile=$_POST['NameTypedeb'.$perfil];
					$sql="insert into sec_debtorxuser (userid,typeid)";
					$sql=$sql." values('".$usuarioseleccionado."',".$NameProfile.")";
					
					$ErrMsg = _('Las operaciones sobre los perfiles para este registro no han sido posibles por que ');
					$DbgMsg = _('El SQL utilizado es:');
					// Se comenta para aligerar carga de pagina
					// $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}
		}
		/********************************************************************/
		/*eliminamos los proveedores para este usuario*/
		$sql="Delete from sec_supplierxuser WHERE userid = '$usuarioseleccionado'";
		$ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		// Se comenta para aligerar carga de pagina
		// $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		/* Agregar permisos de sucursales por usuario*/
		if (isset($_POST['TotalTypesupp'])){
			$TotalPerfiles=$_POST['TotalTypesupp'];
			for ( $perfil = 0 ; $perfil <= $TotalPerfiles ; $perfil ++) {
				
				if ($_POST['TypesuppSel'.$perfil]==TRUE){
					
					$NameProfile=$_POST['NameTypesupp'.$perfil];
					$sql="insert into sec_supplierxuser (userid,typeid)";
					$sql=$sql." values('".$usuarioseleccionado."',".$NameProfile.")";
					
					$ErrMsg = _('Las operaciones sobre los perfiles para este registro no han sido posibles por que ');
					$DbgMsg = _('El SQL utilizado es:');
					// Se comenta para aligerar carga de pagina
					// $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}
		}

		/*Eliminamos partidas especificas*/

		if ($_SESSION['UserID'] == 'desarrollo') {
			// echo "<br>";
			// echo "<br>totalPartidas: ".$_POST['totalPartidas'];
			// echo "<br>";
		}
		
		$sql="Delete from tb_sec_users_partida WHERE userid = '$usuarioseleccionado'";
		$ErrMsg = ('Las partidas especificas para este registro no han sido posibles por que ');
		$DbgMsg = ('El SQL utilizado es:');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		if (isset($_POST['totalPartidas'])){
			if ($_SESSION['UserID'] == 'desarrollo') {
				// echo "<br>entra if totalPartidas";
			}
			$totalPartidas	= $_POST['totalPartidas'];
			for ( $x = 0 ; $x <= $totalPartidas ; $x ++) {
				if ($_SESSION['UserID'] == 'desarrollo') {
					// echo "<br>x: ".$x." - partida: ".$_POST['partida'.$x];
					if ($_POST['partida'.$x]==TRUE){
						// echo "<br>entra agregar datos";
					}
				}
				if ($_POST['partida'.$x]==TRUE){
					
					$NamePartida 	= $_POST['namePartida'.$x];
					$sql 			= "insert into tb_sec_users_partida (userid,partidacalculada,dtm_fecha_alta)";
					$sql      		= $sql." values('".$usuarioseleccionado."',".$NamePartida.",'".$fechaHoy."')";
					$ErrMsg = ('Las operaciones sobre los perfiles para este registro no han sido posibles por que ');
					$DbgMsg = ('El SQL utilizado es:');
					$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}
		}

		unset($_POST['UserID']);
		unset($_POST['RealName']);
		unset($_POST['Cust']);
		unset($_POST['BranchCode']);
		unset($_POST['Salesman']);
		unset($_POST['Phone']);
		unset($_POST['Email']);
		unset($_POST['department']);
		unset($_POST['Password']);
		unset($_POST['PageSize']);
		unset($_POST['Access']);
		unset($_POST['DefaultLocation']);
		unset($_POST['ModulesAllowed']);
		unset($_POST['Blocked']);
		unset($_POST['Theme']);
		unset($_POST['UserLanguage']);
		unset($SelectedUser);

		$procesoterminado= true;
	}
	//echo 'archivo'.$_FILES['ItemPicture']['name'];
	//exit;
	

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
	// comment out except for demo!  Do not want anyopne deleting demo user.
		$sql='SELECT userid FROM audittrail where userid="'. $SelectedUser .'"';
		$result=DB_query($sql, $db);
		if (DB_num_rows($result)!=0) {
			prnMsg(_('No se puede eliminar al usuario como entradas ya existentes para este usuario'), 'warn');
		} else {

			$sql="DELETE FROM www_users WHERE userid='$SelectedUser'";
			$ErrMsg = _('El usuario no puede ser eliminado por que ');;
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg(_('Usuario eliminado'),'info');
		}
		unset($SelectedUser);
}elseif (isset($_GET['funcion'])) {
/*asignacion de funciones por usuario*/
   	$z=0;
  	echo "<form method='post' enctype='multipart/form-data' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
    $sql = "SELECT * FROM sec_modules s where active=1 ";
    $Result = DB_query($sql, $db);

    if (isset($SelectedUser)) {
		echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'><h4><u>" . _('Mostrar todos los usuarios') . '</u></h4></a></div><br>';
	}

    echo '<table width=90% >';
    echo '<div class="centre">';
    echo "<tr><td style=vertical-align:center;><font style=font-size:15px;><b>"._('Funciones por Usuario')."</b></font></td></tr>";
    echo "</div>";
    
    $encontromodulo=false;
    if (DB_num_rows($Result)>0 ) {
        echo "<tr><td><hr></td></tr>";
	$encontromodulo=true;
    }
    $k=0; //row colour counter
    $j=0;

    while($AvailRow = DB_fetch_array($Result)) {
                            
            if ($k==1){
			echo '<tr class="EvenTableRows">';
                    $k=0;
            } else {
                    echo '<tr class="OddTableRows">';
                    $k=1;
            }
            $moduleid=$AvailRow['moduleid'];
            $namemodule=strtolower($AvailRow['title']);
            echo "<td ><li><font style=font-size:14px;><b>".ucwords($namemodule)."</b></font></li></td></tr>";
            /* trae submodulos*/
            $y=0;
            $a=0;
	    
	    $nombrecategoria="";
	    $nombrecategoryant="";
            $sql = "SELECT * FROM sec_submodules s where active=1 and moduleid='".$moduleid. "' ORDER BY orderno";
            $ReSubmodule = DB_query($sql, $db);
            $condmodule=false;
            if (DB_num_rows($ReSubmodule)>0 ) {
                    echo "<tr><td><table width=100% >";
                    $condmodule=true;
            }
            while($submoduleRow = DB_fetch_array($ReSubmodule)) {
                    if ($y==1){
                            echo '<tr class=EvenTableRows>';
                            $y=0;
                    } else {
                            echo '<tr class=OddTableRows>';
                            $y=1;
                    }
                    $submoduleid=$submoduleRow['submoduleid'];
                    $namesubmodule=strtolower($submoduleRow['title']);
                    echo '<td ><li><font style=font-size:12px;><b>'.ucwords($namesubmodule).'</b></font></li>';
                    /* trae funciones*/
                    $sql = "SELECT distinct title,
							   FP.userid as profilea,
							   FP.functionid as functiona,
							   F.functionid as funcion,
							   F.type as type,
							   C.name as category,
							   case when FP.permiso is null then 1 else FP.permiso end as permiso
						    FROM sec_functions_new F left join  sec_funxuser FP on F.functionid = FP.functionid
						         and FP.userid='".$SelectedUser."',
							 www_users P, sec_categories C
						    WHERE F.active=1 and C.categoryid=F.categoryid
						    and F.SubModuleid=".$submoduleid." order by  C.name,F.type,F.title  ";
                    $ReFuntion = DB_query($sql, $db);
                    $condfuntion=false;
                    if (DB_num_rows($ReFuntion)>0 ) {
                            //echo "<table width=95% CELLPADDING=0 CELLSPACING=0 border=1 bordercolor=black><tr>";
			   				 echo "<table width=95% CELLPADDING=0 CELLSPACING=0 border=1 bordercolor=black>";
                            $condfuntion=true;
                    }
		   			 $nombrecategoryant = "";
		   			 $tipofuncionant="";
                    while($ResFuntion = DB_fetch_array($ReFuntion)) {
                            $functionid=$ResFuntion['funcion'];
                            $nameFuntion=strtolower($ResFuntion['title']);
						    $Funtionval=$ResFuntion['functiona'];
						    $nombrecategoria= strtolower($ResFuntion['category']);
						    $tipofuncion=$ResFuntion['type'];
						    $tienepermiso=$ResFuntion['permiso'];
							if ($nombrecategoria!=$nombrecategoryant and $nombrecategoryant!=''){
								//echo "</table></td>";
								
							    }
							    
						    if ($nombrecategoria!=$nombrecategoryant){
								//echo '<td style=vertical-align:top;><li><b><font style=font-size:12px;>'.ucwords($nombrecategoria).'</font></b></li><table>';
								echo '<tr class=EvenTableRows><td width=40% style=vertical-align:top;><b><font style=font-size:12px;>'.ucwords($nombrecategoria).'</font></b></td>
								<td style=vertical-align:top;><b><font style=font-size:12px;>'._('Administracion de Funciones').'</font></b></td>
								</tr>';
						    }
						    
						    if ($tipofuncion!=$tipofuncionant){
						    	//echo '<tr class=EvenTableRows><td width=40% style=vertical-align:top; colspan="2"><b><font style=font-size:10px;>'.ucwords($tipofuncion).'</font></b></td></tr>';
						    }
						    
						    if (is_null($Funtionval)){
							echo '<tr >
								<td >
								<font style=font-size:12px;>'.$functionid.'&nbsp;&nbsp;&nbsp;'.ucwords($nameFuntion).'</font></td><td>';
								echo '<font style=font-size:12px;><input type=radio name=funcion'.$z.' value="-1" checked>'._('Perfil');	
								echo '<input type=radio name=funcion'.$z.'  value=1>'._('Agregar') .'';
								echo '<input type=radio name=funcion'.$z.' value=0>'._('Quitar') .'</font>';
								echo '<input type=hidden name=fun'.$z.' value=' .$functionid . '>
								</td>
								</tr>';
						    } else{
							echo '<tr>
								<td >
								 <font style=font-size:12px;>'.$functionid.'&nbsp;&nbsp;&nbsp;'.ucwords($nameFuntion).'</font></td><td>';
								  if ($tienepermiso==1) {
								     echo '<font style=font-size:12px;><input type=radio name=funcion'.$z.' value="-1">'._('Perfil');		
								     echo '<input type=radio name=funcion'.$z.' checked value=1>'._('Autorizado') .'';
								     echo '<input type=radio name=funcion'.$z.' value=0  >'._('Quitar') .'</font>';
								  } else {
								     echo '<font style=font-size:12px;><input type=radio name=funcion'.$z.' value="-1">'._('Perfil');		
								     echo '<input type=radio name=funcion'.$z.'  value=1>'._('Agregar') .'';
								     echo '<input type=radio name=funcion'.$z.' value=0 checked >'._('Eliminado') .'</font>';
								  } 
								  echo '<input type=hidden name=fun'.$z.' value=' .$functionid . '>
								</td>
							      </tr>';
						    }
						    $nombrecategoryant=$nombrecategoria;
						    $tipofuncionant=$tipofuncion;
                            $z=$z+1;
                    }//Fin de while para extraer funciones
		    
                    if ($condfuntion==true){// si existian funciones para ese submodulo se cierra la tabla de funciones y submodulos por modulos
			   //echo "</table></td>";
			    echo "</tr></table>";
                    }	
            }
            $condfuntion==false;
	    if ($condmodule==true){// Si existian modulos se cierra la tabla
		echo "</table></td></tr>";
            }
		
            $j=$j+1;
            $submoduleid=0;
            
    }
    echo '</table>';
    echo '<div class="centre"><input type="hidden" name="totalfunciones" value="' .$z . '">';
    echo "<input type='hidden' name='SelectedUser' value='" . $SelectedUser . "'>";
    echo "<input type='hidden' name='UserID' value='" . $_POST['UserID'] . "'></div>";
    echo '<div class="centre"><input type="submit" name="submit" value="' . _('Guardar') . '"></div></form>';

	include('includes/footer_Index.inc');
	exit ;
	
}elseif(isset($_POST['submit']) and $Selectedtotal > 0) {
		//eliminamos los funciones por usuario
		$sql="Delete from sec_funxuser WHERE userid = '".$SelectedUser."'";
		$ErrMsg = _('Las operaciones sobre las funciones para este registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		
		if (isset($Selectedtotal)){
			$totalfunciones=$Selectedtotal-1;
			for ( $funciones = 0 ; $funciones <= $totalfunciones ; $funciones++) {
				if ($_POST['funcion'.$funciones]>=0){
					$idfuncionr=$_POST['fun'.$funciones];
					$permisouser=$_POST['funcion'.$funciones];
					echo "<br>-->" . $idfuncionr . "-->" . $permisouser;
					$sql="insert into sec_funxuser (userid,functionid,permiso)";
					$sql=$sql." values('".$SelectedUser."',".$idfuncionr.",".$permisouser.")";
					echo $sql;
					$ErrMsg = _('Las operaciones sobre las funciones para este registro no han sido posibles por que ');
					$DbgMsg = _('El SQL utilizado es:');
					$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}
			
		}
		
		unset($_POST['UserID']);
		unset($SelectedUser);
		unset($Selectedtotal);
		unset($_POST['total']);
	
}

echo "<form method='post' enctype='multipart/form-data' name='FDatosB' id='FDatosB' action=".$_SERVER['PHP_SELF']. "?".SID.">";

if (!isset($SelectedUser)) {
?>	
<div class="col-md-10">
	<div class="form-inline row">
	  <div class="col-md-1" style="vertical-align: middle;">
	      <span><label>UR: </label></span>
	  </div>
	  <div class="col-md-7">
	      <select id="selectUnidadNegocio" name="filtag" class="form-control selectUnidadNegocio" data-unidad="<?= $_POST['filtag'] ?>" data-todos=true></select>
	  </div>
		<div class="col-md-3">
			<component-button type="submit" id="btnbuscar" name="btnbuscar" class="glyphicon glyphicon-search" value="Buscar Usuarios"></component-button>
		</div>
	</div>
</div>

<br>

<?php
	/*echo "<table style='text-align:center; margin: 0 auto;'>";
	echo "<tr>";
	echo "<td>".('Filtrar por UR').":</td>";
	$sql = "SELECT tags.tagref,
					tags.tagdescription
			FROM tags";
	$result = DB_query($sql, $db);
	echo "<td><select name=filtag>";
	echo "<option selected value='*'>Todas las unidades de negocio</option>";
	while ($row = DB_fetch_array($result)){
		if($_POST['filtag'] == $row['tagref']){
			echo "<option selected value='".$row['tagref']."'>".$row['tagdescription']."</option>";
		}else{
			echo "<option value='".$row['tagref']."'>".$row['tagdescription']."</option>";
		}
	}
	echo "</select></td>";*/
	/*echo "<td>";
	echo '<component-button type="submit" id="btnbuscar" name="btnbuscar" class="glyphicon glyphicon-search" onclick="return false;" value="Buscar Usuarios"></component-button>';
	echo "</td></tr>";
	echo "</table>";*/

	$sql = 'SELECT www_users.userid, www_users.realname, www_users.phone, www_users.email,
		www_users.customerid, www_users.branchcode, www_users.salesman, 
		www_users.lastvisitdate, www_users.fullaccess, www_users.pagesize, www_users.theme,
		www_users.language, areas.areadescription, min(sec_profiles.name) as primerperfil, www_users.blocked 
		FROM www_users 
		INNER JOIN sec_unegsxuser on www_users.userid= sec_unegsxuser.userid
		LEFT JOIN areas ON www_users.defaultarea = areas.areacode
		LEFT JOIN sec_profilexuser ON www_users.userid = sec_profilexuser.userid
		LEFT JOIN  sec_profiles ON sec_profilexuser.profileid = sec_profiles.profileid';

	if (Havepermission($_SESSION['UserID'],932, $db)==0){
		$sql=$sql. '	WHERE www_users.userap = 0';
		if(!empty($_POST['filtag']) and $_POST['filtag'] != '-1'){
			$sql = $sql. " AND sec_unegsxuser.tagref = '".$_POST['filtag']."'";
		}
	}else{
		if(!empty($_POST['filtag']) and $_POST['filtag'] != '-1'){
			$sql = $sql. " WHERE sec_unegsxuser.tagref = '".$_POST['filtag']."'";
		}
	}

	$sql=$sql. ' GROUP BY www_users.userid, www_users.realname, www_users.phone, www_users.email,
		www_users.customerid, www_users.branchcode, www_users.salesman, 
		www_users.lastvisitdate, www_users.fullaccess, www_users.pagesize, 
		www_users.theme,
		www_users.language, areas.areadescription, 		
		www_users.blocked';
			
	$result = DB_query($sql, $db);

	echo '<table class="table table-bordered table-striped">';
	echo "<thead class='header-verde'>";
	echo "<tr class='header-verde'><th><b>#</th><th><b>" . _('Login') . "</th>
		<th><b>" . _('Nombre') . "</th>
		<th><b>" . _('Sucursal') . "</th>
		<th><b>" . _('Email') . "</th>
		<th><b>" . _('Primer Perfil') . "</th>
		<th><b>" . _('Ultima Visita') . "</th>
		<th colspan=3><b>" . _('Operaciones') ."</th>
	</tr>";
	echo "</thead>";

	$k=0; //row colour counter
	$i=0;	
	while ($myrow = DB_fetch_array($result)) {
		
		if ($myrow['blocked'] == 1) {
			echo '<tr bgcolor="#d27786">';
		} else {
			if ($k==1){
				echo '<tr class="OddTableRows">';
				$k=0;
			} else {
				echo '<tr bgcolor="#FFFFFF">';
				$k=1;
			}
		}
		
		$i=$i+1;

		$LastVisitDate = ConvertSQLDate($myrow[7]);

		$enc = new Encryption;
		$url = "&SelectedUser=>" . $myrow[0] . "&funcion=>1";
		$url = $enc->encode($url);
		$liga= "URL=" . $url;

		/*The SecurityHeadings array is defined in config.php */
		printf("<td style='font-weight:normal;font-size:10px;' nowrap>%s</td>
		       <td style='font-weight:normal;font-size:10px;' nowrap>%s</td>
					<td style='font-weight:normal;font-size:10px;' nowrap>%s</td>
					<td style='font-weight:normal;font-size:10px;'>%s</td>
					<td style='font-weight:normal;font-size:10px;'>%s&nbsp;</td>
					<td style='font-weight:normal;font-size:10px;'>%s</td>
					<td style='font-weight:normal;font-size:10px;' nowrap>%s&nbsp;</td>
					<td ><a href=\"%s&SelectedUser=%s\">" . _('Editar') . "</a></td>
					<td ><a href=\"%s&SelectedUser=%s&delete=1\">" . _('Borrar') . "</a></td>",
					$i,
					$myrow[0],
					ucwords(strtolower($myrow[1])),
					ucwords(strtolower($myrow[12])),
					strtolower($myrow[3]),
					ucwords(strtolower($myrow[13])),
					$LastVisitDate,
					$_SERVER['PHP_SELF']  . "?" . SID,
					$myrow[0],
					$_SERVER['PHP_SELF'] . "?" . SID,
					$myrow[0]				
					);

		echo "<td nowrap><a href='".$_SERVER['PHP_SELF']."?".$liga."'>" . _('Agregar Funciones') . "</a></td>";
		echo "</tr>";

	} 
	echo '</table><br>';
} 

if (isset($SelectedUser)) {
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'><h4><u>" . _('Mostrar todos los usuarios') . 
	'</u></h4></a></div><br>';
}

?>

<form method='post' enctype='multipart/form-data' name='FDatosB' id='FDatosB' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">

<div class="panel panel-default" style="width: 60%">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3 text-left">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelUsuario" aria-expanded="true" aria-controls="collapseOne">
            <b>Datos de usuario</b>
          </a>
        </div>
      </h4>
    </div>
    <div id="PanelUsuario" name="PanelUsuario" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" data-funcion="<?= $funcion ?>">
      <div class="panel-body">

<?php

if (isset($SelectedUser)) {
	//editing an existing User

	$sql = "SELECT userid,
			realname,
			phone,
			email,
			department,
			customerid,
			password,
			branchcode,
			salesman,
			pagesize,
			fullaccess,
			defaultarea,
			modulesallowed,
			blocked,
			theme,
			language,
			defaultunidadnegocio,
			discount1,
			discount2,
			discount3,
			creditlimit,
			displayrecordsmax,
			ShowIndex,
			defaultlocation
		FROM www_users
		WHERE userid='" . $SelectedUser . "'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);
	$_POST['UserID'] = $myrow['userid'];
	$_POST['RealName'] = $myrow['realname'];
	$_POST['Phone'] = $myrow['phone'];
	$_POST['Email'] = $myrow['email'];
	$_POST['department'] = $myrow['department'];
	$_POST['Cust']	= $myrow['customerid'];
	$_POST['BranchCode']  = $myrow['branchcode'];
	$_POST['Salesman'] = $myrow['salesman'];
	$_POST['PageSize'] = $myrow['pagesize'];
	$_POST['Access'] = $myrow['fullaccess'];
	$_POST['DefaultLocation'] = $myrow['defaultlocation'];
	$_POST['DefaultUnidad'] = $myrow['defaultunidadnegocio'];
	$_POST['ModulesAllowed'] = $myrow['modulesallowed'];
	$_POST['Theme'] = $myrow['theme'];
	$_POST['UserLanguage'] = $myrow['language'];
	$_POST['Blocked'] = $myrow['blocked'];
	$_POST['discount1'] = $myrow['discount1']*100;
	$_POST['discount2'] = $myrow['discount2']*100;
	$_POST['discount3'] = $myrow['discount3']*100;
	$_POST['creditlimit'] = $myrow['creditlimit'];
	$_POST['displayrecordsmax'] = $myrow['displayrecordsmax'];
	$_POST['ShowIndex']=$myrow['ShowIndex'];

	
	echo "<input type='hidden' name='SelectedUser' value='" . $SelectedUser . "'>";
	echo "<input type='hidden' name='UserID' value='" . $_POST['UserID'] . "'>";
	echo "<input type='hidden' name='ModulesAllowed' value='" . $_POST['ModulesAllowed'] . "'>";
	echo "<input type='hidden' name='DefaultUnidad' value='" . $_POST['DefaultUnidad'] . "'>";

	echo '<table> <tr><td>' . _('Usuario') . ':</td><td>';
	echo $_POST['UserID'] . '</td></tr>';

} else { //end of if $SelectedUser only do the else when a new record is being entered

	if ((isset($_POST['SelectAllTerm'])) or (isset($_POST['DeSelectAllTerm'])) or (isset($_POST['SelectAllCat'])) or (isset($_POST['DeSelectAllCat']))){
		echo '<table><tr><td>' . _('Usuario') . ":</td><td><input type='text' name='UserID' size=22 maxlength=20 value='" . $_POST['UserID'] . "'></td></tr>";
	}else{
		echo '<table><tr><td>' . _('Usuario') . ":</td><td><input type='text' name='UserID' size=22 maxlength=20 value='" . $_POST['UserID'] . "'></td></tr>";
	}
	
	$i=0;
	if (!isset($_POST['ModulesAllowed'])) {
		$_POST['ModulesAllowed']='';
	}
	
	/* AL PARECER ESTO NO HACE NADA, NO EXISTE LA ASIGNACION A $ModuleList
	foreach($ModuleList as $ModuleName){
		if ($i>0){
			$_POST['ModulesAllowed'] .=',';
		}
		$_POST['ModulesAllowed'] .= '1';
		$i++;
	}
	*/
	
}

if (!isset($_POST['Password'])) {
	$_POST['Password']='';
}
if (!isset($_POST['RealName'])) {
	$_POST['RealName']='';
}
if (!isset($_POST['Phone'])) {
	$_POST['Phone']='';
}
if (!isset($_POST['Email'])) {
	$_POST['Email']='';
}

echo '<tr><td>' . _('Password') . ":</td>
	<td><input type='password' name='Password' size=22 maxlength=20 value='" . $_POST['Password'] . "'></tr>";
echo '<tr><td>' . _('Nombre Completo') . ":</td>
	<td><input type='text' name='RealName' value='" . $_POST['RealName'] . "' size=36 maxlength=35></td></tr>";
echo '<tr><td>' . _('No. Telefono') . ":</td>
	<td><input type='text' name='Phone' value='" . $_POST['Phone'] . "' size=32 maxlength=30></td></tr>";
echo '<tr><td>' . _('Email') .":</td>
	<td><input type='text' name='Email' value='" . $_POST['Email'] ."' size=45 >  </td> </tr>";
echo '<tr><td>' . _('Departamento') .":</td>
	<td><input type='text' name='department' value='" . $_POST['department'] ."' size=32 ></td></tr>";

if (!isset($_POST['Cust'])) {
	$_POST['Cust']='';
}
if (!isset($_POST['BranchCode'])) {
	$_POST['BranchCode']='';
}

echo '<tr><td>' . _('Estatus') . ":</td><td><select name='Blocked'>";
if ($_POST['Blocked']==0){
	echo '<option selected value=0>' . _('Activo');
	echo '<option value=1>' . _('Inactivo');
} else {
 	echo '<option selected value=1>' . _('Activo');
	echo '<option value=0>' . _('Inactivo');
}
echo '</select></td></tr>';


echo '<tr style="display: none;"><td>' . _('Opcion Menu') . ":</td><td><select name='ShowIndex' >";
if ($_POST['ShowIndex']==0){
	echo '<option selected value=0>' . _('Modular');
	echo '<option value=1>' . _('Vertical');
	echo '<option value=2>' . _('Horizontal');
} elseif ($_POST['ShowIndex']==1 || $_SESSION['ShowIndex']==1){
	echo '<option  value=0>' . _('Modular');
	echo '<option selected value=1>' . _('Vertical');
	echo '<option value=2>' . _('Horizontal');
}else{
	echo '<option value=0>' . _('Modular');
	echo '<option value=1>' . _('Vertical');
	echo '<option selected value=2>' . _('Horizontal');
}
echo '</select></td></tr>';

echo '<tr style="display: none;"><td>' . _('Descuento Maximo 1') . ':</td>
	<td><input type="text" class="number" name="discount1" size=10 maxlength=5 VALUE="' . $_POST['discount1'] .'"></td></tr>';

echo '<tr style="display: none;"><td>' . _('Descuento Maximo 2') . ':</td>
	<td><input type="text" class="number" name="discount2" size=10 maxlength=5 VALUE="' . $_POST['discount2'] .'"></td></tr>';
	

echo '<tr style="display: none;"><td>' . _('Descuento Maximo 3') . ':</td>
	<td><input type="text" class="number" name="discount3" size=10 maxlength=5 VALUE="' . $_POST['discount3'] .'"></td></tr>';	

echo '<tr style="display: none;"><td>' . _('L�mite de Nota Credito Directa') . ':</td>
	<td><input type="text" class="number" name="creditlimit" size=15 maxlength=20 VALUE="' . $_POST['creditlimit'] .'"></td></tr>';	

echo '<tr style="display: none;"><td>' . _('M�ximo de Registros a Mostrar') . ':</td>
	<td><input type="text" class="number" name="displayrecordsmax" size="5" maxlength="5" VALUE="' . $_POST['displayrecordsmax'] .'"></td></tr>';

echo '<tr><td>'._('Imagen').':</td>';
echo '<td><input type="file" id="ItemPicture" name="ItemPicture"></td></tr>';

echo '<tr><td>'._('Almacen por Default').':</td>';
echo '<td>
<select id="DefaultLocation" name="DefaultLocation" class="">';
$qry = "SELECT DISTINCT locations.loccode, CONCAT(locations.loccode,' - ',locations.locationname) as locationname
    FROM locations, sec_loccxusser
    WHERE 
    locations.loccode=sec_loccxusser.loccode 
    AND sec_loccxusser.userid='" . $_SESSION['UserID'] . "'
    ORDER BY locationname";
echo "<option value='-1'>Seleccionar...</option>";
$rscurr = DB_query($qry, $db);
while ($rowcurr = DB_fetch_array($rscurr)) {
    if ($rowcurr['loccode'] == $_POST['DefaultLocation']) {
        echo "<option selected value='".$rowcurr['loccode']."'>".$rowcurr['locationname']."</option>";
    } else {
        echo "<option value='".$rowcurr['loccode']."'>".$rowcurr['locationname']."</option>";
    }
}
echo '</select>
</td></tr>';
	
/*if (isset($SelectedUser)) {
 echo '<tr border=1 style="display: none;"><th colspan=3>' . _('ASIGNACION DE DIAS Y LIMITES DE CREDITO') . '</th>';
	//esta parte sirve para mostrar la primera tabla con todos los registros existentes
	
	$sqlrutas="SELECT day.id_depto,day.numdias,day.id_usuario,day.limitecredit,d.department
		FROM limitxusuario as day
		INNER JOIN departments as d on day.id_depto=d.u_department
		where id_usuario='".$SelectedUser."'";
		$ErrMsg = _('No se realizo bien la consulta');
		$resultrutas = DB_query($sqlrutas,$db,$ErrMsg);
		echo "<tr style='display: none;'><th>" . _('Departamento') . "</th>
		<th>" . _('Limite de Credito') . "</th>
		<th>" . _('Dias Credito') . "</th>
		</tr>";
		while ($myrow = DB_fetch_array($resultrutas)) {
		printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			</tr>",
			$myrow['id_depto'].'-'.' '.$myrow['department'],
			$myrow['limitecredit'],
			$myrow['numdias']
			);
		}
		
		//$usuario=$SelectedUser;
	echo '<td colspan=3 style="text-align:center;"> <a href="'. $rootpath .'/ReporteLimitxUsuario.php?' . SID .'&SelectedUser=' .$SelectedUser.'" title="Asignar L�mites de Cr�dito">Asignar L�mites de Cr�dito</a></td></tr>';
}*/

echo "</table>";
echo "</div>";
echo "</div>";
echo "</div>";

if (isset($_GET['SelectedUser'])) {
//	echo '<script  type="text/javascript">defaultControl(document.forms[0].Password);</script>';
} else {
//	echo '<script  type="text/javascript">defaultControl(document.forms[0].UserID);</script>';
}
if (isset($_POST['UserID'])){
	$usuario=$_POST['UserID'];	
} else {
	$usuario=0;	
}

?>

<div class="panel panel-default" style="width: 60%">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-5 col-xs-5 text-left">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelAlmacenes" aria-expanded="true" aria-controls="collapseOne">
            <b>Almacenes por usuario</b>
          </a>
        </div>
      </h4>
    </div>
    <div id="PanelAlmacenes" name="PanelAlmacenes" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" data-funcion="<?= $funcion ?>">
      <div class="panel-body">

<?php

/* ---------------------Asignacion de sucursales por usuario---------------------------------------------- */
echo "<table>";
//echo '<tr><td colspan=2 align=center> &nbsp;&nbsp;&nbsp;<li>'. _('Almacenes por Usuario').'</li></td></tr>';

echo "<tr><td colspan=2 align='center'>";
	echo "<input style='font-size:11px;' type=button Name='SelectAllTerm' onclick=SelectCheckAuto(1,11) Value='" . _('Sel. Todos') . "'>";
        echo "<input style='font-size:11px;' type=button Name='DeSelectAllTerm'  onclick='DesSelectCheckAuto(1,11);'  Value='" . _('Quitar Sel. a todos') . "'>";
echo "</tr>";

echo '<tr><td colspan=2>';
	$sql = "SELECT L.loccode as codsuc,U.loccode as coduser, L.locationname as suc, tags.tagname 
			FROM locations L left join sec_loccxusser U on  L.loccode = U.loccode and U.userid='".$usuario."'
			LEFT JOIN tags ON L.tagref = tags.tagref
			ORDER BY tags.tagname, L.locationname";
	$Result = DB_query($sql, $db);
	echo '<table width=80% align=center>';
	if (DB_num_rows($Result)>0 ) {
		//echo "<tr><td ><hr></td></tr>";
	}
	$k=0; //row colour counter
	$j=0;
	$tagname = "";
	while($AvailRow = DB_fetch_array($Result)) {
		
		if ($tagname != $AvailRow['tagname']) {
			echo "<tr><th style='text-align:left; font-weight:bold'>" . $AvailRow['tagname'] . "</th></tr>";
			$tagname = $AvailRow['tagname'];
		}
		
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		
		echo '<td nowrap style="font-weight:normal;">';
		$sucursal=$AvailRow['codsuc'];
		$coduser=$AvailRow['coduser'];
		$nombresuc=$AvailRow['suc'];
		if(is_null($coduser)) {
			if ((isset($_POST['SucursalSel'.$j])) and  ($_POST['SucursalSel'.$j]<> '')){
				echo '<INPUT type="checkbox" name="SucursalSel'.$j.'" checked value="' . ($j+1) . '">';
			}else{
				echo '<INPUT type="checkbox" name="SucursalSel'.$j.'" value="' . ($j+1). '">';
			}
			echo '<INPUT type=hidden name=Namesucursal'.$j.' value='.$sucursal.' >';
			echo $nombresuc;

		} else{
			echo '<INPUT type=checkbox name=SucursalSel'.$j.' checked value="' . ($j+1) . '">';
			echo '<INPUT type=hidden name=Namesucursal'.$j.' value='.$sucursal.' >';
			echo $nombresuc;
		}
		$j=$j+1;
		echo '</td>';
		echo '</tr>';
	}
	echo '<tr><td colspan=2><input type=hidden name=TotalSucursal value='.$j.'></td></tr>';
	echo '</table>';
	
echo '</td></tr>';
echo '</table>';
echo "</div></div></div>";

/* Asignacion de Unidades de negocio por usuario */
?>
<div class="panel panel-default" style="width: 60%">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-5 col-xs-5 text-left">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelUnidadesRes" aria-expanded="true" aria-controls="collapseOne">
            <b>Unidades Responsables</b>
          </a>
        </div>
      </h4>
    </div>
    <div id="PanelUnidadesRes" name="PanelUnidadesRes" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" data-funcion="<?= $funcion ?>">
      <div class="panel-body">

<?php
echo '<table>';
//echo '<tr><td colspan=2 align=center> &nbsp;&nbsp;&nbsp;<li>'. _('Unidades Responsables').'</li></td></tr>';
echo '<tr><td colspan=2>';
	$sql = "SELECT L.tagref as codsuc,U.tagref as coduser, L.tagdescription as suc, legalbusinessunit.legalname 
			FROM tags L left join sec_unegsxuser U on  L.tagref = U.tagref and U.userid='".$usuario."'
			LEFT JOIN legalbusinessunit ON legalbusinessunit.legalid = L.legalid
			ORDER BY legalbusinessunit.legalname, L.tagdescription";
	$Result = DB_query($sql, $db);
	
	echo '<table width=80% align=center>';
	
	$k=0; //row colour counter
	$j=0;
	$legalname = "";
	
	while($AvailRow = DB_fetch_array($Result)) {

		if ($legalname != $AvailRow['legalname']) {
			echo "<tr><th style='text-align:left; font-weight:bold'>" . $AvailRow['legalname'] . "</th></tr>";
			$legalname = $AvailRow['legalname'];
		}
		
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		
		echo '<td nowrap style="font-weight:normal;">';
		$sucursal=$AvailRow['codsuc'];
		$coduser=$AvailRow['coduser'];
		$nombresuc=$AvailRow['suc'];
		if(is_null($coduser)) {
			if ((isset($_POST['UNSel'.$j])) and  ($_POST['UNSel'.$j]<> '')){
				echo '<INPUT type=checkbox name=UNSel'.$j.' checked value="' . ($j+1) . '">';
			}else{
				echo '<INPUT type=checkbox name=UNSel'.$j.' value="' . ($j+1) . '">';
			}
			echo '<INPUT type=hidden name=NameUnidad'.$j.' value='.$sucursal.' >';
			echo ucwords(strtolower($sucursal." - ".$nombresuc));
		} else{
			echo '<INPUT type=checkbox name=UNSel'.$j.' checked value="' . ($j+1) . '">';
			echo '<INPUT type=hidden name=NameUnidad'.$j.' value='.$sucursal.' >';
			echo ucwords(strtolower($sucursal." - ".$nombresuc));
		}
		$j=$j+1;
		echo '</td>';
		echo '</tr>';
	}
	echo '<tr><td colspan=2><input type=hidden name=TotalUnidades value='.$j.'></td></tr>';
	echo '</table>';
echo '</td></tr>';
echo '</table>';
echo "</div></div></div>";

/* Asignacion de Unidades Ejecutoras por usuario */
	$sql = "SELECT `us`.`tagref` AS 'UR'

			FROM `sec_unegsxuser` AS `us`
			LEFT JOIN `tags` AS `ur` ON `ur`.`tagref` = `us`.`tagref`
			LEFT JOIN `legalbusinessunit` AS `i` ON `i`.`legalid` = `ur`.`legalid`

			WHERE `userid` = '$usuario'

			ORDER BY `i`.`legalname` ASC, `ur`.`tagdescription` ASC";
	// Esta nueva consulta carga todas las UR activas que cuenten con UE activas
	$sql = "SELECT `unre`.`tagref` AS 'UR'

			FROM `tags` AS `unre`
			LEFT JOIN `tb_cat_unidades_ejecutoras` AS `unej` ON `unej`.`ur` = `unre`.`tagref`
			LEFT JOIN `legalbusinessunit` AS `i` ON `i`.`legalid` = `unre`.`legalid`

			WHERE `unre`.`tagactive` = 1
			AND `unej`.`active` = 1

			GROUP BY `unre`.`tagref`
			ORDER BY `i`.`legalname` ASC, `unre`.`tagdescription` ASC;";
	$ResultadoURS = DB_query($sql, $db);

	$URS = array();
	while($UR = DB_fetch_array($ResultadoURS)['UR']){
		$URS[] = $UR;
?>
<div class="panel panel-default" style="width: 60%">
	<div class="panel-heading" role="tab" id="headingOne">
		<h4 class="panel-title row">
			<div class="col-md-5 col-xs-5 text-left">
				<a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelUE<?= $UR; ?>" aria-expanded="true" aria-controls="collapseOne">
				<b>Unidad Responsable <?= $UR; ?> - Unidades Ejecutoras</b>
				</a>
			</div>
		</h4>
	</div>
	<div id="PanelUE<?= $UR; ?>" name="PanelUE<?= $UR; ?>" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" data-funcion="<?= $funcion ?>">
		<div class="panel-body">

<?php
echo '<table>';
//echo '<tr><td colspan=2 align=center> &nbsp;&nbsp;&nbsp;<li>'. _('Unidades Responsables').'</li></td></tr>';
echo '<tr><td colspan=2>';
	$sql = "SELECT `us`.`userid` AS 'usuario', `unej`.`ue` AS 'valor', `unej`.`desc_ue` AS 'label'

			FROM `tb_cat_unidades_ejecutoras` AS `unej`
			LEFT JOIN `tb_sec_users_ue` AS `us` ON `us`.`tagref` = `unej`.`ur` AND `us`.`ue` = `unej`.`ue` AND `us`.`userid` = '$usuario'

			WHERE `unej`.`ur` = '$UR'
			AND `active` = 1

			ORDER BY LENGTH(`unej`.`ue`) ASC, `unej`.`ue` ASC";
	$Result = DB_query($sql, $db);

	echo ( !DB_num_rows($Result) ? "No se encontrarosn Unidades Ejecutoras pertenecientes a la Unidad Responsable $UR" : "" );////
	
	echo '<table width=80% align=center>';
	
	$k=0; //row colour counter
	$j=0;
	$legalname = "";
	
	while($UE = DB_fetch_array($Result)) {
		
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		
		echo '<td nowrap style="font-weight:normal;">';
		$yaExistente = $UE['usuario'];
		$valor = $UE['valor'];
		$label = $UE['label'];
		$checked = ( !is_null($yaExistente)||( isset($_POST["UE-$UR$j"])&&$_POST["UE-$UR$j"]<>"" ) ? " checked" : "" );
		echo '<label>';
		echo '<input type="checkbox" name="UE-'.$UR.$j.'"'.$checked.' value="' . ($j+1) . '">';
		echo '<input type="hidden" name="Nombre-UE-'.$UR.$j.'" value='.$valor.' >';
		echo " ".ucwords(strtolower($valor." - ".$label));
		echo '</label>';
		echo '</td>';
		echo '</tr>';
		$j++;
	}
	echo '<tr><td colspan=2><input type="hidden" name="TotalUE-'.$UR.'" value="'.$j.'"></td></tr>';
	echo '</table>';
echo '</td></tr>';
echo '</table>';?>
		</div>
	</div>
</div><?php
	}
	$valoresURS = "";
	if(is_array($URS)&&count($URS)){
		$valoresURS = implode(",",$URS);
	}
	echo '<tr><td colspan=2><input type="hidden" name="URS" value="'.$valoresURS.'"></td></tr>';
?>

<?php
echo '<table style="display: none;">';
echo '<tr><td colspan=2 align=center> &nbsp;&nbsp;&nbsp;<li>'. _('Lista de Precio por Usuario').'</li></td></tr>';
echo '<tr><td colspan=2>';
	$sql = "SELECT L.typeabbrev as codsuc,U.pricelist as coduser, L.sales_type as suc
		FROM salestypes L left join sec_pricelist U on  L.typeabbrev = U.pricelist
			AND U.userid='".$usuario."'";
	$sql = ""; // Se comenta para aligerar carga de pagina
	$Result = DB_query($sql, $db);
	echo '<table width=80% align=center>';
	if (DB_num_rows($Result)>0 ) {
		echo "<tr><td ><hr></td></tr>";
	}
	$k=0; //row colour counter
	$j=0;
	while($AvailRow = DB_fetch_array($Result)) {
				
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		echo '<td nowrap style="font-weight:normal;">';
		$price=$AvailRow['codsuc'];
		$codprice=$AvailRow['coduser'];
		$nombreprice=$AvailRow['suc'];
		if(is_null($codprice)) {
			if ((isset($_POST['PrecioSel'.$j])) and  ($_POST['PrecioSel'.$j]<> '')){
				echo '<INPUT type=checkbox name=PrecioSel'.$j.' checked value="' . ($j+1) . '">';
			}else{
				echo '<INPUT type=checkbox name=PrecioSel'.$j.' value="' . ($j+1) . '">';
			}
			echo '<INPUT type=hidden name=Nameprecio'.$j.' value='.$price.' >';
			
			echo ucwords(strtolower($nombreprice));
		} else{
			echo '<INPUT type=checkbox name=PrecioSel'.$j.' checked value="' . ($j+1) . '">';
			echo '<INPUT type=hidden name=Nameprecio'.$j.' value='.$price.' >';
			echo ucwords(strtolower($nombreprice));
		}
		$j=$j+1;
		echo '</td>';
		echo '</tr>';
	}
	echo '<tr><td colspan=2><input type=hidden name=TotalPrices value='.$j.'></td></tr>';
	echo '</table>';
	
echo '</td></tr>';
echo '</table>';

/* ---------------------Asignacion de lista de terminos de pago por usuario---------------------------------------------- */
echo '<table style="display: none;">';
echo '<tr><td colspan=2 align=center> &nbsp;&nbsp;&nbsp;<li>'. _('Terminos de Pago por Usuario').'</li></td></tr>';
echo "<tr><td colspan=2 align='center'>";
	echo "<input style='font-size:11px;' type=Submit Name='SelectAllTerm' Value='" . _('Sel. Todos') . "'>";
        echo "<input style='font-size:11px;' type=Submit Name='DeSelectAllTerm' Value='" . _('Quitar Sel. a todos') . "'>";
echo "</tr>";
echo '<tr><td colspan=2>';
	$sql = "SELECT L.termsindicator as codsuc,U.termsindicator as coduser, L.terms as suc
		FROM paymentterms L left join sec_paymentterms U on  L.termsindicator = U.termsindicator
			AND U.userid='".$usuario."'";
	$sql = ""; // Se comenta para aligerar carga de pagina
	$Result = DB_query($sql, $db);
	echo '<table width=80% align=center>';
	if (DB_num_rows($Result)>0 ) {
		echo "<tr><td ><hr></td></tr>";
	}
	$k=0; //row colour counter
	$j=0;
	while($AvailRow = DB_fetch_array($Result)) {
				
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		echo '<td nowrap style="font-weight:normal;">';
		$codpayterm=$AvailRow['codsuc'];
		$coduserpayterms=$AvailRow['coduser'];
		$nombrepayterms=$AvailRow['suc'];
		if(is_null($coduserpayterms)) {
			if (isset($_POST['SelectAllTerm'])){
				echo '1<INPUT type=checkbox name=PaymenttermsSel'.$j.' checked value="' . ($j+1) . '">';
			}elseif (isset($_POST['DeSelectAllTerm'])){
				echo '2<INPUT type=checkbox name=PaymenttermsSel'.$j.' value="' . ($j+1) . '">';
			}else{
				if ((isset($_POST['PaymenttermsSel'.$j])) and  ($_POST['PaymenttermsSel'.$j]<> '')){
					echo '3<INPUT type=checkbox name=PaymenttermsSel'.$j.' checked value="' . ($j+1) . '">';
				}else{
					echo '4<INPUT type=checkbox name=PaymenttermsSel'.$j.' value="' . ($j+1) . '">';
				}
				
				//echo '<INPUT type=checkbox name=PaymenttermsSel'.$j.' value="' . $j . '">';
			}
			echo '5<INPUT type=hidden name=Namepaymentterms'.$j.' value='.$codpayterm.' >';
			echo ucwords(strtolower($nombrepayterms));
		} else{
			echo '6<INPUT type=checkbox name=PaymenttermsSel'.$j.' checked value="' . ($j+1) . '">';
			echo '<INPUT type=hidden name=Namepaymentterms'.$j.' value='.$codpayterm.' >';
			echo ucwords(strtolower($nombrepayterms));
		}
		$j=$j+1;
		echo '</td>';
		echo '</tr>';
	}
	echo '<tr><td colspan=2><input type=hidden name=TotalPayterms value='.$j.'></td></tr>';
	echo '</table>';
	
echo '</td></tr>';
echo '</table>';


/* ---------------------Asignacion de estatus de creditos por usuario---------------------------------------------- */
echo '<table style="display: none;">';
echo '<tr><td colspan=2 align=center> &nbsp;&nbsp;&nbsp;<li>'. _('Estatus de Credito por Usuario').'</li></td></tr>';
echo '<tr><td colspan=2>';
	$sql = "SELECT L.reasoncode as codsuc, U.reasoncode as coduser, L.reasondescription as suc
		FROM holdreasons L left join sec_holdreasons U on  L.reasoncode = U.reasoncode
			AND U.userid='".$usuario."'";
	$sql = ""; // Se comenta para aligerar carga de pagina
	$Result = DB_query($sql, $db);
	echo '<table width=80% align=center>';
	if (DB_num_rows($Result)>0 ) {
		echo "<tr><td ><hr></td></tr>";
	}
	$k=0; //row colour counter
	$j=0;
	while($AvailRow = DB_fetch_array($Result)) {
				
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		echo '<td nowrap style="font-weight:normal;">';
		$codholdreason=$AvailRow['codsuc'];
		$coduserholdreason=$AvailRow['coduser'];
		$nombreholdreason=$AvailRow['suc'];
		if(is_null($coduserholdreason)) {
			if ((isset($_POST['holdreasonsSel'.$j])) and  ($_POST['holdreasonsSel'.$j]<> '')){
				echo '<INPUT type=checkbox name=holdreasonsSel'.$j.' checked value="' . ($j+1) . '">';
			}else{
				echo '<INPUT type=checkbox name=holdreasonsSel'.$j.' value="' . ($j+1) . '">';
			}
			
			echo '<INPUT type=hidden name=Nameholdreason'.$j.' value='.$codholdreason.' >';
			echo ucwords(strtolower($nombreholdreason));
		} else{
			echo '<INPUT type=checkbox name=holdreasonsSel'.$j.' checked value="' . ($j+1). '">';
			echo '<INPUT type=hidden name=Nameholdreason'.$j.' value='.$codholdreason.' >';
			echo ucwords(strtolower($nombreholdreason));
		}
		$j=$j+1;
		echo '</td>';
		echo '</tr>';
	}
	echo '<tr><td colspan=2><input type=hidden name="TotalHoldreasons" value='.$j.'></td></tr>';
	echo '</table>';
	
echo '</td></tr>';
echo '</table>';


/* ---------------------Asignacion de categorias de inventario por usuario---------------------------------------------- */
echo '<table style="display: none;">';
echo '<tr><td colspan=2 align=center> &nbsp;&nbsp;&nbsp;<li>'. _('Categorias de Inventario por Usuario').'</li></td></tr>';
echo "<tr><td colspan=2 align='center'>";
	echo "<input style='font-size:11px;' type=Submit Name='SelectAllCat' Value='" . _('Sel. Todos') . "'>";
        echo "<input style='font-size:11px;' type=Submit Name='DeSelectAllCat' Value='" . _('Quitar Sel. a todos') . "'>";
echo "</tr>";
echo '<tr><td colspan=2>';
	$sql = "SELECT L.categoryid as codsuc, U.categoryid as coduser, L.categorydescription as suc, ProdLine.Description as Linea
		FROM stockcategory L left join sec_stockcategory U on  L.categoryid = U.categoryid
			AND U.userid='".$usuario."'
			left join ProdLine ON L.prodLineId = ProdLine.Prodlineid
		ORDER BY ProdLine.Description, L.categorydescription ";
	$sql = ""; // Se comenta para aligerar carga de pagina
	$Result = DB_query($sql, $db);
	echo '<table width=80% align=center>';
	if (DB_num_rows($Result)>0 ) {
		echo "<tr><td ><hr></td></tr>";
	}
	$k=0; //row colour counter
	$j=0;
	$anteriorLinea = "";
	while($AvailRow = DB_fetch_array($Result)) {
				
		if ($anteriorLinea != $AvailRow['Linea']) {
			echo "<tr><td ><hr></td></tr>";
			echo '<tr style="background-color:yellow"><td>';
			echo $AvailRow['Linea'].'</td></tr>';
			$anteriorLinea = $AvailRow['Linea'];
		}
		
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		echo '<td nowrap style="font-weight:normal;">';
		$codcategory=$AvailRow['codsuc'];
		$codusercategory=$AvailRow['coduser'];
		$nombrecategory=$AvailRow['suc'];
		if(is_null($codusercategory)) {
			if (isset($_POST['SelectAllCat'])){
				echo '<INPUT type=checkbox name=categorysSel'.$j.' checked value="'  . ($j+1) . '">';
			}elseif (isset($_POST['DeSelectAllCat'])){
				echo '<INPUT type=checkbox name=categorysSel'.$j.' value="'  . ($j+1) . '">';
			}else{
				if ((isset($_POST['categorysSel'.$j])) and  ($_POST['categorysSel'.$j]<> '')){
					echo '<INPUT type=checkbox name=categorysSel'.$j.' checked value="'  . ($j+1) . '">';
				}else{
					echo '<INPUT type=checkbox name=categorysSel'.$j.' value="'  . ($j+1) . '">';
				}
			}
			
			
			echo '<INPUT type=hidden name=Namecategory'.$j.' value="'.$codcategory.'" >';
			echo ucwords(strtolower($nombrecategory));
		} else{
			echo '<INPUT type=checkbox name=categorysSel'.$j.' checked value="'  . ($j+1) . '">';
			echo '<INPUT type=hidden name=Namecategory'.$j.' value="'.$codcategory.'" >';
			echo ucwords(strtolower($nombrecategory));
		}
		$j=$j+1;
		echo '</td>';
		echo '</tr>';
	}
	echo '<tr><td colspan=2><input type=hidden name="TotalCategorys" value='.$j.'></td></tr>';
	echo '</table>';
	
echo '</td></tr>';
echo '</table>';

/* Asignacion de Perfiles por usuario */
?>
<div class="panel panel-default" style="width: 60%">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-5 col-xs-5 text-left">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelPerfiles" aria-expanded="true" aria-controls="collapseOne">
            <b>Perfiles de Usuario</b>
          </a>
        </div>
      </h4>
    </div>
    <div id="PanelPerfiles" name="PanelPerfiles" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" data-funcion="<?= $funcion ?>">
      <div class="panel-body">

<?php
echo '<table>';
//echo '<tr><td colspan=2 align=center> &nbsp;&nbsp;&nbsp;<li>'. _('Perfiles por Usuario').'</li></td></tr>';
echo '<tr><td colspan=2>';
	$sql = "SELECT S.profileid as codprofile,U.userid as coduser, S.name as profile
		FROM sec_profiles S left join sec_profilexuser U on  S.profileid = U.profileid and U.userid='".$usuario."'
		ORDER BY S.name";
	$Result = DB_query($sql, $db);
	echo '<table width=80% align=center>';
	if (DB_num_rows($Result)>0 ) {
		//echo "<tr><td ><hr></td></tr>";
	}
	$k=0; //row colour counter
	$j=0;
	while($AvailRow = DB_fetch_array($Result)) {
				
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		echo '<td nowrap style="font-weight:normal;">';
		$codprofile=$AvailRow['codprofile'];
		$coduserp=$AvailRow['coduser'];
		$nombreprofile=$AvailRow['profile'];
		if(is_null($coduserp)) {
			if ((isset($_POST['PerfilSel'.$j])) and  ($_POST['PerfilSel'.$j]<> '')){
				echo '<INPUT type=checkbox name=PerfilSel'.$j.' checked value="' . ($j+1) . '">';
			}else{
				echo '<INPUT type=checkbox name=PerfilSel'.$j.' value="' . ($j+1) . '">';
			}
			echo '<INPUT type=hidden name=NameProfile'.$j.' value='.$codprofile.' >';
			echo ucwords(strtolower($nombreprofile));
		} else{
			echo '<INPUT type=checkbox name=PerfilSel'.$j.' checked value="' . ($j+1) . '">';
			echo '<INPUT type=hidden name=NameProfile'.$j.' value='.$codprofile.' >';
			echo ucwords(strtolower($nombreprofile));
		}
		$j=$j+1;
		echo '</td>';
		echo '</tr>';
	}
	echo '<tr><td colspan=2><input type=hidden name=TotalPerfiles value='.$j.'></td></tr>';
	echo '</table>';
echo '</td></tr>';
echo '</table>';
echo "</div></div></div>";

/* Asignacion de Perfiles por usuario */


/* Asignacion de Capitulos por usuario */
?>
<div class="panel panel-default" style="width: 60%">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-5 col-xs-5 text-left">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelCapitulo" aria-expanded="true" aria-controls="collapseOne">
            <b>Capitulos de Usuario</b>
          </a>
        </div>
      </h4>
    </div>
    <div id="PanelCapitulo" name="PanelCapitulo" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" data-funcion="<?= $funcion ?>">
      <div class="panel-body">

<?php
echo '<table>';
//echo '<tr><td colspan=2 align=center> &nbsp;&nbsp;&nbsp;<li>'. _('Perfiles por Usuario').'</li></td></tr>';
echo '<tr><td colspan=2>';
	$sql = "SELECT S.profileid as codCap,U.userid as coduser, S.name as nombre
		FROM sec_profiles S left join sec_profilexuser U on  S.profileid = U.profileid and U.userid='".$usuario."'
		ORDER BY S.name";
	$sql = "SELECT distinct tb_cat_partidaspresupuestales_capitulo.ccapmiles as codCap, CONCAT(tb_cat_partidaspresupuestales_capitulo.ccapmiles, ' - ', tb_cat_partidaspresupuestales_capitulo.descripcion) as nombre, sec_capituloxuser.sn_userid as coduser
	FROM tb_cat_partidaspresupuestales_capitulo 
	LEFT JOIN sec_capituloxuser ON sec_capituloxuser.sn_capitulo = tb_cat_partidaspresupuestales_capitulo.ccapmiles AND sec_capituloxuser.sn_userid = '".$usuario."'
	WHERE tb_cat_partidaspresupuestales_capitulo.activo = 1 
	ORDER BY CAST(tb_cat_partidaspresupuestales_capitulo.ccapmiles AS SIGNED) ASC";
	$Result = DB_query($sql, $db);
	echo '<table width=80% align=center>';
	if (DB_num_rows($Result)>0 ) {
		//echo "<tr><td ><hr></td></tr>";
	}
	$k=0; //row colour counter
	$j=0;
	while($AvailRow = DB_fetch_array($Result)) {
				
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		echo '<td nowrap style="font-weight:normal;">';
		$codCap=$AvailRow['codCap'];
		$coduserp=$AvailRow['coduser'];
		$nombre=$AvailRow['nombre'];
		if(is_null($coduserp)) {
			if ((isset($_POST['CapituloSel'.$j])) and  ($_POST['CapituloSel'.$j]<> '')){
				echo '<INPUT type=checkbox name=CapituloSel'.$j.' checked value="' . ($j+1) . '">';
			}else{
				echo '<INPUT type=checkbox name=CapituloSel'.$j.' value="' . ($j+1) . '">';
			}
			echo '<INPUT type=hidden name=NameCapitulo'.$j.' value='.$codCap.' >';
			echo ucwords(strtolower($nombre));
		} else{
			echo '<INPUT type=checkbox name=CapituloSel'.$j.' checked value="' . ($j+1) . '">';
			echo '<INPUT type=hidden name=NameCapitulo'.$j.' value='.$codCap.' >';
			echo ucwords(strtolower($nombre));
		}
		$j=$j+1;
		echo '</td>';
		echo '</tr>';
	}
	echo '<tr><td colspan=2><input type=hidden name=TotalCapitulos value='.$j.'></td></tr>';
	echo '</table>';
echo '</td></tr>';
echo '</table>';
echo "</div></div></div>";

/* Asignacion de Capitulos por usuario */

/* Partidas Especificas */
?>
<div class="panel panel-default" style="width: 100%">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-5 col-xs-5 text-left">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelCapitulo" aria-expanded="true" aria-controls="collapseOne">
            <b>Partidas Especificas</b>
          </a>
        </div>
      </h4>
    </div>
    <div id="PanelCapitulo" name="PanelCapitulo" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" data-funcion="<?= $funcion ?>">
      <div class="panel-body">
	  
	  <?php
echo '<table>';
//echo '<tr><td colspan=2 align=center> &nbsp;&nbsp;&nbsp;<li>'. _('Perfiles por Usuario').'</li></td></tr>';
echo '<tr><td colspan=2>';
	$sql = "SELECT cat.partidacalculada, cat.descripcion, pa.partidacalculada AS checked
	FROM tb_cat_partidaspresupuestales_partidaespecifica cat LEFT JOIN tb_sec_users_partida pa ON cat.partidacalculada=pa.partidacalculada AND userid= '".$usuario."'
	WHERE activo = 1 order by cat.descripcion ";
	$Result = DB_query($sql, $db);
	echo '<table width=80% align=center>';
	if (DB_num_rows($Result)>0 ) {
		//echo "<tr><td ><hr></td></tr>";
	}
	$k=0; //row colour counter
	$j=0;
	while($AvailRow = DB_fetch_array($Result)) {
				
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		echo '<td nowrap style="font-weight:normal;">';
		$partidacalculada   = $AvailRow['partidacalculada'];
		$descripcion        = $AvailRow['descripcion'];
		$nombre             = $partidacalculada ." - ".$descripcion;
		$checked            = $AvailRow['checked'];
		  if ($checked != null ){
			echo '<INPUT type=checkbox name="partida'.$j.'" checked  value="1">';
		  }else{
			echo '<INPUT type=checkbox name="partida'.$j.'"  value="1">';
		  }
			
			echo ucwords(strtolower($nombre));
			echo '<INPUT type=hidden name=namePartida'.$j.' value='.$partidacalculada.' >';
		$j=$j+1;
		echo '</td>';
		echo '</tr>';
	}
	echo '<tr><td colspan=2><input type=hidden name=totalPartidas value='.$j.'></td></tr>';
	echo '</table>';
echo '</td></tr>';
echo '</table>';
echo "</div></div></div>";

/* Partidas Presupuestales */
?>
<div class="panel panel-default" style="width: 60%; display: none;">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-4 col-xs-4 text-left">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelRoles" aria-expanded="true" aria-controls="collapseOne">
            <b>Roles por Usuario</b>
          </a>
        </div>
      </h4>
    </div>
    <div id="PanelRoles" name="PanelRoles" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" data-funcion="<?= $funcion ?>">
      <div class="panel-body">

<?php
echo '<table>';
//echo '<tr><td colspan=2 align=center> &nbsp;&nbsp;&nbsp;<li>'. _('ROLES DENTRO DEL SISTEMA X Usuario').'</li></td></tr>';
echo '<tr><td colspan=2>';
	$sql = "SELECT S.profileid as codprofile,U.userid as coduser, S.name as profile
		FROM sec_ROL S left join sec_ROLxuser U on  S.profileid = U.profileid and U.userid='".$usuario."'
		ORDER BY S.name";
	$Result = DB_query($sql, $db);
	echo '<table width=80% align=center>';
	if (DB_num_rows($Result)>0 ) {
		//echo "<tr><td ><hr></td></tr>";
	}
	$k=0; //row colour counter
	$j=0;
	while($AvailRow = DB_fetch_array($Result)) {
				
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		echo '<td nowrap style="font-weight:normal;">';
		$codprofile=$AvailRow['codprofile'];
		$coduserp=$AvailRow['coduser'];
		$nombreprofile=$AvailRow['profile'];
		if(is_null($coduserp)) {
			if ((isset($_POST['ROLSel'.$j])) and  ($_POST['ROLSel'.$j]<> '')){
				echo '<INPUT type=checkbox name=ROLSel'.$j.' checked value="' . ($j+1) . '">';
			}else{
				echo '<INPUT type=checkbox name=ROLSel'.$j.' value="' . ($j+1) . '">';
			}
			echo '<INPUT type=hidden name=NameROL'.$j.' value='.$codprofile.' >';
			echo ucwords(strtolower($nombreprofile));
		} else{
			echo '<INPUT type=checkbox name=ROLSel'.$j.' checked value="' . ($j+1) . '">';
			echo '<INPUT type=hidden name=NameROL'.$j.' value='.$codprofile.' >';
			echo ucwords(strtolower($nombreprofile));
		}
		$j=$j+1;
		echo '</td>';
		echo '</tr>';
	}
	echo '<tr><td colspan=2><input type=hidden name=TotalROL value='.$j.'></td></tr>';
	echo '</table>';
echo '</td></tr>';
echo '</table>';
echo "</div></div></div>";

echo '<table style="display: none;">';
echo '<tr><td colspan=2 align=center> &nbsp;&nbsp;&nbsp;<li>'. _('Tipo de clientes X usuario').'</li></td></tr>';
echo '<tr><td colspan=2>';
	$sql = "SELECT S.typeid as codprofile,U.userid as coduser, S.typename as profile
		FROM debtortype S left join sec_debtorxuser U on  S.typeid = U.typeid and U.userid='".$usuario."'";
	$Result = DB_query($sql, $db);
	echo '<table width=80% align=center>';
	if (DB_num_rows($Result)>0 ) {
		echo "<tr><td ><hr></td></tr>";
	}
	$k=0; //row colour counter
	$j=0;
	while($AvailRow = DB_fetch_array($Result)) {
				
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		echo '<td nowrap style="font-weight:normal;">';
		$codprofile=$AvailRow['codprofile'];
		$coduserp=$AvailRow['coduser'];
		$nombreprofile=$AvailRow['profile'];
		if(is_null($coduserp)) {
			if ((isset($_POST['Typedeb'.$j])) and  ($_POST['Typedeb'.$j]<> '')){
				echo '<INPUT type=checkbox name=TypedebSel'.$j.' checked value="' . ($j+1) . '">';
			}else{
				echo '<INPUT type=checkbox name=TypedebSel'.$j.' value="' . ($j+1) . '">';
			}
			echo '<INPUT type=hidden name=NameTypedeb'.$j.' value='.$codprofile.' >';
			echo ucwords(strtolower($nombreprofile));
		} else{
			echo '<INPUT type=checkbox name=TypedebSel'.$j.' checked value="' . ($j+1) . '">';
			echo '<INPUT type=hidden name=NameTypedeb'.$j.' value='.$codprofile.' >';
			echo ucwords(strtolower($nombreprofile));
		}
		$j=$j+1;
		echo '</td>';
		echo '</tr>';
	}
	echo '<tr><td colspan=2><input type=hidden name=TotalTypedeb value='.$j.'></td></tr>';
	echo '</table>';
echo '</td></tr>';
echo '</table>';

/**********************************************************************************************************/
echo '<table style="display: none;">';
echo '<tr><td colspan=2 align=center> &nbsp;&nbsp;&nbsp;<li>'. _('Tipo de proveedores X usuario').'</li></td></tr>';
echo '<tr><td colspan=2>';
	$sql = "SELECT S.typeid as codprofile,U.userid as coduser, S.typename as profile
		FROM supplierstype S left join sec_supplierxuser U on  S.typeid = U.typeid and U.userid='".$usuario."'";
	$Result = DB_query($sql, $db);
	echo '<table width=80% align=center>';
	if (DB_num_rows($Result)>0 ) {
		echo "<tr><td ><hr></td></tr>";
	}
	$k=0; //row colour counter
	$j=0;
	while($AvailRow = DB_fetch_array($Result)) {
				
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		echo '<td nowrap style="font-weight:normal;">';
		$codprofile=$AvailRow['codprofile'];
		$coduserp=$AvailRow['coduser'];
		$nombreprofile=$AvailRow['profile'];
		if(is_null($coduserp)) {
			if ((isset($_POST['Typedeb'.$j])) and  ($_POST['Typedeb'.$j]<> '')){
				echo '<INPUT type=checkbox name=TypesuppSel'.$j.' checked value="' . ($j+1) . '">';
			}else{
				echo '<INPUT type=checkbox name=TypesuppSel'.$j.' value="' . ($j+1) . '">';
			}
			echo '<INPUT type=hidden name=NameTypesupp'.$j.' value='.$codprofile.' >';
			echo ucwords(strtolower($nombreprofile));
		} else{
			echo '<INPUT type=checkbox name=TypesuppSel'.$j.' checked value="' . ($j+1) . '">';
			echo '<INPUT type=hidden name=NameTypesupp'.$j.' value='.$codprofile.' >';
			echo ucwords(strtolower($nombreprofile));
		}
		$j=$j+1;
		echo '</td>';
		echo '</tr>';
	}
	echo '<tr><td colspan=2><input type=hidden name=TotalTypesupp value='.$j.'></td></tr>';
	echo '</table>';
echo '</td></tr>';
echo '</table>';


/* ---------------------Asignacion de proyectos por usuario---------------------------------------------- */
echo '<table style="display: none;">';
echo '<tr><td colspan=2 align=center> &nbsp;&nbsp;&nbsp;<li>'. _('Proyectos por Usuario').'</li></td></tr>';
echo '<tr><td colspan=2>';
	$sql = "SELECT S.idproyecto as codproyecto,U.userid as coduser, S.nombre as proyecto 
			FROM prdproyectos S left join sec_proyectoxuser U 
			on  S.idproyecto = U.idproyecto and U.userid='".$usuario."'";
	$Result = DB_query($sql, $db);
	echo '<table width=80% align=center>';
	if (DB_num_rows($Result)>0 ) {
		echo "<tr><td ><hr></td></tr>";
	}
	$k=0; //row colour counter
	$j=0;
	while($AvailRow = DB_fetch_array($Result)) {
				
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		echo '<td nowrap style="font-weight:normal;">';
		$codproyecto=$AvailRow['codproyecto'];
		$coduserp=$AvailRow['coduser'];
		$nombreproyecto=$AvailRow['proyecto'];
		if(is_null($coduserp)) {
			if ((isset($_POST['ProyectoSel'.$j])) and  ($_POST['ProyectoSel'.$j]<> '')){
				echo '<INPUT type=checkbox name=ProyectoSel'.$j.' checked value="' . ($j+1) . '">';
			}else{
				echo '<INPUT type=checkbox name=ProyectoSel'.$j.' value="' . ($j+1) . '">';
			}
			echo '<INPUT type=hidden name=NameProyecto'.$j.' value='.$codproyecto.' >';
			echo ucwords(strtolower($nombreproyecto));
		} else{
			echo '<INPUT type=checkbox name=ProyectoSel'.$j.' checked value="' . ($j+1) . '">';
			echo '<INPUT type=hidden name=NameProyecto'.$j.' value='.$codproyecto.' >';
			echo ucwords(strtolower($nombreproyecto));
		}
		$j=$j+1;
		echo '</td>';
		echo '</tr>';
	}
	echo '<tr><td colspan=2><input type="hidden" name="TotalProyectos" value="'.$j.'"></td></tr>';
	echo '</table>';
echo '</td></tr>';
echo '</table>';

echo "<br>";
//echo '<div class="centre"><input type="submit" name="submit" value="' . _('Registra Informacion') . '"></div>';

echo '<component-button type="submit" id="submit" name="submit" class="glyphicon glyphicon-floppy-disk" value="Guardar"></component-button>';

echo "</form>";

include('includes/footer_Index.inc');

if ($procesoterminado) {
	$titulo= '<i class="fa fa-check-circle" aria-hidden="true"></i>&nbsp;<span>Proceso Exitoso</span>';
	echo "<script>";
	echo 'muestraModalGeneral(3, \''.$titulo.'\', "<h4>Los datos de usuario han sido guardados satisfactoriamente</h4>");';
	echo '$(":checkbox").attr("checked", false);';
	echo "</script>";
}

?>

<script>
function DesSelectCheckAuto(checklist,cuenta)
{

 if (checklist==1){
	cad = 'SucursalSel';
 }
 
 if(checklist==2){
	cad= 'REQSucursalSel';
 }
 
 for (i=0;i<document.FDatosB.elements.length;i++) {
      if(document.FDatosB.elements[i].type == "checkbox") {
       tipo =document.FDatosB.elements[i].getAttribute('name');
       salida=tipo;
       
       var x=salida.substring(0,cuenta);
       
     
       if(x==cad){
         document.FDatosB.elements[i].checked=0
       }
      }
 }
}

function SelectCheckAuto(checklist, cuenta)
{
 //cad = autosel;
 if (checklist==1){
	cad = 'SucursalSel';
 }
 if(checklist==2){
	cad= 'REQSucursalSel';
 }
 
 for (i=0;i<document.FDatosB.elements.length;i++) {
      if(document.FDatosB.elements[i].type == "checkbox") {
       tipo =document.FDatosB.elements[i].getAttribute('name');
       salida=tipo;
       var x=salida.substring(0,cuenta);
      
       if(x==cad){
         document.FDatosB.elements[i].checked=1;
       }
      }
 }
}

</script>