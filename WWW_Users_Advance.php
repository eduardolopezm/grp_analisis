
<script LANGUAGE="JavaScript">
function ActiveModule(obj){


}


function checkUser(fieldid)
{
	var md = document.getElementById("chk"+fieldid);
	if (md){
		if (md.checked == true) {
			md.value = 1;
		}
		else {
			md.value = 0;
		}
	}
}



function selByLegal(field)
{
	for (i=0; i<field.lenght; i++)
		if (field[i].checked == true) {
			field[i].checked = false;
		} else {
			field[i].checked = true;
		}
}

function checkedUser(fieldid)
{
	var md = document.getElementById("chk"+fieldid);
	if (md){
		if (md.checked == true) {
			md.value = 0;
			md.checked = false;
		}
		else {
			md.value = 1;
			md.checked = true;
		}
	}
}

function checkAll(indice)
{

	for (i = indice+1; i < (11+indice); i++) {
		checkedUser(i);
	}
}

function checkAlmacen(fieldid)
{
	var md = document.getElementById("chkAlmacen"+fieldid);
	if (md){
		if (md.checked == true) {
			md.value = 1;
		}
		else {
			md.value = 0;
		}
	}
}



function toggle(source,fieldname)
{
  var checkboxes = document.getElementsByName("SucursalSel"+fieldname);

  for (i=0; i< checkboxes.lenght; i++) {
	checkboxes[i].checked = source.checked;
  }
}

</script>

<?php

$PageSecurity=15;

include('includes/session.inc');

$title = _('Mantenimiento de usuario');
include('includes/header.inc');

include('includes/SQL_CommonFunctions.inc');

$funcion=90;
include('includes/SecurityFunctions.inc');

//Seleccion de nuevo menœ o viejo menu
if($_SESSION['ShowIndex']!=0){
	$sec_functions = "sec_functions_new";
}else{
	$sec_functions = "sec_functions";
}

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/group_add.png" title="' . _('Buscar') . '" alt="">' . ' ' . $title.'<br>';

$firstTime=false;
if (empty($_POST) && empty($_GET))
	$firstTime=true;


if (isset($_GET['SelectedUser'])){
	$SelectedUser = $_GET['SelectedUser'];
} elseif (isset($_POST['SelectedUser'])){
	$SelectedUser = $_POST['SelectedUser'];
}

if (isset($_POST['btnNuevoUsuario'])){
	$SelectedUser="";
}

//echo "<pre>Selected User $SelectedUser";

if (isset($_GET['totalfunciones'])){
	$Selectedtotal = $_GET['totalfunciones'];
} elseif (isset($_POST['totalfunciones'])){
	$Selectedtotal = $_POST['totalfunciones'];
}else{
	$Selectedtotal=0;
}

$_POST['costoHr']=0;

/**************************************************************************************************/
/*************************** OPERACIONES DE BASE DE DATOS             *****************************/
/**************************************************************************************************/

