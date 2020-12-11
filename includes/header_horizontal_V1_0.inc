<?php
	/*desarrollo- 08/FEBRERO/2013 - Aplique nuevo dise–o grafico */

	if (!headers_sent()){
		header('Content-type: text/html; charset=' . _('iso-8859-1'));
	}
	
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	//echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	//		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

	echo '<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>' . $title . '</title>
		<link href="' . $rootpath . '/css/css_lh.css" rel="stylesheet" type="text/css" />';

	//echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
	echo '<link rel="shortcut icon" href="'. $rootpath . '/favicon.ico" />';
	echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';
	//echo '<meta http-equiv="Content-Type" content="text/html; charset=' . _('ISO-8859-1') . '" />';
	echo '<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE"/>';
	//echo '<link href="' . $rootpath . '/css/'. $_SESSION['Theme'] . '/default.css" rel="stylesheet" type="text/css" />';
	echo '<script type="text/javascript" src = "' . $rootpath . '/javascripts/MiscFunctions.js"></script>';
    
	echo '<script language="JavaScript">';
	
	echo 'function NoRefrescar()';
	echo '{';
	echo 'var tecla=window.event.keyCode;';
	
	echo 'if (tecla==116)';
	echo '{';
	echo 'event.keyCode=0;';
	echo 'event.returnValue=false;';
	echo '}';
	echo '}';
	
	echo '</script>';

	echo '</head>';
	/* funcion para redireccionar*/
	
	$currentDateDBFormat = date('Y') . '/' . date('m') . '/' . date('d');
	$rate = GetCurrencyRateByLastDateWRate($currentDateDBFormat, 'USD', $db);
	
echo '	<body>';
echo '		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="margen">';
echo '		  <tr>';
echo '		    <td class="header"><table width="1012" border="0" cellpadding="0" cellspacing="0" class="tabla_header">';
echo '		      <tr>';
echo '			<td><table width="1012" border="0" cellspacing="0" cellpadding="0">';
echo '			  <tr>';
echo '			    <td width="179">
				<img src="'.$rootpath . '/' . $_SESSION['LogoFile'] . '" width="181" height="55" />
				
				</td>';
echo '			    <td width="16">&nbsp;</td>';
echo '			    <td width="405" valign="top"><table width="405" border="0" cellspacing="0" cellpadding="0">';
echo '			      <tr>';
echo '				<td valign="top" class="no_empresa">'.stripslashes($_SESSION['CompanyRecord']['coyname']).'</td>';
echo '			      </tr>';
echo '			      <tr>';
echo '				<td class="fecha">Fecha</td>';
echo '			      </tr>';
echo '			    </table></td>';
echo '			    <td width="16">&nbsp;</td>';
echo '			    <td valign="top"><table width="392" border="0" align="right" cellpadding="0" cellspacing="0">';
echo '			      <tr>';
echo '				<td width="37"><a href="#" class="links_azules">Menu</a></td>';
echo '				<td width="23"><img src="images/imgs/lv1.jpg" width="23" height="26" /></td>';
echo '				<td width="23"><img src="images/imgs/i_usuario.jpg" width="23" height="23" /></td>';
echo '				<td width="5" class="fecha">&nbsp;</td>';
echo '				<td class="fecha"><a href="' .  $rootpath . '/UserSettings.php?' . SID . '">' . stripslashes($_SESSION['UsersRealName']) . '</a></td>';
echo '				<td width="23"><img src="images/imgs/lv1.jpg" width="23" height="26" /></td>';
echo '				<td width="23"><img src="images/imgs/i_chat.jpg" width="23" height="23" /></td>';
echo '				<td width="2">&nbsp;</td>';
echo '				<td width="30"><a href="#" class="links_azules">Chat</a></td>';
echo '				<td width="23"><img src="images/imgs/lv1.jpg" width="23" height="26" /></td>';
echo '				<td width="30"><a href="#" class="links_azules">Salir</a></td>';
echo '			      </tr>';
echo '			    </table></td>';
echo '			  </tr>';
echo '			</table></td>';
echo '		      </tr>';
echo '		      <tr>';
echo '			<td><img src="images/imgs/01.jpg" width="1012" height="7" /></td>';
echo '		      </tr>';
echo '		      <tr>';
echo '			<td><table width="1012" border="0" cellspacing="0" cellpadding="0">';
echo '			  <tr>';
echo '			    <td><table width="830" border="0" cellspacing="0" cellpadding="0">';
echo '			      <tr>';
echo '				<td><table width="830" border="0" cellspacing="0" cellpadding="0">';
echo '				  <tr>';
echo '				    <td width="23"><img src="images/imgs/i_transac.jpg" width="23" height="23" /></td>';

	/***********************************************************************/
	/***  BUSCAR OPCIONES PREFERENTES PARA ESTE USUARIO DE TRANSACCIONES ***/
	
	$sql = "SELECT sec_functions.shortdescription, sec_functions.functionid, sec_functions.url
		    FROM sec_favxuser JOIN sec_functions ON sec_favxuser.functionid = sec_functions.functionid
		    WHERE sec_favxuser.userid = '".$_SESSION['UserID']."' and sec_favxuser.type = 1
		    ORDER BY sec_functions.title";
	$ReFuntion = DB_query($sql, $db);
	if (DB_num_rows($ReFuntion)>0 ) {
		while($ResFuntion = DB_fetch_array($ReFuntion)) {
echo '				    <td width="15">&nbsp;</td>';
echo '				    <td><a href="'.$ResFuntion['url'].'" class="links_azules">'.$ResFuntion['shortdescription'].'</a></td>';
		}
	}
	/***********************************************************************/

echo '				  </tr>';
echo '				</table></td>';
echo '			      </tr>';
echo '			      <tr>';
echo '				<td><table width="830" border="0" cellspacing="0" cellpadding="0">';
echo '				  <tr>';
echo '				    <td width="23"><img src="images/imgs/i_reportes.jpg" width="23" height="23" /></td>';

	/******************************************************************/
	/***  BUSCAR OPCIONES PREFERENTES PARA ESTE USUARIO DE REPORTES ***/
	
	$sql = "SELECT sec_functions.shortdescription, sec_functions.functionid, sec_functions.url
		    FROM sec_favxuser JOIN sec_functions ON sec_favxuser.functionid = sec_functions.functionid
		    WHERE sec_favxuser.userid='".$_SESSION['UserID']."' and sec_favxuser.type = 2
		    ORDER BY sec_functions.title";
	$ReFuntion = DB_query($sql, $db);
	if (DB_num_rows($ReFuntion)>0 ) {
		while($ResFuntion = DB_fetch_array($ReFuntion)) {
echo '				    <td width="15">&nbsp;</td>';
echo '				    <td><a href="'.$ResFuntion['url'].'" class="links_azules">'.$ResFuntion['shortdescription'].'</a></td>';
		}
	}
	/*******************************************************************/

echo '				  </tr>';
echo '				</table></td>';
echo '			      </tr>';
echo '			    </table></td>';
echo '			    <td width="23"><img src="images/imgs/lv2.gif" width="23" height="45" /></td>';
echo '			    <td width="158" class="tipocambio">Tipo de cambio: <span class="tipocambiovalor">' . number_format($rate,4) . '</span> al '.substr($currentDateDBFormat,0,10).'</td>';
echo '			  </tr>';
echo '			</table></td>';
echo '		      </tr>';
echo '		    </table></td>';
echo '		  </tr>';
echo '		  <tr>';
echo '		    <td height="23" class="areat">&nbsp;</td>';
echo '		  </tr>';

?>
