<?php

	if (!headers_sent()){
		header('Content-type: text/html; charset=' . _('iso-8859-1'));
	}
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';


	echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
	echo '<link rel="shortcut icon" href="'. $rootpath.'/favicon.ico" />';
	echo '<link rel="icon" href="' . $rootpath.'/favicon.ico" />';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=' . _('ISO-8859-1') . '" />';
	echo '<link href="'.$rootpath. '/css/'. $_SESSION['Theme'] .'/default.css" rel="stylesheet" type="text/css" />';
	echo '<script type="text/javascript" src = "'.$rootpath.'/javascripts/MiscFunctions.js"></script>';
	echo '</head>';

	echo '<body>';
	echo '<table class="callout_main" cellpadding="0" cellspacing="0">';
	echo '<tr>';
	echo '<td colspan="2" rowspan="2">';

	echo '<table class="main_page" cellpadding="0" cellspacing="0">';
	echo '<tr>';
	echo '<td>';
	echo '<table width="100%" border="0" cellpadding="0" cellspacing="0" >';
	echo '<tr>';
	echo '<td>';

	if ($title AND substr($title,0,4) != 'Help') {
		echo '<table cellpadding="0" cellspacing="0" border="0" id="quick_menu" class="quick_menu">';
		echo '<tr>';
		echo '<td align="center" style="width:100%;" class="quick_menu_left">';
		echo '<table cellpadding="0" cellspacing="0" border="0"><tr><td><table cellpadding="0" cellspacing="0" border="0"><tr align=left><td>';
		echo '<img src="'.$rootpath . '/' . $_SESSION['LogoFile'] . '" align="left" height="50" TITLE="' . _('EMPRESA') . ':" ALT="' . _('EMPRESA') . ':">';
		echo '</td></tr><tr><td>';
		echo '</td></tr></table>';
		echo '</td><td width="80%" align="center">';
		echo '&nbsp;&nbsp;&nbsp;' . stripslashes($_SESSION['CompanyRecord']['coyname']);
		// Make the title text a class, can be set to display:none is some themes
		echo '<BR>&nbsp;&nbsp;&nbsp;<a href="' .  $rootpath . '/UserSettings.php?' . SID . '"><img src="'.$rootpath.'/css/'.$theme.'/images/user.png" TITLE="User" ALT="' . _('Usuario') . '"> ' . stripslashes($_SESSION['UsersRealName']) . '</a>';
		echo '<BR>&nbsp;&nbsp;&nbsp;<font class="header_title"> ' . $title . '</font>';
		echo '</td></tr></table>';
		echo ' </td>';

		echo '<td class="quick_menu_tabs">';
		echo '<table cellpadding="0" cellspacing="0" class="quick_menu_tabs"><tr>';
		echo '<td class="quick_menu_tab" align="center"><a accesskey="1" href="' .  $rootpath . '/index.php?' . SID . '"><span style="text-decoration:underline;">1</span> ' . _('Menu Inicio') . '</a></td>';

		if (count($_SESSION['AllowedPageSecurityTokens'])>1){

			echo '<td class="quick_menu_tab" align="center"><a accesskey="2" href="' .  $rootpath . '/SelectCustomer.php?' . SID . '"><span style="text-decoration:underline;">2</span> ' . _('Clientes') . '</a></td>';

			echo '<td class="quick_menu_tab" align="center"><a accesskey="3" href="' .  $rootpath . '/SelectProduct.php?' . SID . '"><span style="text-decoration:underline;">3</span> ' . _('Productos') . '</a></td>';

			echo '<td class="quick_menu_tab" align="center"><a accesskey="4" href="' .  $rootpath . '/SelectSupplier.php?' . SID . '"><span style="text-decoration:underline;">4</span> ' . _('Proveedores') . '</a></td>';

			$DefaultManualLink = '<td class="quick_menu_tab" align="center"><a rel="external" accesskey="8" href="' .  $rootpath . '/doc/Manual/ManualContents.php?' . SID . '"><span style="text-decoration:underline;">8</span> ' . _('Manual') . '</a></td>';

			if (substr($_SESSION['Language'],0,2) !='en'){
				if (file_exists('locale/' . $_SESSION['Language'] . '/Manual/ManualContents.php')){
					echo '<td class="quick_menu_tab" align="center"><a target="_blank" accesskey="8" href="' .  $rootpath . '/locale/' . $_SESSION['Language'] . '/Manual/ManualContents.php' . SID . '"><span style="text-decoration:underline;">8</span> ' . _('Manual') . '</a></td>';
				} else {
					echo $DefaultManualLink;
				}
			} else {
					echo $DefaultManualLink;
			}
		}

		echo '<td class="quick_menu_tab" align="center"><a accesskey="0" href="' . $rootpath . '/Logout.php?' . SID . '" onclick="return confirm(\'' . _('Estas seguro que quieres terminar tu sesion ?') . '\');"><span style="text-decoration:underline;">0</span> '  . _('Cerrar') . '</a></td>';

		echo '</tr></table>';
		echo '</td></tr></table>';

	}

echo '</td>';
echo '</tr>';
echo '</table>';

?>