if (isset($_POST['submit']) and $Selectedtotal == 0) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	if ($_POST['userid']!=""){ //nuevo user
		if (strlen($_POST['userid'])<3){
			$InputError = 1;
			prnMsg(_('La longitud del login de usuario debe ser mayor a 4 caracteres'),'error');
		} elseif (ContainsIllegalCharacters($_POST['userid'])) {
			$InputError = 1;
			prnMsg(_('El nombre de usuario no puede contener los siguientes caracteres ') . " - ' & + \" \\ " . _('o espacios'),'error');
		} elseif (strlen($_POST['password'])<5){
			$InputError = 1;
			prnMsg(_('El password debe ser mayor a 5 caracteres'),'error');
		} elseif (strstr($_POST['password'],$_POST['userid'])!= False){
			$InputError = 1;
			prnMsg(_('El password debe ser diferente al login de usuario'),'error');
		}
	}

 	if ((strlen($_POST['custusuario'])>0) AND (strlen($_POST['branchcodeusuario'])==0)) {
		$InputError = 1;
		prnMsg(_('Si introduce el codigo de cliente, tambien debe introducir el codigo de sucursal'),'error');
	}

	if ((strlen($_POST['branchcodeusuario'])>0) AND ($InputError !=1)) {
		// check that the entered branch is valid for the customer code
		$sql = "SELECT custbranch.debtorno
				FROM custbranch
				WHERE custbranch.debtorno='" . $_POST['custusuario'] . "'
				AND custbranch.branchcode='" . $_POST['branchcodeusuario'] . "'";
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

	//ver si se seleccionaron usuarios existentes
	$arrUsersSelected = array();
	$qry = "Select userid FROM www_users";
	$rsu = DB_query($qry,$db);
	while ($regusers = DB_fetch_array($rsu)){
		if ($_POST['selUser'.$regusers['userid']] == 1){
			$arrUsersSelected[] = $regusers['userid'];
		}
	}

	if ($SelectedUser AND $InputError !=1) {

	/*SelectedUser could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		if (!isset($_POST['custusuario']) OR $_POST['custusuario']==NULL OR $_POST['custusuario']==''){
			$_POST['custusuario']='';
			$_POST['branchcodeusuario']='';
		}

		$UpdatePassword = "";
		if ($_POST['password'] != ""){
			$UpdatePassword = "password='" . CryptPass($_POST['password']) . "',";
		}


		$sql = "UPDATE www_users SET realname='" . $_POST['nombreusuario'] . "',
						customerid='" . $_POST['custusuario'] ."',
						phone='" . $_POST['phoneusuario'] ."',
						email='" . $_POST['emailusuario'] ."',
						" . $UpdatePassword . "
						branchcode='" . $_POST['branchcodeusuario'] . "',
						pagesize='" . $_POST['pagesizeusuario'] . "',
						fullaccess=8,
						theme='" . $_POST['themeusuario'] . "',
						language ='" . $_POST['userlanguageusuario'] . "',
						defaultarea='" . $_POST['areausuario'] ."',
						defaultunidadNegocio='" . $_POST['unidadusuario'] ."',
						blocked=" . $_POST['Blocked'] . ",
						discount1=" . $_POST['discount1']/100 . ",
						discount2=" . $_POST['discount2']/100 . ",
						discount3=" . $_POST['discount3']/100 . ",
						costoHr = " . $_POST['costoHr'] . "
					WHERE userid = '$SelectedUser'";

		$msg = _('El usuario seleccionado ha sido actualizado');
		$usuarioseleccionado=$SelectedUser;
		$arrUsersSelected[] = $usuarioseleccionado;

	} elseif ($InputError !=1) {

		if ($_POST['userid']!=""){
			$sql = "INSERT INTO www_users (userid,
							realname,
							customerid,
							branchcode,
							password,
							phone,
							email,
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
							costoHr
							)
						VALUES ('" . $_POST['userid'] . "',
							'" . $_POST['nombreusuario'] ."',
							'" . $_POST['custusuario'] ."',
							'" . $_POST['branchcodeusuario'] ."',
							'" . CryptPass($_POST['password']) ."',
							'" . $_POST['phoneusuario'] . "',
							'" . $_POST['emailusuario'] ."',
							'" . $_POST['pagesizeusuario'] ."',
							8,
							'" . $_POST['areausuario'] ."',
							'" . $ModulesAllowed . "',
							" . $_POST['displayrecordsmax'] . ",
							'" . $_POST['themeusuario'] . "',
							'" . $_POST['userlanguageusuario'] . "',
							'". $_POST['unidadusuario'] ."',
							". $_POST['discount1']/100 .",
							". $_POST['discount2']/100 .",
							". $_POST['discount3']/100 .",
							" . $_POST['costoHr'] . ")";
			$msg = _('Registro de usuario Exitoso');
			$usuarioseleccionado=$_POST['userid'];

			$arrUsersSelected[] = $usuarioseleccionado;

			//echo '<td colspan=3 style="text-align:center;"> <a href="'. $rootpath .'/ReporteLimitxUsuario.php?' . SID .'&SelectedUser=' .$SelectedUser.'" title="Asignar Límites de Crédito">Asignar Límites de Crédito</a></td>';
			//header('Location:' . $rootpath . '/ReporteLimitxUsuario.php?'. SID .'&SelectedUser=' .$usuarioseleccionado.'');
		}
		else
			if (count($arrUsersSelected)==0)
				$InputError=1;

	}
	if ($InputError!=1){
		echo "<pre>entro...";
		//echo "<pre>$sql";
		$ErrMsg = _('Las operaciones sobre el registro no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		foreach($arrUsersSelected as $usuarioseleccionado){

			//movimientos segun seleccion de modulos
			if ($_POST['selModuloAlmacenes']){

				$arrtagref = array();
				/*eliminamos los permisos para almacenes para este usuario*/
				$sql="Delete from sec_loccxusser WHERE userid = '".$usuarioseleccionado."'";
				$ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
				$DbgMsg = _('El SQL utilizado es:');
				$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

				/*eliminamos los permisos para unidades de negocio para este usuario*/
				$sql="Delete from sec_unegsxuser WHERE userid = '".$usuarioseleccionado."'";
				$ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
				$DbgMsg = _('El SQL utilizado es:');
				$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

				/* Agregar permisos para almacenes por usuario*/
				if (isset($_POST['TotalSucursal'])){
					$totalsucursales=$_POST['TotalSucursal'];
					for ( $suc = 0 ; $suc <= $totalsucursales ; $suc++) {
						if ($_POST['SucursalSel'.$suc]==TRUE){
							$namesucursal=$_POST['SucursalSel'.$suc];
							if (strpos($namesucursal,"|") > 0){//significa que la unid negocio no tiene almacen
								$arr = explode("|",$namesucursal);
								if (!in_array($arr[1],$arrtagref))
									$arrtagref[] = $arr[1];
							}
							else{
								$sql="insert into sec_loccxusser (userid,loccode)";
								$sql=$sql." values('".$usuarioseleccionado."','".$namesucursal."')";
								$ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
								$DbgMsg = _('El SQL utilizado es:');


								$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

								//buscamos unidad de negocio del almacen
								$qry = "Select tagref FROM locations WHERE loccode = '$namesucursal'";
								$res = DB_query($qry,$db);
								$reg = DB_fetch_array($res);
								if (!in_array($reg['tagref'],$arrtagref))
									$arrtagref[] = $reg['tagref'];
							}
						}
					}
					/* Agregar permisos de unidad de negocio por usuario*/
					if (count($arrtagref)>0){
						$sql="insert into sec_unegsxuser (userid,tagref) VALUES ";
						foreach($arrtagref as $loccode){
							$sql.="('$usuarioseleccionado','$loccode'),";
						}
						$sql = substr($sql,0,strlen($sql)-1);

						$ErrMsg = _('Las operaciones sobre las unidades de negocio para este registro no han sido posibles por que ');
						$DbgMsg = _('El SQL utilizado es:');
						$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
					}
				}


			}

			if ($_POST['selModuloPerfiles']){
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
			}

			if ($_POST['selModuloCategorias']){
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
			}

			if ($_POST['selModuloCredito']){
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

			}

			if ($_POST['selModuloStatusCredito']){
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
			}

			if ($_POST['selModuloRoles']){
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
			}

			if ($_POST['selModuloClienteProveedor']){
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
			}

			if ($_POST['selModuloProyectos']){
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
			}

			if ($_POST['selModuloWorkflow']){
				/*eliminamos los workflows para este usuario*/
				$sql="Delete from sec_WorkFlowXUser WHERE userid = '$usuarioseleccionado'";
				$ErrMsg = _('Las operaciones sobre los Work Flows para este registro no han sido posibles por que ');
				$DbgMsg = _('El SQL utilizado es:');
				$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				/* Agregar permisos de proyectos por usuario*/
				if (isset($_POST['TotalFlujos'])){
					$TotalProyectos=$_POST['TotalFlujos'];
					for ( $proyecto = 0 ; $proyecto <= $TotalProyectos ; $proyecto ++) {

						if ($_POST['FlujoSel'.$proyecto]==TRUE){
							$NameProyecto=$_POST['NameFlujo'.$proyecto];
							$sql="insert into sec_WorkFlowXUser (userid,idflujo)";
							$sql=$sql." values('".$usuarioseleccionado."',".$NameProyecto.")";
							$ErrMsg = _('Las operaciones sobre los WorkFlows para este registro no han sido posibles por que ');
							$DbgMsg = _('El SQL utilizado es:');
							$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
						}
					}
				}
			}

		}//foreach

		/********************************************************************/

		/*unset($_POST['UserID']);
		unset($_POST['RealName']);
		unset($_POST['Cust']);
		unset($_POST['BranchCode']);
		unset($_POST['Salesman']);
		unset($_POST['Phone']);
		unset($_POST['Email']);
		unset($_POST['Password']);
		unset($_POST['PageSize']);
		unset($_POST['Access']);
		unset($_POST['DefaultLocation']);
		unset($_POST['ModulesAllowed']);
		unset($_POST['Blocked']);
		unset($_POST['Theme']);
		unset($_POST['UserLanguage']);
		unset($_POST['costoHr']);
		unset($SelectedUser);
		*/
	}

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
			prnMsg(_('Usuario Eliminado'),'info');
		}
		unset($SelectedUser);
}elseif (isset($_GET['funcion'])) {

/*asignacion de funciones por usuario*/

   $z=0;
  echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
    $sql = "SELECT * FROM sec_modules s where active=1 ";
    $Result = DB_query($sql, $db);
    if (isset($SelectedUser)) {
		echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Revisar Usuarios Existentes') . '</a></div><br>';
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
            $sql = "SELECT * FROM sec_submodules s where active=1 and moduleid=".$moduleid;
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
				   C.name as category,
				   case when FP.permiso is null then 1 else FP.permiso end as permiso
			    FROM $sec_functions F left join  sec_funxuser FP on F.functionid = FP.functionid
			         and FP.userid='".$SelectedUser."',
				 www_users P, sec_categories C
			    WHERE F.active=1 and C.categoryid=F.categoryid
			    and F.SubModuleid=".$submoduleid." order by  C.name ";
                    $ReFuntion = DB_query($sql, $db);
                    $condfuntion=false;
                    if (DB_num_rows($ReFuntion)>0 ) {
                            //echo "<table width=95% CELLPADDING=0 CELLSPACING=0 border=1 bordercolor=black><tr>";
			    echo "<table width=95% CELLPADDING=0 CELLSPACING=0 border=1 bordercolor=black>";
                            $condfuntion=true;
                    }
		    $nombrecategoryant = "";
                    while($ResFuntion = DB_fetch_array($ReFuntion)) {
                            $functionid=$ResFuntion['funcion'];
                            $nameFuntion=strtolower($ResFuntion['title']);
			    $Funtionval=$ResFuntion['functiona'];
			    $nombrecategoria= strtolower($ResFuntion['category']);
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
    //echo "<input type='hidden' name='SelectedUser' value='" . $SelectedUser . "'>";
    echo "<input type='hidden' name='UserID' value='" . $_POST['UserID'] . "'></div>";
    echo '<div class="centre"><input type="submit" name="submit" value="' . _('Registra Informacion') . '"></div></form>';

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
/**************************************************************************************************/
/*************************** FIN DE OPERACIONES DE BASE DE DATOS      *****************************/
/**************************************************************************************************/



/**************************************************************************************************/
/**********************   INICIO DE TODO !!!!    **************************************************/
/**************************************************************************************************/

/* SIEMPRE DESPLIEGA ESTA PARTE DEL CODIGO... */

	/**************************************************************************************************************/
	/**************************************************************************************************************/
	/**************************************************************************************************************/

	echo "<form name='FDatosA' id='SubFrm' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'>";

	if (!isset($_POST['findLogin'])) $_POST['findLogin'] = '*';
	if (!isset($_POST['findUserName'])) $_POST['findUserName'] = '*';
	if (!isset($_POST['findPerfil'])) $_POST['findPerfil'] = '*';
	if (!isset($_POST['findActivo'])) $_POST['findActivo'] = 1;



	echo "<table style='margin-left: auto; margin-right: auto; width: 70%' border=1>";

	echo '<tr><td style="text-align:right">'._('X Login') . ': </td><td nowrap> <input type=text name="findLogin" MAXLENGTH=8 size=9 value="'.$_POST['findLogin'].'">* para todos...</td>';

	/**** COLUMNA QUE DESPLIEGA OPCIONES A MODIFICAR *******/
	echo "<td rowspan=4 nowrap>";

	if (isset($_GET['selModuloAlmacenes']))
		$_POST['selModuloAlmacenes'] = $_GET['selModuloAlmacenes'];

	if (isset($_GET['selModuloPerfiles']))
		$_POST['selModuloPerfiles'] = $_GET['selModuloPerfiles'];

	if (isset($_GET['selModuloCategorias']))
		$_POST['selModuloCategorias'] = $_GET['selModuloCategorias'];

	if (isset($_GET['selModuloCredito']))
		$_POST['selModuloCredito'] = $_GET['selModuloCredito'];

	if (isset($_GET['selModuloStatusCredito']))
		$_POST['selModuloStatusCredito'] = $_GET['selModuloStatusCredito'];

	if (isset($_GET['selModuloRoles']))
		$_POST['selModuloRoles'] = $_GET['selModuloRoles'];

	if (isset($_GET['selModuloClienteProveedor']))
		$_POST['selModuloClienteProveedor'] = $_GET['selModuloClienteProveedor'];

	if (isset($_GET['selModuloProyectos']))
		$_POST['selModuloProyectos'] = $_GET['selModuloProyectos'];

	if (isset($_GET['selModuloWorkflow']))
		$_POST['selModuloWorkflow'] = $_GET['selModuloWorkflow'];



	if ($_POST['selModuloAlmacenes'] || $firstTime)
		echo "<INPUT type=checkbox id='selModuloAlmacenes' checked name='selModuloAlmacenes' onclick='ActiveModule(this);'> <b>Configura Almacenes y Unid. Negocio</b><BR>";
	else echo "<INPUT type=checkbox id='selModuloAlmacenes' name='selModuloAlmacenes' onclick='ActiveModule(this);'> <b>Configura Almacenes y Unid. Negocio</b><BR>";

	if ($_POST['selModuloPerfiles'] || $firstTime)
		echo "<INPUT type=checkbox id='selModuloPerfiles' checked name='selModuloPerfiles' onclick='ActiveModule(this);'> <b>Configura Perfiles</b><BR>";
	else echo "<INPUT type=checkbox id='selModuloPerfiles' name='selModuloPerfiles' onclick='ActiveModule(this);'> <b>Configura Perfiles</b><BR>";

	if ($_POST['selModuloCategorias'] || $firstTime)
		echo "<INPUT type=checkbox id='selModuloCategorias' checked name='selModuloCategorias' onclick='ActiveModule(this);'> <b>Configura Categorias</b><BR>";
	else echo "<INPUT type=checkbox id='selModuloCategorias' name='selModuloCategorias' onclick='ActiveModule(this);'> <b>Configura Categorias</b><BR>";

	if ($_POST['selModuloCredito'] || $firstTime)
		echo "<INPUT type=checkbox id='selModuloCredito' checked name='selModuloCredito' onclick='ActiveModule(this);'> <b>Configura Terminos y Listas</b><BR>";
	else echo "<INPUT type=checkbox id='selModuloCredito' name='selModuloCredito' onclick='ActiveModule(this);'> <b>Configura Terminos y Listas</b><BR>";

	if ($_POST['selModuloStatusCredito'] || $firstTime)
		echo "<INPUT type=checkbox id='selModuloStatusCredito' checked name='selModuloStatusCredito' onclick='ActiveModule(this);'> <b>Configura Estatus Credito</b><BR>";
	else echo "<INPUT type=checkbox id='selModuloStatusCredito' name='selModuloStatusCredito' onclick='ActiveModule(this);'> <b>Configura Estatus Credito</b><BR>";

	if ($_POST['selModuloRoles'] || $firstTime)
		echo "<INPUT type=checkbox id='selModuloRoles' checked name='selModuloRoles' onclick='ActiveModule(this);'> <b>Configura Roles</b><BR>";
	else echo "<INPUT type=checkbox id='selModuloRoles' name='selModuloRoles' onclick='ActiveModule(this);'> <b>Configura Roles</b><BR>";

	if ($_POST['selModuloClienteProveedor'] || $firstTime)
		echo "<INPUT type=checkbox id='selModuloClienteProveedor' checked name='selModuloClienteProveedor' onclick='ActiveModule(this);'> <b>Configura Cliente y Proveedor</b><BR>";
	else echo "<INPUT type=checkbox id='selModuloClienteProveedor' name='selModuloClienteProveedor' onclick='ActiveModule(this);'> <b>Configura Cliente y Proveedor</b><BR>";

	if ($_POST['selModuloProyectos'] || $firstTime)
		echo "<INPUT type=checkbox id='selModuloProyectos' checked name='selModuloProyectos' onclick='ActiveModule(this);'> <b>Configura Proyectos en Tareas</b><BR>";
	else echo "<INPUT type=checkbox id='selModuloProyectos' name='selModuloProyectos' onclick='ActiveModule(this);'> <b>Configura Proyectos en Tareas</b><BR>";

	if ($_POST['selModuloWorkflow'] || $firstTime)
		echo "<INPUT type=checkbox id='selModuloWorkflow' checked name='selModuloWorkflow' onclick='ActiveModule(this);'> <b>Configura Workflow</b><BR>";
	else echo "<INPUT type=checkbox id='selModuloWorkflow' name='selModuloWorkflow' onclick='ActiveModule(this);'> <b>Configura Workflow</b><BR>";

	echo "</td>";

	echo "</tr>";

	echo '<tr><td style="text-align:right" nowrap><b>'._('X Nombre del Usuario') . ': </b></td><td nowrap> <input type=text name="findUserName" MAXLENGTH=40 size=40 value="'.$_POST['findUserName'].'">* para todos...</td>';
	echo "</tr>";

	echo '<tr><td style="text-align:right">' . _('X Estatus') . ":</td><td><select name='findActivo'>";
	if ($_POST['findActivo']==1){
		echo '<option selected value=1>' . _('Activo');
		echo '<option value=0>' . _('Inactivo');
	} else {
		echo '<option value=1>' . _('Activo');
		echo '<option selected value=0>' . _('Inactivo');
	}
	echo '</select></td></tr>';

	/************************************/
	/* SELECCION DEL PERFIL DE SEGURIDAD*/
	echo "<tr><td style='text-align:right' nowrap>" . _('X Perfil de Seguridad') . ":</td><td nowrap>";
	echo "<select name='findPerfil'>";
	$SQL = "SELECT S.profileid as codprofile, S.name as profile
		FROM sec_profiles S
		ORDER BY S.name";

	$ErrMsg = _('No transactions were returned by the SQL because');
	$TransResult = DB_query($SQL,$db,$ErrMsg);

	echo "<option selected value='*'>Todos los perfiles de seguridad...</option>";

	while ($myrow=DB_fetch_array($TransResult)) {

		if ($myrow['codprofile'] == $_POST['findPerfil']){
			echo "<option selected value='" . $myrow['codprofile'] . "'>" . $myrow['profile'] . "</option>";
		}else{
			echo "<option value='" . $myrow['codprofile'] . "'>" . $myrow['profile'] . "</option>";
		}
	}

	echo "</select>";
	echo "</td></tr>";

	$caption = "Alta Nuevo Usuario";
	if ($SelectedUser)
		$caption = "Quitar usuario seleccionado";

	echo '<tr><td></td>
			<td colspan=1  style="text-align:center;">
			<input type="hidden" name="SelectedUser" value="' . $SelectedUser . '">

			<input type=submit name="searchAllUsersReal" VALUE="' . _('Usuarios Con Estos Criterios') . '"></td>

		  <td colspan=1  style="text-align:center;">
			<input type=submit name="btnNuevoUsuario" VALUE="' . _($caption) . '"></td>
			</tr>';
	echo "</table>";

	/**************************************************************************************************************/
	/**************************************************************************************************************/
	/**************************************************************************************************************/

	echo "<div style='text-align:center'>...</div>";



/************************************************************************************/
/**********   DESPLIEGA USUARIOS CON CONDICIONES SELECCIONADAS ARRIBA ***************/

if (isset($_POST['searchAllUsersReal'])) {
	$sql = 'SELECT www_users.userid, www_users.realname, www_users.phone, www_users.email,
			www_users.customerid, www_users.branchcode, www_users.salesman,
			www_users.lastvisitdate, www_users.fullaccess, www_users.pagesize, www_users.theme,
			www_users.language, areas.areadescription, IFNULL(min(sec_profiles.name),"XXXXXXXX") as primerperfil,
			www_users.costoHr
		FROM www_users LEFT JOIN areas ON www_users.defaultarea = areas.areacode
			LEFT JOIN sec_profilexuser ON www_users.userid = sec_profilexuser.userid
			LEFT JOIN  sec_profiles ON sec_profilexuser.profileid = sec_profiles.profileid
		WHERE (www_users.userid like "%'.$_POST['findLogin'].'%" OR "'.$_POST['findLogin'].'" = "*")
			AND (www_users.realname like "%'.$_POST['findUserName'].'%" OR "'.$_POST['findUserName'].'" = "*")
			AND (sec_profiles.profileid = "'.$_POST['findPerfil'].'" OR "'.$_POST['findPerfil'].'" = "*")
			AND www_users.active = "'.$_POST['findActivo'].'"
		group by www_users.userid, www_users.realname, www_users.phone,www_users.email,
			www_users.customerid, www_users.branchcode,www_users.salesman,
			www_users.lastvisitdate, www_users.fullaccess,www_users.pagesize, www_users.theme,
			www_users.language, areas.areadescription
		order by primerperfil, www_users.realname
		';
	//echo "<pre>$sql";
	$result = DB_query($sql,$db);

	echo '<table border=1 cellspacing=0 cellpadding=2>';

	$k=0; //row colour counter
	$i=0;
	$j=0;
	$indexJ = 0;
	$perfilanterior = "";
	while ($myrow = DB_fetch_row($result)) {
		if ($i % 10 == 0) {
			$j=$j+1;
			echo "<tr><th><input name='topchk".$i."' type='checkbox' onclick='checkAll(".$i.");'></th>
				<th><b>" . _('Login') . "</th>
				<th><b>" . _('Nombre') . "</th>
				<th><b>" . _('Sucursal') . "</th>
				<th><b>" . _('Email') . "</th>
				<th><b>" . _('Primer Perfil') . "</th>
				<th><b>" . _('$Hr') . "</th>
				<th><b>" . _('Ultima Visita') . "</th>
				<th colspan=3><b>" . _('Operaciones') ."</th>
			</tr>";
			$perfilanterior = $myrow[13];
			$indexJ = 0;
		}

		if ($k==1){
			echo '<tr class="OddTableRows">';
			$k=0;
		} else {
			echo '<tr bgcolor="#FFFFFF">';
			$k=1;
		}
		$i=$i+1;
		$indexJ = $indexJ + 1;
		$LastVisitDate = ConvertSQLDate($myrow[7]);



		if (isset($_POST['selUser'.$myrow[0]]) AND $_POST['selUser'.$myrow[0]] == 1) {
			$ifchecked = "checked";
			$checkValue = 1;
		} else {
			$ifchecked = "";
			$checkValue = 0;
		}

		printf("<td style='font-weight:normal;font-size:10px;' nowrap>
				<INPUT type=checkbox id='chk".$i."' ".$ifchecked." name='selUser".$myrow[0]."' onChange='checkUser(".$i.")' value=".$checkValue.">&nbsp;%s</td>
				<td style='font-weight:normal;font-size:10px;' nowrap>%s</td>
					<td style='font-weight:normal;font-size:10px;' nowrap>%s</td>
					<td style='font-weight:normal;font-size:10px;'>%s</td>
					<td style='font-weight:normal;font-size:10px;'>%s&nbsp;</td>
					<td style='font-weight:normal;font-size:10px;'>%s</td>
					<td style='font-weight:normal;font-size:10px;'>%s</td>
					<td style='font-weight:normal;font-size:10px;' nowrap>%s&nbsp;</td>
					<td ><a href=\"%s&SelectedUser=%s\">" . _('Editar') . "</a></td>
					<td ><a href=\"%s&SelectedUser=%s&delete=1\">" . _('Borrar') . "</a></td>
					<td nowrap><a href=\"%s&SelectedUser=%s&funcion=1\">" . _('Agregar Funciones') . "</a></td>
					</tr>",
					$i,
					$myrow[0],
					ucwords(strtolower($myrow[1])),
					ucwords(strtolower($myrow[12])),
					strtolower($myrow[3]),
					ucwords(strtolower($myrow[13])),
					number_format($myrow[14],2),
					$LastVisitDate,
					$_SERVER['PHP_SELF']  . "?selModuloAlmacenes=".$_POST['selModuloAlmacenes'].
											 "&selModuloPerfiles=".$_POST['selModuloPerfiles'].
											 "&selModuloCategorias=".$_POST['selModuloCategorias'].
											 "&selModuloCredito=".$_POST['selModuloCredito'].
											 "&selModuloStatusCredito=".$_POST['selModuloStatusCredito'].
											 "&selModuloRoles=".$_POST['selModuloRoles'].
											 "&selModuloClienteProveedor=".$_POST['selModuloClienteProveedor'].
											 "&selModuloProyectos=".$_POST['selModuloProyectos'].
											 "&selModuloWorkflow=".$_POST['selModuloWorkflow'] .SID,
					$myrow[0],
					$_SERVER['PHP_SELF'] . "?" . SID,
					$myrow[0],
					$_SERVER['PHP_SELF'] . "?" . SID,
					$myrow[0]
			);

	} //END WHILE LIST LOOP
	echo '</table><br>';
}
/**** FIN DE DESPLIEGUE DE USUARIOS QUE COINCIDEN CON CRITERIO SELECCIONADO ******/

if (isset($_POST['btnNuevoUsuario'])){
	$SelectedUser="";
	$_POST['nombreusuario'] = "";
	$_POST['emailusuario'] = "";
	$_POST['discount1'] = "";
	$_POST['discount2'] = "";
	$_POST['discount3'] = "";
	$_POST['Blocked'] = "";
	$_POST['phoneusuario'] = "";
	$_POST['unidadusuario'] = "";
	$_POST['areausuario'] = "";
	$_POST['themeusuario'] = "";
	$_POST['pagesizeusuario'] = "";
	$_POST['userlanguageusuario'] = "";
}

if($SelectedUser!=""){
	$qry = "Select * FROM www_users where userid='$SelectedUser'";
	$rs = DB_query($qry,$db);
	$userreg = DB_fetch_array($rs);
	echo "<p class='page_title_text'>Usuario Seleccionado: ".$userreg['realname']."</p>";

	$_POST['nombreusuario'] = $userreg['realname'];
	$_POST['emailusuario'] = $userreg['email'];
	$_POST['discount1'] = $userreg['discount1'];
	$_POST['discount2'] = $userreg['discount2'];
	$_POST['discount3'] = $userreg['discount3'];
	$_POST['Blocked'] = $userreg['blocked'];
	$_POST['phoneusuario'] = $userreg['phone'];
	$_POST['unidadusuario'] = $userreg['defaultunidadNegocio'];
	$_POST['areausuario'] = $userreg['defaultarea'];
	$_POST['themeusuario'] = $userreg['theme'];
	$_POST['pagesizeusuario'] = $userreg['pagesize'];
	$_POST['userlanguageusuario'] = $userreg['language'];
}


/****************************************************/
/***** INICIA CONFIGURACION DE CADA MODULO   ********/

/****** INVENTARIOS Y UNIDADES DE NEGOCIOS **********/

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/group_add.png" title="' . _('Buscar') . '" alt="">' . 'Modulos a Configurar para Todos los Usuarios Seleccionados !<br>';

echo "<table style='margin-left: auto; margin-right: auto; width: 50%;' border=1>";

echo '<tr style="background-color:orange;height:25px"><td colspan=2 style="text-align:center"><li><b>'. _('Configura Datos Generales').'</b></li></td></tr>';

if (isset($_POST['btnNuevoUsuario']) && $_POST['btnNuevoUsuario']=="Alta Nuevo Usuario"){
	echo '<tr><td>' . _('User Login') . ":</td>
			  <td><input type='text' name='userid' size=20 maxlength=20 ></td>
		  </tr>";
	echo '<tr><td>' . _('Password') . ":</td>
			<td><input type='password' name='password' size=20 maxlength=20 ></tr>";

}

echo '<tr><td>' . _('Nombre') . ':</td>
	<td><input type="text"  name="nombreusuario" size=40  VALUE="' . $_POST['nombreusuario'] .'"></td></tr>';

echo '<tr><td>' . _('No. Telefono') . ":</td>
	<td><input type='text' name='phoneusuario' value='" . $_POST['phoneusuario'] . "' size=30 ></td></tr>";

echo '<tr><td>' . _('Email') . ':</td>
	<td><input type="text"  name="emailusuario" size=40  VALUE="' . $_POST['emailusuario'] .'"></td></tr>';

echo '<tr><td>' . _('Codigo de Cliente') . ':</td>
	<td><input type="text" name="custusuario" size=10 maxlength=8 value="' . $_POST['custusuario'] . '"></td></tr>';

echo '<tr><td>' . _('Codigo de Sucursal') . ':</td>
	<td><input type="text" name="branchcodeusuario" size=10 maxlength=8 VALUE="' . $_POST['branchcodeusuario'] .'"></td></tr>';

echo '<tr><td>' . _('Unidad de Negocio') . ':</td>
	<td><select name="unidadusuario">';
$sql = 'SELECT tagref, tagdescription FROM tags';
$result = DB_query($sql,$db);
if (DB_num_rows($result)>0){
	echo "<option selected value='0'> NINGUNO </OPTION>";
	while ($myrow=DB_fetch_array($result)){

		if ($myrow['tagref'] == $_POST['unidadusuario']){

			echo "<option selected value='" . $myrow['tagref'] . "'>" . $myrow['tagdescription'];

		} else {
			echo "<option Value='" . $myrow['tagref'] . "'>" . $myrow['tagdescription'];

		}
	}
}
echo '</select></td></tr>';

echo '<tr><td>' . _('Area de venta') . ':</td>
	<td><select name="areausuario">';

$sql = 'SELECT areacode, areadescription FROM areas ';
$result = DB_query($sql,$db);
if (DB_num_rows($result)>0){
	echo "<option selected value='0'> NINGUNO </OPTION>";

	while ($myrow=DB_fetch_array($result)){

		if ($myrow['areacode'] == $_POST['areausuario']){

			echo "<option selected value='" . $myrow['areacode'] . "'>" . $myrow['areadescription'];

		} else {
			echo "<option Value='" . $myrow['areacode'] . "'>" . $myrow['areadescription'];

		}

	}
}

echo '</select></td></tr>';

echo '<tr><td>' . _('Tamaño de pagina en reportes') .":</td>
	<td><select name='pagesizeusuario'>";

if($_POST['pagesizeusuario']=='A4'){
	echo "<option selected value='A4'>" . _('A4');
} else {
	echo "<option value='A4'>A4";
}

if($_POST['pagesizeusuario']=='A3'){
	echo "<option selected Value='A3'>" . _('A3');
} else {
	echo "<option value='A3'>A3";
}

if($_POST['pagesizeusuario']=='A3_landscape'){
	echo "<option selected Value='A3_landscape'>" . _('A3') . ' ' . _('landscape');
} else {
	echo "<option value='A3_landscape'>" . _('A3') . ' ' . _('landscape');
}

if($_POST['pagesizeusuario']=='letter'){
	echo "<option selected Value='letter'>" . _('Letter');
} else {
	echo "<option value='letter'>" . _('Letter');
}

if($_POST['pagesizeusuario']=='letter_landscape'){
	echo "<option selected Value='letter_landscape'>" . _('Letter') . ' ' . _('landscape');
} else {
	echo "<option value='letter_landscape'>" . _('Letter') . ' ' . _('landscape');
}

if($_POST['pagesizeusuario']=='legal'){
	echo "<option selected value='legal'>" . _('Legal');
} else {
	echo "<option Value='legal'>" . _('Legal');
}
if($_POST['pagesizeusuario']=='legal_landscape'){
	echo "<option selected value='legal_landscape'>" . _('Legal') . ' ' . _('landscape');
} else {
	echo "<option value='legal_landscape'>" . _('Legal') . ' ' . _('landscape');
}

echo '</select></td></tr>';

echo '<tr>
	<td>' . _('Visualizacion') . ":</td>
	<td><select name='themeusuario'>";

$ThemeDirectory = dir('css/');


while (false != ($ThemeName = $ThemeDirectory->read())){

	if (is_dir("css/$ThemeName") AND $ThemeName != '.' AND $ThemeName != '..' AND $ThemeName != 'CVS'){

		if ($_POST['themeusuario'] == $ThemeName){
			echo "<option selected value='$ThemeName'>$ThemeName";
		} else {
			echo "<option value='$ThemeName'>$ThemeName";
		}
	}
}

echo '</select></td></tr>';


echo '<tr>
	<td>' . _('Lenguaje') . ":</td>
	<td><select name='userlanguageusuario'>";

 $LangDirHandle = dir('locale/');


while (false != ($LanguageEntry = $LangDirHandle->read())){

	if (is_dir('locale/' . $LanguageEntry) AND $LanguageEntry != '..' AND $LanguageEntry != 'CVS' AND $LanguageEntry!='.'){

		if ($_POST['userlanguageusuario'] == $LanguageEntry){
			echo "<option selected value='$LanguageEntry'>$LanguageEntry";
		}else {
			echo "<option value='$LanguageEntry'>$LanguageEntry";
		}
	}
}

echo '</select></td></tr>';


echo '<tr><td>' . _('Estatus') . ":</td><td><select name='Blocked'>";

if ($_POST['Blocked']==0){
	echo '<option selected value=0>' . _('Activo');
	echo '<option value=1>' . _('Inactivo');
} else {
 	echo '<option selected value=1>' . _('Activo');
	echo '<option value=0>' . _('Inactivo');
}
echo '</select></td></tr>';


echo '<tr><td>' . _('Descuento Maximo 1') . ':</td>
	<td><input type="text" class="number" name="discount1" size=10 maxlength=5 VALUE="' . $_POST['discount1'] .'"></td></tr>';

echo '<tr><td>' . _('Descuento Maximo 2') . ':</td>
	<td><input type="text" class="number" name="discount2" size=10 maxlength=5 VALUE="' . $_POST['discount2'] .'"></td></tr>';

echo '<tr><td>' . _('Descuento Maximo 3') . ':</td>
	<td><input type="text" class="number" name="discount3" size=10 maxlength=5 VALUE="' . $_POST['discount3'] .'"></td></tr>';

/* SI ES LA PRIMERA VEZ QUE ENTRA, INICIALIZA COMODINES PARA CADA CAMPO */
if (!isset($_POST['legalid'])) $_POST['legalid'] = '*';
if (!isset($_POST['unidadnegocio'])) $_POST['unidadnegocio'] = '*';
if (!isset($_POST['xArea'])) $_POST['xArea'] = '*';
if (!isset($_POST['xRegion'])) $_POST['xRegion'] = '*';
if (!isset($_POST['findAlmacenName'])) $_POST['findAlmacenName'] = '*';

/* ---------------------Asignacion de almacenes por usuario---------------------------------------------- */

echo '<tr style="background-color:white;height:25px"><td colspan=2 style="text-align:center"><li><b></b></li></td></tr>';

if ($_POST['selModuloAlmacenes'] || $firstTime){

	echo '<tr style="background-color:orange;height:25px"><td colspan=2 style="text-align:center"><li><b>'. _('Configura Seleccion de Almacenes').'</b></li></td></tr>';

	echo '<tr style="background-color:white"><td colspan=2 style="text-align:center">';

			echo "<table  style='margin-left: auto; margin-right: auto; width: 50%;' border=1>";

			/************************************/
			/* SELECCION DE REGION              */
			echo '<tr><td  style="text-align:right" nowrap>' . _('X Region') . ':' . "</td>
				<td><select tabindex='4' name='xRegion'>";

			$sql = "SELECT regioncode, CONCAT(regioncode,' - ',name) as name FROM regions";
			$result=DB_query($sql,$db);

			echo "<option selected value='*'>Todas las regiones...</option>";

			while ($myrow=DB_fetch_array($result)){
				  if ($myrow['regioncode'] == $_POST['xRegion']){
					echo "<option selected value='" . $myrow["regioncode"] . "'>" . $myrow['name'];
				  } else {
					  echo "<option value='" . $myrow['regioncode'] . "'>" . $myrow['name'];
				  }
			}
			echo '</select><input type=submit name="searchAllUsers" VALUE="' . _('->') . '"></td></tr>';

			/************************************/
			/* SELECCION DE AREA                */
			echo '<tr><td style="text-align:right" nowrap>' . _('X Area') . ':' . "</td>
				<td><select tabindex='4' name='xArea'>";

			$sql = "SELECT regions.name as regionname, areas.areacode, CONCAT(areas.areacode,' - ',areas.areadescription) as name
					FROM areas JOIN regions ON areas.regioncode = regions.regioncode
				  WHERE areas.regioncode = '" . $_POST['xRegion'] . "' OR '" . $_POST['xRegion'] . "' = '*'
				  GROUP BY regions.name, areas.areacode, areas.areadescription";

			$result=DB_query($sql,$db);

			echo "<option selected value='*'>Todas las areas...</option>";

			$regionAnterior = '';
			while ($myrow=DB_fetch_array($result)){
				if ($regionAnterior != $myrow["regionname"]) {
					echo "<option style='background-color:DarkGray' value='XX'>***" . $myrow['regionname']."</option>";
					$regionAnterior = $myrow["regionname"];
				}

				if ($myrow['areacode'] == $_POST['xArea']){
					echo "<option selected value='" . $myrow["areacode"] . "'>" . $myrow['name']."</option>";
				} else {
					  echo "<option value='" . $myrow['areacode'] . "'>" . $myrow['name']."</option>";
				}
			}
			echo '</select><input type=submit name="searchAllUsers" VALUE="' . _('->') . '"></td></tr>';
			/************************************/

			echo "<tr style='background-color:#050505;height:5px'><td colspan=2></td></tr>";

			/************************************/
			/* SELECCION DEL RAZON SOCIAL */

			echo '<tr><td style="text-align:right" nowrap><b>'._('X Razon Social:').'</b></td><td nowrap><select name="legalid">';
			///Imprime las razones sociales
			$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
			$SQL = $SQL .	" FROM tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid
						JOIN areas ON t.areacode = areas.areacode";
			$SQL = $SQL .	" WHERE (areas.areacode = '".$_POST['xArea']."' OR '*'= '".$_POST['xArea']."' )
							AND (areas.regioncode = '".$_POST['xRegion']."' OR '*'= '".$_POST['xRegion']."' )";
			$SQL = $SQL .	" GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname
						ORDER BY legalbusinessunit.legalname";

			$result=DB_query($SQL,$db);

			/* NO PODER SELECCIONAR TODAS PUES ESTA REFERENCIA SE USA PARA EL ALTA DE UN MOVIMIENTO */
			echo "<option selected value='*'>Todas las razones sociales...</option>";

			while ($myrow=DB_fetch_array($result)){
				if (isset($_POST['legalid']) and $_POST['legalid']==$myrow["legalid"]){
					echo '<option selected value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
				} else {
					echo '<option value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
				}
			}
			echo '</select>

				<input type=submit name="searchAllUsers" VALUE="' . _('->') . '">

			</td></tr>';

			/************************************/
			/* SELECCION DE LA UNIDAD DE NEGOCIO DE PRODUCTOS */
			echo "<tr><td style='text-align:right' nowrap>" . _('X Unidad de Negocio') . ":</td><td nowrap>";
			echo "<select name='unidadnegocio'>";
			$SQL = "SELECT  t.tagref, CONCAT(t.tagref,' - ',t.tagdescription) as tagdescription, t.tagdescription,
					legalbusinessunit.legalid,
					legalbusinessunit.legalname";//areas.areacode, areas.areadescription";
				$SQL = $SQL .	" FROM tags t join areas ON t.areacode = areas.areacode
								JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid ";
				$SQL = $SQL .	" WHERE (t.legalid = '".$_POST['legalid']."' OR '*'= '".$_POST['legalid']."' )
							AND (areas.areacode = '".$_POST['xArea']."' OR '*'= '".$_POST['xArea']."' )
							AND (areas.regioncode = '".$_POST['xRegion']."' OR '*'= '".$_POST['xRegion']."' )
						  ORDER BY legalbusinessunit.legalid,
								legalbusinessunit.legalname,
								t.tagdescription, areas.areacode";

			$ErrMsg = _('No transactions were returned by the SQL because');
			$TransResult = DB_query($SQL,$db,$ErrMsg);

			echo "<option selected value='*'>Todas a las que tengo accceso...</option>";

			$antLegalid = '';
			while ($myrow=DB_fetch_array($TransResult)) {

				if ($antLegalid != $myrow['legalid']) {
					echo "<option value='XX'>**** " . $myrow['legalname'] . " ****</option>";
					$antLegalid = $myrow['legalid'];
				}
				if ($myrow['tagref'] == $_POST['unidadnegocio']){
					echo "<option selected value='" . $myrow['tagref'] . "'>" . $myrow['tagdescription'] . "</option>";
				}else{
					echo "<option value='" . $myrow['tagref'] . "'>" . $myrow['tagdescription'] . "</option>";
				}
			}

			echo "</select><input type=submit name='searchAllUsers' VALUE='" . _('->') . "'>";
			echo "</td></tr>";

			echo "<tr style='background-color:#050505;height:5px'><td colspan=2></td></tr>";

			echo '<tr><td style="text-align:right" nowrap><b>'._('X Nombre de Almacen') . ': </b></td><td nowrap> <input type=text name="findAlmacenName" MAXLENGTH=40 size=40 value="'.$_POST['findAlmacenName'].'">* para todos...</td>';
			echo "</tr>";

			echo '<tr><td></td>
				<td colspan=1  style="text-align:center;">
				<input type=submit name="searchAllUsers" VALUE="' . _('Almacenes Con Estos Criterios') . '"></td>
				</tr>';

			echo "</table>";
			/************************************/
	echo '</td></tr>';



	/********* INICIA BUSQUEDA DE ALMACENES QUE COINCIDEN CON CRITERIOS **********/

	echo '<tr><td colspan=2>';

		$sql = "SELECT if(L.loccode is null,'-1',L.loccode) as codsuc, if(L.locationname is null,'Sin Almacen',L.locationname) as suc, legalbusinessunit.legalname, tags.tagdescription,
					areas.areadescription, legalbusinessunit.legalid,tags.tagref,
					regions.name as regionname, departments.department as departmentname, if(sec_loccxusser.loccode is null and sec_unegsxuser.tagref is null,0,1) as tieneacceso
			FROM tags LEFT join locations L ON L.tagref = tags.tagref
					 join legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
					 join areas ON tags.areacode = areas.areacode
					 join regions ON areas.regioncode = regions.regioncode
					 join departments ON tags.u_department = departments.u_department
					 left join sec_loccxusser
					 ON L.loccode=sec_loccxusser.loccode
					 and sec_loccxusser.userid = '$SelectedUser'
					 left join sec_unegsxuser
					 ON sec_unegsxuser.tagref=tags.tagref
					 and sec_unegsxuser.userid = '$SelectedUser'

			WHERE (legalbusinessunit.legalid = '".$_POST['legalid']."' OR '".$_POST['legalid']."' = '*')
				AND (tags.tagref = '".$_POST['unidadnegocio']."' OR '".$_POST['unidadnegocio']."' = '*')
				AND (areas.areacode = '".$_POST['xArea']."' OR '*'= '".$_POST['xArea']."' )
				AND (areas.regioncode = '".$_POST['xRegion']."' OR '*'= '".$_POST['xRegion']."' )
				AND (L.locationname like '%".$_POST['findAlmacenName']."%' OR '*'= '".$_POST['findAlmacenName']."' )
			ORDER BY tags.legalid, legalbusinessunit.legalname, regions.name, areas.areadescription,
				departments.department, tags.tagdescription,
					L.locationname";

		//echo "<pre>$sql";
		$Result = DB_query($sql, $db);
		echo '<table width=100% align=center>';
		if (DB_num_rows($Result)>0 ) {
		}
		$k=0; //row colour counter
		$j=0;
		$lastunidad = "";
		while($AvailRow = DB_fetch_array($Result)) {

			if ($lastunidad != $AvailRow['legalname']) {


				echo '<tr style="background-color:darkgray;">
					<td colspan=5 style="font-size:16px">';
				$marcaAll=false;
				if (isset($_POST['allLegalIdChecked'.$AvailRow['legalid']])) {
					echo '	<INPUT type="checkbox" checked name="allLegalIdChecked'.$AvailRow['legalid'].'">*'.$AvailRow['legalname'].'
						<input type=submit name="searchAlLLoc'.$AvailRow['legalid'].'" VALUE="' . _('->') . '">';
						$marcaAll=true;
				} else {
					echo '	<INPUT type="checkbox" name="allLegalIdChecked'.$AvailRow['legalid'].'">*'.$AvailRow['legalname'].'
						<input type=submit name="searchAllLoc'.$AvailRow['legalid'].'" VALUE="' . _('->') . '">';
				}

				echo '		</td>';
				echo '</tr>';

				echo '<tr style="background-color:lightgray;">
						<th style="text-align:center;font-size:14px"><b>REGION</b></th>
						<th style="text-align:center;font-size:14px"><b>AREA</b></th>
						<th style="text-align:center;font-size:14px"><b>DEPARTAMENTO</b></th>
						<th style="text-align:center;font-size:14px"><b>U.NEGOCIO</b></th>
						<th style="text-align:center;font-size:14px"><b>ALMACEN</b></th>
					</tr>';

				$lastunidad = $AvailRow['legalname'];
			}



			if ($k==1){
				echo '<tr style="background-color:#FaFaFa;">';
				$k=0;
			} else {
				echo '<tr style="background-color:#F8F8F8;">';
				$k=1;
			}

			echo '<td nowrap style="font-weight:normal;font-size:8px">';
			echo $AvailRow['regionname'];
			echo '</td>';
			echo '<td nowrap style="font-weight:normal;;font-size:8px">';
			echo $AvailRow['areadescription'];
			echo '</td>';
			echo '<td nowrap style="font-weight:normal;;font-size:8px">';
			echo $AvailRow['departmentname'];
			echo '</td>';
			echo '<td nowrap style="font-weight:normal;;font-size:8px">';
			echo $AvailRow['tagdescription'];
			echo '</td>';
			echo '<td nowrap style="font-weight:normal;">';

			$chkuser = "";
			if ($AvailRow['tieneacceso'] && (isset($_GET['SelectedUser']) || isset($_POST['searchAllUsers']))){
					$chkuser="checked";
			}

			$valor = $AvailRow['codsuc'];
			if ($valor == -1)//indica unid de negocio sin almacen
				$valor = "-1|".$AvailRow['tagref'];

			if ( ( ($_POST['SucursalSel'.$j] <> '') and (!isset($_POST['searchAllUsers'])) and (!isset($_POST['searchAllLoc'.$AvailRow['legalid'].'']))) OR ($marcaAll) ){
				echo '<INPUT checked type="checkbox" id="'.$j.'" name="SucursalSel'.$j.'"  value="' . $valor . '">';
			}else{
				echo '<INPUT '.$chkuser.' type="checkbox" id="'.$j.'" name="SucursalSel'.$j.'" value="' . $valor. '">';
			}

			echo ucwords(strtolower($AvailRow['suc']));

			$j=$j+1;
			echo '</td>';
			echo '</tr>';
		}
		echo '<tr><td colspan=2><input type=hidden name=TotalSucursal value='.$j.'></td></tr>';
		echo '</table>';

	echo '</td></tr>';


	echo '<tr style="background-color:white;height:25px"><td colspan=2 style="text-align:center"><li><b></b></li></td></tr>';

}//if muestro o no

if ($_POST['selModuloCredito'] || $firstTime){

	echo '<tr style="background-color:orange;height:25px"><td colspan=2 style="text-align:center"><li><b>'. _('Configura Seleccion de Listas de Precios').'</b></li></td></tr>';

	/* ---------------------Asignacion de lista de precios por usuario---------------------------------------------- */
	echo '<tr><td colspan=2>';

		$sql = "SELECT L.typeabbrev as codsuc, L.sales_type as suc, ifnull(U.userid,0) as tieneacceso
			FROM salestypes L
			left join sec_pricelist U
				ON  L.typeabbrev = U.pricelist
				AND U.userid='".$SelectedUser."'";
		$Result = DB_query($sql, $db);

		echo '<table width=100% align=center>';
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
			$nombreprice=$AvailRow['suc'];

			$chkuser = "";
			if ($AvailRow['tieneacceso']!="0" && (isset($_GET['SelectedUser']) || isset($_POST['searchAllUsers'])))
				$chkuser="checked";

			if (isset($_POST['PrecioSel'.$j]) and ($_POST['PrecioSel'.$j] <> ''))
				echo '<INPUT type=checkbox name=PrecioSel'.$j.' checked value="' . ($j+1) . '">';
			else
				echo '<INPUT type=checkbox name=PrecioSel'.$j.' '.$chkuser.' value="' . ($j+1) . '">';

			echo '<INPUT type=hidden name=Nameprecio'.$j.' value='.$price.' >';
			echo ucwords(strtolower($nombreprice));

			$j=$j+1;
			echo '</td>';
			echo '</tr>';
		}
		echo '<tr><td colspan=2><input type=hidden name=TotalPrices value='.$j.'></td></tr>';
		echo '</table>';

	echo '</td></tr>';


	echo '<tr style="background-color:white;height:25px"><td colspan=2 style="text-align:center"><li><b></b></li></td></tr>';
	echo '<tr style="background-color:orange;height:25px"><td colspan=2 style="text-align:center"><li><b>'. _('Configura Seleccion de Terminos de Pago').'</b></li></td></tr>';

	/* ---------------------Asignacion de lista de terminos de pago por usuario---------------------------------------------- */

	echo '<tr><td colspan=2>';
		$sql = "SELECT L.termsindicator as codsuc, L.terms as suc, ifnull(U.userid,0) as tieneacceso
			FROM paymentterms L
			left join sec_paymentterms U
				ON  L.termsindicator = U.termsindicator
				AND U.userid='".$SelectedUser."'";
		$Result = DB_query($sql, $db);
		echo '<table width=100% align=center>';
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
			$nombrepayterms=$AvailRow['suc'];

			$chkuser = "";
			if ($AvailRow['tieneacceso']!="0" && (isset($_GET['SelectedUser']) || isset($_POST['searchAllUsers'])))
				$chkuser="checked";

			if (isset($_POST['PaymenttermsSel'.$j]) and ($_POST['PaymenttermsSel'.$j] <> ''))
				echo '<INPUT type=checkbox name=PaymenttermsSel'.$j.' checked value="' . ($j+1) . '">';
			else
				echo '<INPUT type=checkbox name=PaymenttermsSel'.$j.' '.$chkuser.' value="' . ($j+1) . '">';


			echo '<INPUT type=hidden name=Namepaymentterms'.$j.' value='.$codpayterm.' >';
			echo ucwords(strtolower($nombrepayterms));

			$j=$j+1;
			echo '</td>';
			echo '</tr>';
		}
		echo '<tr><td colspan=2><input type=hidden name=TotalPayterms value='.$j.'></td></tr>';
		echo '</table>';

	echo '</td></tr>';


	echo '<tr style="background-color:white;height:25px"><td colspan=2 style="text-align:center"><li><b></b></li></td></tr>';
}//si se muestra o no

if ($_POST['selModuloStatusCredito'] || $firstTime){

	echo '<tr style="background-color:orange;height:25px"><td colspan=2 style="text-align:center"><li><b>'. _('Configura Seleccion de Estatus Crediticios').'</b></li></td></tr>';

	/* ---------------------Asignacion de estatus de creditos por usuario---------------------------------------------- */
	echo '<tr><td colspan=2>';
		$sql = "SELECT L.reasoncode as codsuc, L.reasondescription as suc, ifnull(U.userid,0) as tieneacceso
			FROM holdreasons L
			left join sec_holdreasons U
				ON  L.reasoncode = U.reasoncode
				AND U.userid='".$SelectedUser."'";
		$Result = DB_query($sql, $db);
		echo '<table width=100% align=center>';
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
			$nombreholdreason=$AvailRow['suc'];

			$chkuser = "";
			if ($AvailRow['tieneacceso']!="0" && (isset($_GET['SelectedUser']) || isset($_POST['searchAllUsers'])))
				$chkuser="checked";

			if (isset($_POST['holdreasonsSel'.$j]) and ($_POST['holdreasonsSel'.$j] <> ''))
				echo '<INPUT type=checkbox name=holdreasonsSel'.$j.'  checked value="' . ($j+1). '">';
			else
				echo '<INPUT type=checkbox name=holdreasonsSel'.$j.'  '.$chkuser.' value="' . ($j+1). '">';

			echo '<INPUT type=hidden name=Nameholdreason'.$j.' value='.$codholdreason.' >';
			echo ucwords(strtolower($nombreholdreason));

			$j=$j+1;
			echo '</td>';
			echo '</tr>';
		}
		echo '<tr><td colspan=2><input type=hidden name="TotalHoldreasons" value='.$j.'></td></tr>';
		echo '</table>';

	echo '</td></tr>';


	echo '<tr style="background-color:white;height:25px"><td colspan=2 style="text-align:center"><li><b></b></li></td></tr>';
}//si se muestra o no

