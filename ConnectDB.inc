<?php
/* $Revision: 1.23 $ */

/* ARCHIVO MODIFICADO POR: GONZALO ALVAREZ*/
/* FECHA DE MODIFICACION: 10-NOV-2009 */
/* CAMBIOS:*/
/* 1.- TRADUCCION A ESPAÑOL */
/* FIN DE CAMBIOS*/

require_once ($PathPrefix .'includes/MiscFunctions.php');
if (!isset($_SESSION['DatabaseName'])){
	if (isset($_POST['CompanyNameField'])){
		if (is_dir('companies/' . $_POST['CompanyNameField']) AND $_POST['CompanyNameField'] != '..'){
			$_SESSION['DatabaseName'] = $_POST['CompanyNameField'];
			include_once ($PathPrefix . 'includes/ConnectDB_' . $dbType . '.inc');
		} else {
                      prnMsg(_('El nombre de la compañia seleccionada no esta configurada para usarse en esta instalacion del webERP. El nombre abreviado de la compañia seleccionada, debe de tener un directorio definido. Reportar a su administrador de sistema'),'error');
   		}
	} elseif (isset($DatabaseName)) { /* Scripts that do not require a login must have the $DatabaseName variable set in hard code */
		$_SESSION['DatabaseName'] = $DatabaseName;
		include_once ($PathPrefix . 'includes/ConnectDB_' . $dbType . '.inc');
	}
} else {
 	include_once($PathPrefix .'includes/ConnectDB_' . $dbType . '.inc');
}

?>
