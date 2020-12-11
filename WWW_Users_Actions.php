<?php
/**
ini_set('display_errors', 1);;
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
error_reporting(E_ALL);
*/

//Seleccion de nuevo menœ o viejo menu
if($_SESSION['ShowIndex']!=0){
	$sec_functions = "sec_functions_new";
}else{
	$sec_functions = "sec_functions";
}

$PageSecurity=15;
if (isset($_POST['UserID']) AND isset($_POST['ID'])){
	if ($_POST['UserID'] == $_POST['ID']) {
		$_POST['Language'] = $_POST['UserLanguage'];
	}
}

include('includes/session.inc');
include('includes/mail.php');
//echo "base:".$_SESSION['DatabaseName'];
$funcion=1409;
$title =_('Actividad de Usuarios');
include('includes/header.inc');

include('includes/SQL_CommonFunctions.inc');


include('includes/SecurityFunctions.inc');
//$permiso = Havepermission($_SESSION['UserID'],$funcion, $db) ;

echo "<table border=0 style='margin-left: auto; margin-right: auto; width:0; background-color:#ffff;' border=0 width=500 nowrap>";

echo '<tr><td colspan=2 class="texto_lista"><p align="center"><img src="images/manten_usuario.png" height="25" width="22" title="' . _('Actividad de Usuarios') . '" alt="">' . ' ' . $title.'<br></td></tr>';

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