if ($_POST['selModuloCategorias'] || $firstTime){
	echo '<tr style="background-color:orange;height:25px"><td colspan=2 style="text-align:center"><li><b>'. _('Configura Seleccion de Categorias de Inventarios').'</b></li></td></tr>';

	/* ---------------------Asignacion de categorias de inventario por usuario---------------------------------------------- */


	/* SI ES LA PRIMERA VEZ QUE ENTRA, INICIALIZA COMODINES PARA CADA CAMPO */
	if (!isset($_POST['xGrupo'])) $_POST['xGrupo'] = '*';
	if (!isset($_POST['xLinea'])) $_POST['xLinea'] = '*';
	if (!isset($_POST['xCategoria'])) $_POST['xCategoria'] = '*';
	if (!isset($_POST['findCategoryName'])) $_POST['findCategoryName'] = '*';

	echo "<tr><td colspan=2 align='center'>";

		echo "<table  style='margin-left: auto; margin-right: auto; width: 50%;' border=1>";

			/************************************/
			/* SELECCION DEL GRUPO DE PRODUCTOS */
			echo '<tr><td>' . _('X Grupo') . ':' . "</td>
				<td><select tabindex='4' name='xGrupo'>";

			$sql = 'SELECT Prodgroupid, description FROM ProdGroup';
			$result=DB_query($sql,$db);

			echo "<option selected value='*'>Todos los grupos...</option>";

			while ($myrow=DB_fetch_array($result)){
				  if ($myrow['Prodgroupid'] == $_POST['xGrupo']){
					echo "<option selected value='" . $myrow["Prodgroupid"] . "'>" . $myrow['description'];
				  } else {
					  echo "<option value='" . $myrow['Prodgroupid'] . "'>" . $myrow['description'];
				  }
			}
			echo '</select><input type=submit name="searchAllUsers" VALUE="' . _('->') . '"></td></tr>';
			/************************************/

			/************************************/
			/* SELECCION DEL LINEA DE PRODUCTOS */
			echo '<tr><td>' . _('X Linea') . ':' . "</td>
				<td><select tabindex='4' name='xLinea'>";

			$sql = 'SELECT Prodlineid, Description FROM ProdLine';
			$result=DB_query($sql,$db);

			echo "<option selected value='*'>Todas las lineas...</option>";

			while ($myrow=DB_fetch_array($result)){
				  if ($myrow['Prodlineid'] == $_POST['xLinea']){
					echo "<option selected value='" . $myrow["Prodlineid"] . "'>" . $myrow['Description'];
				  } else {
					  echo "<option value='" . $myrow['Prodlineid'] . "'>" . $myrow['Description'];
				  }
			}
			echo '</select><input type=submit name="searchAllUsers" VALUE="' . _('->') . '"></td></tr>';
			/************************************/

			/************************************/
			/* SELECCION DEL CATEGORIA DE PRODUCTOS */
			echo '<tr><td>' . _('X Categoria') . ':' . "</td>
				<td><select tabindex='4' name='xCategoria'>";

			$sql='SELECT sto.categoryid, categorydescription
				FROM stockcategory sto, sec_stockcategory sec
				WHERE sto.categoryid=sec.categoryid AND userid="'.$_SESSION['UserID'].'"
				ORDER BY categorydescription';

			$result=DB_query($sql,$db);

			echo "<option selected value='*'>Todas las categorias...</option>";

			while ($myrow=DB_fetch_array($result)){
				  if ($myrow['categoryid'] == $_POST['xCategoria']){
					echo "<option selected value='" . $myrow["categoryid"] . "'>" . $myrow['categorydescription'];
				  } else {
					  echo "<option value='" . $myrow['categoryid'] . "'>" . $myrow['categorydescription'];
				  }
			}
			echo '</select></td></tr>';
			/************************************/

			echo "<tr style='background-color:#050505;height:5px'><td colspan=2></td></tr>";

			echo '<tr><td style="text-align:right" nowrap><b>'._('X Nombre de la Categoria') . ': </b></td><td nowrap> <input type=text name="findCategoryName" MAXLENGTH=40 size=40 value="'.$_POST['findCategoryName'].'">* para todos...</td>';
			echo "</tr>";

			echo '<tr><td></td>
				<td colspan=1  style="text-align:center;">
				<input type=submit name="searchAllUsers" VALUE="' . _('Categorias Con Estos Criterios') . '"></td>
				</tr>';

			echo "</table>";
			/************************************/
	echo "</td></tr>";

	echo '<tr><td colspan=2>';
		$sql = "SELECT L.categoryid as codsuc,
				L.categorydescription as suc,
				ProdLine.Description as Linea,
				ProdGroup.Description as Grupo,
				ProdGroup.Prodgroupid,
				ProdLine.Prodlineid,
				ifnull(sec_stockcategory.userid,0) as tieneacceso
			FROM stockcategory L
				join ProdLine ON L.prodLineId = ProdLine.Prodlineid
				join ProdGroup ON ProdLine.Prodgroupid = ProdGroup.Prodgroupid
				left join sec_stockcategory ON L.categoryid = sec_stockcategory.categoryid
											and sec_stockcategory.userid='$SelectedUser'

			WHERE (ProdLine.Prodgroupid = '".$_POST['xGrupo']."' OR '".$_POST['xGrupo']."' = '*')
				AND (ProdLine.Prodlineid = '".$_POST['xLinea']."' OR '".$_POST['xLinea']."' = '*')
				AND (L.categoryid = '".$_POST['xCategoria']."' OR '".$_POST['xCategoria']."' = '*')
				AND (L.categorydescription like '%".$_POST['findCategoryName']."%' OR '".$_POST['findCategoryName']."' = '*')
			ORDER BY ProdGroup.Description, ProdLine.Description, L.categorydescription ";

		//echo "<pre>$sql";

		$Result = DB_query($sql, $db);
		echo '<table width=100% align=center>';
		if (DB_num_rows($Result)>0 ) {
			echo "<tr><td ><hr></td></tr>";
		}
		$k=0; //row colour counter
		$j=0;
		$anteriorGrupo = "";
		$anteriorLinea = "";
		while($AvailRow = DB_fetch_array($Result)) {

			if ($anteriorGrupo != $AvailRow['Grupo']) {


				echo '<tr style="background-color:darkgray;">
					<td colspan=2 style="font-size:16px">';

				$marcaAll=false;
				if (isset($_POST['allGroupIdChecked'.$AvailRow['Prodgroupid']])) {
					echo '	<INPUT type="checkbox" checked name="allGroupIdChecked'.$AvailRow['Prodgroupid'].'">'.$AvailRow['Prodgroupid'].'>'.$AvailRow['Grupo'].'
						<input type=submit name="searchAllGroups'.$AvailRow['Prodgroupid'].'" VALUE="' . _('->') . '">';
						$marcaAll=true;
				} else {
					echo '	<INPUT type="checkbox" name="allGroupIdChecked'.$AvailRow['Prodgroupid'].'">'.$AvailRow['Prodgroupid'].'>'.$AvailRow['Grupo'].'
						<input type=submit name="searchAllGroups'.$AvailRow['Prodgroupid'].'" VALUE="' . _('->') . '">';
				}

				echo '		</td>';
				echo '</tr>';
				$anteriorGrupo = $AvailRow['Grupo'];
			}

			if ($anteriorLinea != $AvailRow['Linea']) {


				echo '<tr style="background-color:lightgray;">
					<td colspan=2 style="font-size:14px">';
				$marcaAllLines=false;
				if (isset($_POST['allLineIdChecked'.$AvailRow['Prodlineid']])) {
					echo '	<INPUT type="checkbox" checked name="allLineIdChecked'.$AvailRow['Prodlineid'].'" - >'.$AvailRow['Prodlineid'].'>'.$AvailRow['Linea'].'
						<input type=submit name="searchAllLines'.$AvailRow['Prodlineid'].'" VALUE="' . _('->') . '">';
					$marcaAllLines=true;
				} else {
					echo '	<INPUT type="checkbox" name="allLineIdChecked'.$AvailRow['Prodlineid'].'" - >'.$AvailRow['Prodlineid'].'>'.$AvailRow['Linea'].'
						<input type=submit name="searchAllLines'.$AvailRow['Prodlineid'].'" VALUE="' . _('->') . '">';
				}

				echo '	</td>';
				echo '</tr>';
				$anteriorLinea = $AvailRow['Linea'];
			}

			if ($k==1){
				echo '<tr style="background-color:#FaFaFa;">';
				$k=0;
			} else {
				echo '<tr style="background-color:#F8F8F8;">';
				$k=1;
			}

			echo '<td nowrap style="font-weight:normal;">';
			$codcategory=$AvailRow['codsuc'];
			$nombrecategory=$AvailRow['suc'];

			$chkuser = "";
			if ($AvailRow['tieneacceso']!="0" && (isset($_GET['SelectedUser']) || isset($_POST['searchAllUsers'])))
				$chkuser="checked";


			if ( ((isset($_POST['categorysSel'.$j])) and ($_POST['categorysSel'.$j] <> '') and (!isset($_POST['searchAllGroups'.$AvailRow['Prodgroupid'].''])) and (!isset($_POST['searchAllLines'.$AvailRow['Prodlineid'].'']))) OR ($marcaAll or $marcaAllLines) ){
				echo '<INPUT checked type="checkbox" id="'.$j.'" name="categorysSel'.$j.'"  value="' . ($j+1) . '">';
			}else{
				echo '<INPUT '.$chkuser.' type=checkbox name=categorysSel'.$j.' value="'  . ($j+1) . '">';
			}
			echo '<INPUT type=hidden name=Namecategory'.$j.' value="'.$codcategory.'" >';
			echo $AvailRow['codsuc'].' '.ucwords(strtolower($nombrecategory));


			$j=$j+1;

			echo '</td>';
			echo '</tr>';
		}
		echo '<tr><td colspan=2><input type=hidden name="TotalCategorys" value='.$j.'></td></tr>';
		echo '</table>';

	echo '</td></tr>';


	echo '<tr style="background-color:white;height:25px"><td colspan=2 style="text-align:center"><li><b></b></li></td></tr>';
}//si se muestra o no

