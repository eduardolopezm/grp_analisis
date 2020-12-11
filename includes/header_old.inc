<?php

	if (!headers_sent()){
		header('Content-type: text/html; charset=' . _('iso-8859-1'));
	}

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

	echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
	echo '<link rel="shortcut icon" href="'. $rootpath . '/favicon.ico" />';
	echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=' . _('ISO-8859-1') . '" />';
	echo '<link href="' . $rootpath . '/css/'. $_SESSION['Theme'] . '/default.css" rel="stylesheet" type="text/css" />';
	echo '<script type="text/javascript" src = "' . $rootpath . '/javascripts/MiscFunctions.js"></script>';
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

					if (isset($_SESSION['UserID'])) {

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
						echo '</td>';
						echo '<td class="quick_menu_tabs">';
						echo '<table cellpadding="0" cellspacing="0" class="quick_menu_tabs"><tr>';

						//Inicio de Permisos por Perfil de modulo
						/* trae funciones*/
						$sql = "SELECT  distinct s.moduleid,s.title,s.url
							    FROM sec_modules s,
								 sec_submodules sm,
								 www_users u,
								 sec_profilexuser PU,
								 sec_funxprofile FP,
								 sec_functions F
							    WHERE s.moduleid=sm.moduleid
								  and s.active=1 and sm.active=1
								  and FP.profileid=PU.profileid
								  and F.submoduleid=sm.submoduleid
								  and F.functionid=FP.functionid
								  and u.userid=PU.userid and PU.userid='".$_SESSION['UserID']."'
								  order by s.orderno
								  ";
						$ReFuntion = DB_query($sql, $db);
						$condfuntion=false;
						if (DB_num_rows($ReFuntion)>0 ) {
							$accesskey = 0;
							while($ResFuntion = DB_fetch_array($ReFuntion)) {
								$accesskey = $accesskey + 1;
								$URL=$ResFuntion['url'];
								$titulo=$ResFuntion['title'];
								$moduloid=$ResFuntion['moduleid'];
								$URL='/'.$URL.'modulosel='.$moduloid;
								echo '<td class="quick_menu_tab" align="center"><a accesskey="' . $accesskey .'" href="' .  $rootpath . $URL . SID . '"><span style="text-decoration:underline;">' . $accesskey . '</span> ' . $titulo . '</a></td>';

							}
						}

						echo '</tr>';
						echo '</table>';
					}

					echo '</td></tr>';
					echo '</table>';

?>