if (isset($_POST['submit']) and $Selectedtotal == 0) {
	
	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	if(!isset($SelectedUser)){
		$SQL="select * from www_users where userid='".trim($_POST['UserID'])."'";
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
		
		if (DB_num_rows($result)!=0){
			$InputError = 1;
			prnMsg(_('La clave de usuario <b>').$_POST['UserID'].('</b> ya se encuentra registrado'),'error');
		}
	}
	
	//first off validate inputs sensible
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
	}
	
	if ((strlen($_POST['BranchCode'])>0) AND ($InputError !=1)) {
		// check that the entered branch is valid for the customer code
		$sql = "SELECT custbranch.debtorno
				FROM custbranch
				WHERE custbranch.debtorno='" . $_POST['Cust'] . "'
				AND custbranch.branchcode='" . $_POST['BranchCode'] . "'";
		$ErrMsg = _('La validacion del codigo de sucursal fallo por que');
		$DbgMsg = _('El SQL usado en la validacion de la sucursal fue:');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

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
				prnMsg(_('El tamano de archivo esta sobre el maximo permitido. El tamaño maximo en KB es') . ' ' . $_SESSION['MaxImageSize'],'warn');
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
						ShowIndex=".$_POST['ShowIndex']."
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
						ImagenUsuario
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
						'".$filename."')";
		$msg = _('Registro de usuario Exitoso');
		
		if(IsEmailAddress($_POST['Email'])){
			$emailFrom= $_SESSION['SMTP_emailSENDER'];
			$emailTo=$_POST['Email'];
			$emailToName=$_POST['RealName'];
			$subject = "Datos de Acceso al Sistema";
			$message = "Se ha creado el usuario para " . $_POST['RealName'] .".\r\n\r\n
Servidor: http://hidalgo.tecnoaplicada.com \r\n
Usuario: ".$_POST['UserID']."
Contrase–a: ".$_POST['Password']."\r\n\r\n
Le sugerimos modificar su contrase–a dando click en su nombre cuando ingrese al sistema. ";
				
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
			prnMsg(_('Datos de Acceso enviados:').$emailTo, 'success');
			
		}else{
			prnMsg(_("Se ha creado el usuario para " . $_POST['RealName'] .".\r\n\r\n
Servidor: http://hidalgo.tecnoaplicada.com \r\n
Usuario: ".$_POST['UserID']."
Contrase&ntilde;a: ".$_POST['Password']."\r\n\r\n
Le sugerimos modificar su contrase&ntilde;a dando click en su nombre cuando ingrese al sistema. "), 'success');
		}
		
		$usuarioseleccionado=$_POST['UserID'];
		//echo '<td colspan=3 style="text-align:center;"> <a href="'. $rootpath .'/ReporteLimitxUsuario.php?' . SID .'&SelectedUser=' .$SelectedUser.'" title="Asignar Límites de Crédito">Asignar Límites de Crédito</a></td>';
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
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
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
					$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}
			
		}
		
		/*eliminamos los permisos para unidades de negocio para este usuario*/
		$sql="Delete from sec_unegsxuser WHERE userid = '".$usuarioseleccionado."'";
		$ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		/* Agregar permisos de Dependencia por usuario*/
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
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
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
					$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}			
		}

		/*eliminamos los estatus de credito para este usuario*/
		$sql="Delete from sec_holdreasons WHERE userid = '".$usuarioseleccionado."'";
		$ErrMsg = _('Las operaciones sobre los estatus de credito para este registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
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
					$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}			
		}

		/*eliminamos las categorias de inventario para este usuario*/
		$sql="Delete from sec_stockcategory WHERE userid = '".$usuarioseleccionado."'";
		$ErrMsg = _('Las operaciones sobre las categorias de inventario para este registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		/* Agregar permisos de terminos de pago por usuario*/
		if (isset($_POST['TotalCategorys'])){
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
		}



		/*eliminamos la lista de precios para este usuario*/
		$sql="Delete from sec_pricelist WHERE userid = '".$usuarioseleccionado."'";
		$ErrMsg = _('Las operaciones sobre las listas de precios para este registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
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
					$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}			
		}
		
		
		/*eliminamos los proyectos para este usuario*/
		$sql="Delete from sec_proyectoxuser WHERE userid = '$usuarioseleccionado'";
		$ErrMsg = _('Las operaciones sobre los proyectos para este registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
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
					$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}
		}
		
		/********************************************************************/
		
		/*eliminamos los clientes para este usuario*/
		$sql="Delete from sec_debtorxuser WHERE userid = '$usuarioseleccionado'";
		$ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
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
					$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}
		}
		/********************************************************************/
		/*eliminamos los proveedores para este usuario*/
		$sql="Delete from sec_supplierxuser WHERE userid = '$usuarioseleccionado'";
		$ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
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
					$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}
		}
		/********************************************************************/
		
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
			prnMsg(_('User Deleted'),'info');
		}
		unset($SelectedUser);
}elseif (isset($_GET['funcion'])) {
/*asignacion de funciones por usuario*/
   $z=0;
  echo "<form  method='post' enctype='multipart/form-data' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
    $sql = "SELECT * FROM sec_modules s where active=1 ";
    $Result = DB_query($sql, $db);
    if (isset($SelectedUser)) {
		//echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Review Existing Users') . '</a></div><br>';
		echo "<div class='texto_azul'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'><img src='images/regresar.png'></a></div><br>";
		
	}
    echo '<table align="center" width=90% >';
    echo '<div class="centre">';
    echo "<tr><td style=vertical-align:center; class='texto_status'><b>"._('Funciones por Usuario')."</b></font></td></tr>";
    echo "</div>";
    
    $encontromodulo=false;
    if (DB_num_rows($Result)>0 ) {
        echo "<tr><td><hr align='CENTER'></td></tr>";
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
            echo "<td class='texto_normal' align='center'><li><b>".ucwords($namemodule)."</b></li></td></tr>";
            /* trae submodulos*/
            $y=0;
            $a=0;
	    
	    $nombrecategoria="";
	    $nombrecategoryant="";
            $sql = "SELECT * FROM sec_submodules s where active=1 and moduleid=".$moduleid;
            $ReSubmodule = DB_query($sql, $db);
            $condmodule=false;
            if (DB_num_rows($ReSubmodule)>0 ) {
                    echo "<tr><td class='texto_n_izq' align='center'><table align='center' width=100% >";
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
                    echo '<td class="texto_normal" align="center"><li><font style=font-size:12px;><b>'.ucwords($namesubmodule).'</b></li>';
                    /* trae funciones*/
                    $sql = "SELECT distinct title,
							   FP.userid as profilea,
							   FP.functionid as functiona,
							   F.functionid as funcion,
							   F.type as type,
							   C.name as category,
							   case when FP.permiso is null then 1 else FP.permiso end as permiso
						    FROM $sec_functions F left join  sec_funxuser FP on F.functionid = FP.functionid
						         and FP.userid='".$SelectedUser."',
							 www_users P, sec_categories C
						    WHERE F.active=1 and C.categoryid=F.categoryid
						    and F.SubModuleid=".$submoduleid." order by  C.name,F.type,F.title  ";
                    $ReFuntion = DB_query($sql, $db);
                    $condfuntion=false;
                    if (DB_num_rows($ReFuntion)>0 ) {
                            //echo "<table width=95% CELLPADDING=0 CELLSPACING=0 border=1 bordercolor=black><tr>";
			   				 echo "<table align='center' CELLPADDING=0 CELLSPACING=0 border=1 bordercolor=lightgray width=100% >";
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
    echo '<div class="centre"><div align="center"><button style="border:0; background-color:transparent;" name="submit"><img src="images/guardar.png" ALT="Guardar"></button></div></form>';

	include('includes/footer.inc');
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

if (!isset($SelectedUser)) {
/* If its the first time the page has been displayed with no parameters then none of the above are true and the list of Users will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/
	/*
	$sql = 'SELECT 
			www_users.userid,
			www_users.realname,
			www_users.phone,
			www_users.email,
			www_users.customerid,
			www_users.branchcode,
			www_users.salesman,
			www_users.lastvisitdate,
			www_users.fullaccess,
			www_users.pagesize,
			www_users.theme,
			www_users.language,
			areas.areadescription,
			min(sec_profiles.name) as primerperfil
		FROM www_users LEFT JOIN areas ON www_users.defaultarea = areas.areacode, sec_profilexuser, sec_profiles
where www_users.userid = sec_profilexuser.userid and
	sec_profilexuser.profileid = sec_profiles.profileid
group by 			www_users.userid,
			www_users.realname,
			www_users.phone,
			www_users.email,
			www_users.customerid,
			www_users.branchcode,
			www_users.salesman,
			www_users.lastvisitdate,
			www_users.fullaccess,
			www_users.pagesize,
			www_users.theme,
			www_users.language,
			areas.areadescription';
	*/
	
	$sql = ' SELECT www_users.userid, www_users.realname, www_users.phone, www_users.email,
		www_users.customerid, www_users.branchcode, www_users.salesman, 
		www_users.lastvisitdate, www_users.fullaccess, www_users.pagesize, www_users.theme,
		www_users.language, areas.areadescription, min(sec_profiles.name) as primerperfil , 
		www_users.lastaction,www_users.lastpage,www_users.ip_address, www_users.loginCount,
		www_users.blocked, tags.tagname
		FROM www_users LEFT JOIN areas ON www_users.defaultarea = areas.areacode
		LEFT JOIN sec_profilexuser ON www_users.userid = sec_profilexuser.userid
		LEFT JOIN  sec_profiles ON sec_profilexuser.profileid = sec_profiles.profileid
		LEFT JOIN tags ON www_users.defaultunidadNegocio= tags.tagref
			WHERE www_users.login';
	if (Havepermission($_SESSION['UserID'],69, $db)==0){
		//$sql=$sql. '	WHERE www_users.tecnoaplicadauser = 0';
	}
	$sql=$sql. ' group by www_users.userid, www_users.realname, www_users.phone,www_users.email,
		www_users.customerid, www_users.branchcode,www_users.salesman,
		www_users.lastvisitdate, www_users.fullaccess,www_users.pagesize, www_users.theme,
		www_users.language, areas.areadescription
		
		order by www_users.userid,www_users.realname
		';
			
	//echo '<pre><br>sql:'.$sql;
	
	$result = DB_query($sql,$db);
	echo '<table cellspacing=0 border=0 align="center" width=1200 height=10 bordercolor=#762123 cellpadding=0 style="background-color:#ed1b2f">';
	echo '<tr><td colspan=2 class="texto_lista"><p align="left"> En L&iacute;nea</td></tr>';
	
	echo '<table border=1 cellspacing=0 cellpadding=3 align="center" width=1200 bordercolor=#aeaeae>';
	echo "<tr><td class='titulos_principales'><b>" ._('#') . "</td>
			<td class='titulos_principales'><b>" . _('Estado') . "</td>
		</td><td class='titulos_principales'><b>" . _('Login') . "</td>
		<td class='titulos_principales'><b>" . _('Nombre') . "</td>
		<td class='titulos_principales'><b>" . _('Organo Superior / Unidad Presupuestal') . "</td>
		<td class='titulos_principales'><b>" . _('Ultimo Login') . "</td>
		<td class='titulos_principales'><b>" . _('Ultima Actividad') . "</td>
		<td class='titulos_principales'><b>" . _('Pagina') . "</td>
		<td class='titulos_principales'><b>" . _('IP Address') . "</td>
		<td class='titulos_principales'><b>" . _('# de Accesos') . "</td>
	</tr>";

	$k=0; //row colour counter
	$i=0;	
	while ($myrow = DB_fetch_row($result)) {
		if ($k==1){
			echo '<tr class="OddTableRows">';
			$k=0;
		} else {
			echo '<tr bgcolor="#FFFFFF">';
			$k=1;
		}
		$i=$i+1;

		$LastVisitDate = ConvertSQLDate($myrow[7]);
		$LastAction = $myrow[14];
		$LastPage = $myrow[15];
		$IpAddress = $myrow[16];
		$loginCount = $myrow[17];
		$OS="";
		//www_users.bloqued, tags.tagname
		$estado="#";
		if($myrow[18]=="1")
		{
			$estado="INACTIVO";
		}else{
			$estado="ACTIVO";
		}
		$UP=$myrow[19];
		/*The SecurityHeadings array is defined in config.php */
		

		printf("<td class='texto_normal'><p align=center>%s</td>
		       <td class='texto_normal' nowrap><p align=center>%s</td>
				<td class='texto_normal' nowrap><p align=center>%s</td>
				<td class='texto_normal' nowrap><p align=center>%s</td>
					<td class='texto_normal' nowrap><p align=center>%s</td>
					<td class='texto_normal' nowrap><p align=center>%s&nbsp;</td>
					<td class='texto_normal' nowrap><p align=center>%s&nbsp;</td>
					<td class='texto_normal' nowrap><p align=center>%s&nbsp;</td>
				    <td class='texto_normal' nowrap><p align=center>%s&nbsp;</td>
				    <td class='texto_normal' nowrap><p align=center>%s&nbsp;</td>
					</tr>",
					$i,
					$estado,
					$myrow[0],
					ucwords(strtolower($myrow[1])),
					ucwords(strtolower($UP)),
					$LastVisitDate,
					$LastAction,
					$LastPage,
					$IpAddress,
					$loginCount
					);

	} //END WHILE LIST LOOP
	echo '</table><br>';
	
	$sql = ' SELECT www_users.userid, www_users.realname, www_users.phone, www_users.email,
		www_users.customerid, www_users.branchcode, www_users.salesman,
		www_users.lastvisitdate, www_users.fullaccess, www_users.pagesize, www_users.theme,
		www_users.language, areas.areadescription, min(sec_profiles.name) as primerperfil ,
		www_users.lastaction,www_users.lastpage,www_users.ip_address, www_users.loginCount, 
		www_users.blocked, tags.tagname
		FROM www_users LEFT JOIN areas ON www_users.defaultarea = areas.areacode
		LEFT JOIN sec_profilexuser ON www_users.userid = sec_profilexuser.userid
		LEFT JOIN  sec_profiles ON sec_profilexuser.profileid = sec_profiles.profileid
		LEFT JOIN tags ON www_users.defaultunidadNegocio= tags.tagref
			WHERE www_users.login=0';
	
	$sql=$sql. ' group by www_users.userid, www_users.realname, www_users.phone,www_users.email,
		www_users.customerid, www_users.branchcode,www_users.salesman,
		www_users.lastvisitdate, www_users.fullaccess,www_users.pagesize, www_users.theme,
		www_users.language, areas.areadescription
	
		order by www_users.lastaction desc,www_users.userid,www_users.realname
		';
		
	
	$result = DB_query($sql,$db);
	echo '<table cellspacing=0 border=0 align="center" width=1200 height=10 bordercolor=#762123 cellpadding=0 style="background-color:#ed1b2f">';
	echo '<tr><td colspan=2 class="texto_lista"><p align="left"> Fuera de Linea</td></tr>';
	
	echo '<table border=1 cellspacing=0 cellpadding=3 align="center" width=1200 bordercolor=#aeaeae>';
	echo "<tr><td class='titulos_principales'><b>" ._('#') . "
		<td class='titulos_principales'><b>" . _('Estado') . "</td>
		</td><td class='titulos_principales'><b>" . _('Login') . "</td>
		<td class='titulos_principales'><b>" . _('Nombre') . "</td>
		<td class='titulos_principales'><b>" . _('Organo Superior / Unidad Presupuestal') . "</td>
		<td class='titulos_principales'><b>" . _('Ultimo Login') . "</td>
		<td class='titulos_principales'><b>" . _('Ultima Actividad') . "</td>
		<td class='titulos_principales'><b>" . _('Pagina') . "</td>
		<td class='titulos_principales'><b>" . _('IP Address') . "</td>
		<td class='titulos_principales'><b>" . _('# de Accesos') . "</td>
	</tr>";
	
	$k=0; //row colour counter
	$i=0;
	while ($myrow = DB_fetch_row($result)) {
		if ($k==1){
			echo '<tr class="OddTableRows">';
			$k=0;
		} else {
			echo '<tr bgcolor="#FFFFFF">';
			$k=1;
		}
		$i=$i+1;
	
		$LastVisitDate = ConvertSQLDate($myrow[7]);
		$LastAction = $myrow[14];
		$LastPage = $myrow[15];
		$IpAddress = $myrow[16];
		$loginCount= $myrow[17];
		$OS="";
		//www_users.bloqued, tags.tagname
		$estado="#";
		if($myrow[18]=="1")
		{
			$estado="INACTIVO";
		}else{
			$estado="ACTIVO";
		}
		$UP=$myrow[19];
		/*The SecurityHeadings array is defined in config.php */
	
	
		printf("<td class='texto_normal'><p align=center>%s</td>
		       <td class='texto_normal' nowrap><p align=center>%s</td>
				    <td class='texto_normal' nowrap><p align=center>%s</td>
					<td class='texto_normal' nowrap><p align=center>%s</td>
				 	<td class='texto_normal' nowrap><p align=center>%s</td>
					<td class='texto_normal' nowrap><p align=center>%s&nbsp;</td>
					<td class='texto_normal' nowrap><p align=center>%s&nbsp;</td>
					<td class='texto_normal' nowrap><p align=center>%s&nbsp;</td>
				    <td class='texto_normal' nowrap><p align=center>%s&nbsp;</td>
				<td class='texto_normal' nowrap><p align=center>%s&nbsp;</td>
					</tr>",
						$i,
						$estado,
						$myrow[0],
						ucwords(strtolower($myrow[1])),
						ucwords(strtolower($UP)),
						$LastVisitDate,
						$LastAction,
						$LastPage,
						$IpAddress,
						$loginCount
		);
	
	} //END WHILE LIST LOOP
	echo '</table><br>';
} //end of ifs and buts!


	
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


include('includes/footer.inc');
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

function SelectCheckAuto(checklist,cuenta)
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
         document.FDatosB.elements[i].checked=1
       }
      }
 }


}

</script>