if ($_POST['selModuloPerfiles'] || $firstTime){

	echo '<tr style="background-color:orange;height:25px"><td colspan=2 style="text-align:center"><li><b>'. _('Configura Seleccion de Perfiles de Usuario').'</b></li></td></tr>';

	/* Asignacion de Perfiles por usuario */
	echo '<tr><td colspan=2>';
		$sql = "SELECT S.profileid as codprofile, S.name as profile, ifnull(U.userid,0) as tieneacceso
			FROM sec_profiles S
			left join sec_profilexuser U
				ON  S.profileid = U.profileid and U.userid='".$SelectedUser."'
			ORDER BY S.name";
		$Result = DB_query($sql, $db);
		echo '<table width=100% align=center>';
		if (DB_num_rows($Result)>0 ) {
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
			$nombreprofile=$AvailRow['profile'];

			$chkuser = "";
			if ($AvailRow['tieneacceso']!="0" && (isset($_GET['SelectedUser']) || isset($_POST['searchAllUsers'])))
				$chkuser="checked";

			if (isset($_POST['PerfilSel'.$j]) and ($_POST['PerfilSel'.$j] <> ''))
				echo '<INPUT type=checkbox name=PerfilSel'.$j.' checked  value="' . ($j+1) . '">';
			else
				echo '<INPUT type=checkbox name=PerfilSel'.$j.' '.$chkuser.'  value="' . ($j+1) . '">';

			echo '<INPUT type=hidden name=NameProfile'.$j.' value='.$codprofile.' >';
			echo ucwords(strtolower($nombreprofile));

			$j=$j+1;
			echo '</td>';
			echo '</tr>';
		}
		echo '<tr><td colspan=2><input type=hidden name=TotalPerfiles value='.$j.'></td></tr>';
		echo '</table>';
	echo '</td></tr>';
	/**********************************************************************************************************/


	echo '<tr style="background-color:white;height:25px"><td colspan=2 style="text-align:center"><li><b></b></li></td></tr>';
}//si se muestra o no

if ($_POST['selModuloRoles'] || $firstTime){
	echo '<tr style="background-color:orange;height:25px"><td colspan=2 style="text-align:center"><li><b>'. _('Configura Seleccion de Roles dentro del Sistema').'</b></li></td></tr>';

	/* Asignacion de Roles por usuario */
	echo '<tr><td colspan=2>';
		$sql = "SELECT S.profileid as codprofile, S.name as profile, ifnull(U.userid,0) as tieneacceso
			FROM sec_ROL S
			left join sec_ROLxuser U
				ON  S.profileid = U.profileid and U.userid='".$SelectedUser."'
			ORDER BY S.name";
		$Result = DB_query($sql, $db);
		echo '<table width=100% align=center>';
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
			$nombreprofile=$AvailRow['profile'];

			$chkuser = "";
			if ($AvailRow['tieneacceso']!="0" && (isset($_GET['SelectedUser']) || isset($_POST['searchAllUsers'])))
				$chkuser="checked";

			if (isset($_POST['ROLSel'.$j]) and ($_POST['ROLSel'.$j] <> ''))
				echo '<INPUT  type=checkbox name=ROLSel'.$j.'  checked  value="' . ($j+1) . '">';
			else
				echo '<INPUT  type=checkbox name=ROLSel'.$j.'  '.$chkuser.'  value="' . ($j+1) . '">';


			echo '<INPUT type=hidden name=NameROL'.$j.' value='.$codprofile.' >';
			echo ucwords(strtolower($nombreprofile));

			$j=$j+1;
			echo '</td>';
			echo '</tr>';
		}
		echo '<tr><td colspan=2><input type=hidden name=TotalROL value='.$j.'></td></tr>';
		echo '</table>';
	echo '</td></tr>';
	/**********************************************************************************************************/


	echo '<tr style="background-color:white;height:25px"><td colspan=2 style="text-align:center"><li><b></b></li></td></tr>';
}//si se muestra o no

if ($_POST['selModuloClienteProveedor'] || $firstTime){

	echo '<tr style="background-color:orange;height:25px"><td colspan=2 style="text-align:center"><li><b>'. _('Configura Seleccion de Tipos de Clientes').'</b></li></td></tr>';

	echo '<tr><td colspan=2>';
		$sql = "SELECT S.typeid as codprofile, ifnull(U.userid,0) as tieneacceso, S.typename as profile
			FROM debtortype S
			left join sec_debtorxuser U
				ON  S.typeid = U.typeid and U.userid='".$SelectedUser."'";
		$Result = DB_query($sql, $db);
		echo '<table width=100% align=center>';
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
			$nombreprofile=$AvailRow['profile'];

			$chkuser = "";
			if ($AvailRow['tieneacceso']!="0" && (isset($_GET['SelectedUser']) || isset($_POST['searchAllUsers'])))
				$chkuser="checked";

			if (isset($_POST['TypedebSel'.$j]) and ($_POST['TypedebSel'.$j] <> ''))
				echo '<INPUT type=checkbox name=TypedebSel'.$j.'  checked  value="' . ($j+1) . '">';
			else
				echo '<INPUT type=checkbox name=TypedebSel'.$j.'  '.$chkuser.'  value="' . ($j+1) . '">';

			echo '<INPUT type=hidden name=NameTypedeb'.$j.' value='.$codprofile.' >';
			echo ucwords(strtolower($nombreprofile));

			$j=$j+1;
			echo '</td>';
			echo '</tr>';
		}
		echo '<tr><td colspan=2><input type=hidden name=TotalTypedeb value='.$j.'></td></tr>';
		echo '</table>';
	echo '</td></tr>';

	/**********************************************************************************************************/


	echo '<tr style="background-color:white;height:25px"><td colspan=2 style="text-align:center"><li><b></b></li></td></tr>';
	echo '<tr style="background-color:orange;height:25px"><td colspan=2 style="text-align:center"><li><b>'. _('Configura Seleccion de Tipos de Proveedores').'</b></li></td></tr>';

	echo '<tr><td colspan=2>';
		$sql = "SELECT S.typeid as codprofile, ifnull(U.userid,0) as tieneacceso, S.typename as profile
			FROM supplierstype S
			left join sec_supplierxuser U
				ON  S.typeid = U.typeid and U.userid='".$SelectedUser."'";
		$Result = DB_query($sql, $db);
		echo '<table width=100% align=center>';
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
			$nombreprofile=$AvailRow['profile'];

			$chkuser = "";
			if ($AvailRow['tieneacceso']!="0" && (isset($_GET['SelectedUser']) || isset($_POST['searchAllUsers'])))
				$chkuser="checked";


			if (isset($_POST['TypesuppSel'.$j]) and ($_POST['TypesuppSel'.$j] <> ''))
				echo '<INPUT type=checkbox name=TypesuppSel'.$j.'  checked value="' . ($j+1) . '">';
			else
				echo '<INPUT type=checkbox name=TypesuppSel'.$j.'  '.$chkuser.' value="' . ($j+1) . '">';


			echo '<INPUT type=hidden name=NameTypesupp'.$j.' value='.$codprofile.' >';
			echo ucwords(strtolower($nombreprofile));

			$j=$j+1;
			echo '</td>';
			echo '</tr>';
		}
		echo '<tr><td colspan=2><input type=hidden name=TotalTypesupp value='.$j.'></td></tr>';
		echo '</table>';
	echo '</td></tr>';


	echo '<tr style="background-color:white;height:25px"><td colspan=2 style="text-align:center"><li><b></b></li></td></tr>';
}// si se muestra o no

if ($_POST['selModuloProyectos'] || $firstTime){

	echo '<tr style="background-color:orange;height:25px"><td colspan=2 style="text-align:center"><li><b>'. _('Configura Seleccion de Proyectos en Modulo de Tareas').'</b></li></td></tr>';

	/* ---------------------Asignacion de proyectos por usuario---------------------------------------------- */
	echo '<tr><td colspan=2>';
		$sql = "SELECT S.idproyecto as codproyecto,ifnull(U.userid,0) as tieneacceso, S.nombre as proyecto
				FROM prdproyectos S
				left join sec_proyectoxuser U
					ON  S.idproyecto = U.idproyecto and U.userid='".$SelectedUser."'";
		$Result = DB_query($sql, $db);
		echo '<table width=100% align=center>';
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
			$nombreproyecto=$AvailRow['proyecto'];

			$chkuser = "";
			if ($AvailRow['tieneacceso']!="0" && (isset($_GET['SelectedUser']) || isset($_POST['searchAllUsers'])))
				$chkuser="checked";


			if (isset($_POST['ProyectoSel'.$j]) and ($_POST['ProyectoSel'.$j] <> ''))
				echo '<INPUT type=checkbox name=ProyectoSel'.$j.'  checked value="' . ($j+1) . '">';
			else
				echo '<INPUT type=checkbox name=ProyectoSel'.$j.'  '.$chkuser.' value="' . ($j+1) . '">';


			echo '<INPUT type=hidden name=NameProyecto'.$j.' value='.$codproyecto.' >';
			echo ucwords(strtolower($nombreproyecto));

			$j=$j+1;
			echo '</td>';
			echo '</tr>';
		}
		echo '<tr><td colspan=2><input type="hidden" name="TotalProyectos" value="'.$j.'"></td></tr>';
		echo '</table>';
	echo '</td></tr>';


	echo '<tr style="background-color:white;height:25px"><td colspan=2 style="text-align:center"><li><b></b></li></td></tr>';
}//si se muestra o no

if ($_POST['selModuloWorkflow'] || $firstTime){

	echo '<tr style="background-color:orange;height:25px"><td colspan=2 style="text-align:center"><li><b>'. _('Configura Seleccion de Workflows por Usuario').'</b></li></td></tr>';

	/* ---------------------Asignacion de Flujos por usuario---------------------------------------------- */
	echo '<tr><td colspan=2>';
		$sql = "SELECT S.idflujo,ifnull(U.userid,0) as tieneacceso, S.flujo
				FROM prdflujos S
				left join sec_WorkFlowXUser U
					ON  S.idflujo = U.idflujo and U.userid='".$SelectedUser."'";
		$Result = DB_query($sql, $db);
		echo '<table width=100% align=center>';
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
			$codproyecto=$AvailRow['idflujo'];
			$nombreproyecto=$AvailRow['flujo'];

			$chkuser = "";
			if ($AvailRow['tieneacceso']!="0" && (isset($_GET['SelectedUser']) || isset($_POST['searchAllUsers'])))
				$chkuser="checked";


			if (isset($_POST['FlujoSel'.$j]) and ($_POST['FlujoSel'.$j] <> ''))
				echo '<INPUT type=checkbox name=FlujoSel'.$j.'  checked value="' . ($j+1) . '">';
			else
				echo '<INPUT type=checkbox name=FlujoSel'.$j.'  '.$chkuser.' value="' . ($j+1) . '">';

			echo '<INPUT type=hidden name=NameFlujo'.$j.' value='.$codproyecto.' >';
			echo ucwords(strtolower($nombreproyecto));

			$j=$j+1;
			echo '</td>';
			echo '</tr>';
		}
		echo '<tr><td colspan=2><input type="hidden" name="TotalFlujos" value="'.$j.'"></td></tr>';
		echo '</table>';
	echo '</td></tr>';
}//si se muestra o no

echo '</table><br>
	<div class="centre"><input type="submit" name="submit" value="' . _('Registra Informacion') . '"></div>
	</form>';

include('includes/footer.inc');
?>
